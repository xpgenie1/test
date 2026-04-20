<?php
require_once 'v4/private/class/GBL/GBLOutput.php';
require_once 'v4/private/class/CLI/CLIClienteMDL.php';
require_once 'v4/private/class/USU/USUCategoriasPlanes.php';
require_once 'v4/private/class/CUOT/CUOTFactory.php';
require_once 'v4/private/class/USU/USUPlanMDL.php';
require_once 'v4/private/class/USU/USUUsuarios.php';
require_once 'v4/private/class/GRID/GRIDGridOU.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/FACT/FACTFactura.php';
require_once 'v4/private/class/FACT/FACTPedido.php';
require_once 'v4/private/class/FACT/FACTPedidoOU.php';
require_once 'v4/private/class/TICK/TICKTicketMDL.php';
require_once 'v4/private/class/TICK/TICKBase.php';
require_once 'v4/private/class/CARR/CARRCodDescuento.php';
require_once 'v4/private/class/PAYP/paypal-subscription.class.php';
require_once 'v4/private/class/PAYP/PAYPUtilidades.php';
require_once 'v4/private/class/AVI/AVIAvisoCancelacionSuscripcion.php';
require_once 'v4/private/class/PER/PERPermisos.php';

/**
 * Objeto OU con la salida de la ficha de cliente
 * @package USU
 * @author Victor J Chamorro - victor@ipdea.com
 */
class USUClienteOU implements GBLOutput,  AJAXRequest{
	
	/**
	 * MDL de Cliente
	 * @var CLIClienteMDL
	 */
	private $objMDL;
        
        private $BDBase;
	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * Tabla de pedidos
	 * @var GRIDGridOU
	 */
	private $gridPedidos;
	
	/**
	 *
	 * @var USUPlanMDL
	 */
	private $objPlan=null;	
	
	/**
	 * Valor para cancelar el plan (el "borrado" de siempre)
	 * @var int
	 * */
	const CUENTA_CANCELADA=1;
	const CUENTA_ACTIVA=0;
	
	
	/**
	 * Genera un objeto OU con la salida de la ficha de cliente
	 * @param string $plan
	 * @throws TeException
	 */
	public function __construct($plan){
		
		if (empty($plan)) throw new TeException('No se ha pasado un plan válido al constructor', __LINE__,__CLASS__);
		
		if (!PERPermisos::checkSection('cuenta')) throw new TeException('Permiso denegado',403,'PERPermisos');
		
		$this->plan=$plan;
		// Datos de su plan
		USUPlanMDL::clearSinglenton();
		$this->objPlan=USUPlanMDL::getInstance($this->plan);
                            
		//Grid/Tabla
		$this->generaGridPedidos();
	}
	
	/**
	 * Tabla de pedidos
	 */
	private function generaGridPedidos(){
		//Controller
		$gui=GUIController::getInstance();
				
		//Grid principal
		$this->gridPedidos=new GRIDGridOU();
		$this->gridPedidos->setTplVersion($gui->getTPLVersion());
		$this->gridPedidos->setSQL("SELECT p.id as Id, p.fecha as Fecha,p.modo_pago as 'Modo Pago',p.estado as Estado,p.cursado as Cursado,(select count(*) from facturas where id_pedido=p.id)>0 as Factura,p.id as 'Imp.' FROM pedidos p WHERE p.id_plan='".$this->objPlan->getId()."'");
		$this->gridPedidos->setCamposBusqueda(array('p.id'=>LANGBase::__('Numero de pedido')));
		$acciones=array('3'=>LANGBase::__('Detalles'),'5'=>LANGBase::__('Mostrar factura'));
                $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), $this->plan);
                if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
                    $acciones['6']= LANGBase::__('Pagar');
                }
                $this->gridPedidos->setAccionesRegistro($acciones);
		$this->gridPedidos->setOrder('id','desc');
		$this->gridPedidos->setPagesize(10);
		$this->gridPedidos->setPage(1);
		$this->gridPedidos->addJSCSS($gui);
		$this->gridPedidos->setParseoColumna(function($campo,$data){
			switch(strtolower($campo)){
                            
				case 'imp.':
					//Le paso el plan para que no lo genere tantas veces como lineas de pedidos tengamos
					$objPedido=new FACTPedido($data,  GBLSession::getPlan());
					$data=number_format($objPedido->objCarro->getTotal(),2)."€";
					break;
				case 'cursado':
				case 'factura':
					if ($data=='1'){
						$data='<i class="icon-check"></i></a>';
					}else{
						$data='<i class="icon-check-empty"></i>';
					}
				break;
				case 'estado':
					switch($data){
						case FACTPedido::ESTADO_PAGADO:
							$data=LANGBase::__('Pagado');
						break;
						case FACTPedido::ESTADO_PENDIENTE:
							$data=LANGBase::__('Pendiente');
						break;
						case FACTPedido::ESTADO_ANULADO:
							$data=LANGBase::__('Anulado');
						break;
					}
					break;
				case 'modo pago':
					switch($data){
						case FACTPedido::MODO_PAGO_EXENTO:
							$data=LANGBase::__('Exento');
						break;
						case FACTPedido::MODO_PAGO_PUNTUAL_CUENTABANCARIA:
							$data=LANGBase::__('Puntual C.Bancaria');
						break;
						case FACTPedido::MODO_PAGO_PUNTUAL_PAYPAL:
							$data=LANGBase::__('Puntual PayPal');
						break;
						case FACTPedido::MODO_PAGO_PUNTUAL_TALON:
							$data=LANGBase::__('Puntual Talón');
						break;
						case FACTPedido::MODO_PAGO_PUNTUAL_TARJETA:
							$data=LANGBase::__('Puntual Tarjeta');
						break;
						case FACTPedido::MODO_PAGO_PUNTUAL_TRANSFERENCIA:
							$data=LANGBase::__('Puntual Transferencia');
						break;
						case FACTPedido::MODO_PAGO_RECURRENTE_CUENTABANCARIA:
							$data=LANGBase::__('Recurrente C.Bancaria');
						break;
						case FACTPedido::MODO_PAGO_RECURRENTE_PAYPAL:
							$data=LANGBase::__('Recurrente PayPal');
						break;
						case FACTPedido::MODO_PAGO_RECURRENTE_TALON:
							$data=LANGBase::__('Recurrente Talón');
						break;
						case FACTPedido::MODO_PAGO_RECURRENTE_TARJETA:
							$data=LANGBase::__('Recurrente Tarjeta');
						break;
						case FACTPedido::MODO_PAGO_RECURRENTE_TRANSFERENCIA:
							$data=LANGBase::__('Recurrente Transferencia');
						break;
						case FACTPedido::MODO_PAGO_INDEFINIDO:
							$data=LANGBase::__('Indefinido');
						break;
					}
					break;
				case 'fecha':
					$data=  UTLDateTime::toNewTimeZoneFormat($data);
				break;
				
			}
			return $data;
		});
		
	}
       	
        /**
         * Añade librerias JS
         * @param GUIController $gui
         */
        private function addJs($gui){
            $gui->addJSEndBody('/v4/public/js/USU/cuenta.js');
            $gui->addJSDeclaration('var id_tipo_plan_actual="'.$this->objPlan->getTipoPlan().'";');
            $gui->addJS('/v4/public/js/UTL/jquery-plugins.js');
            $gui->addJS('/v4/public/js/UTL/jquery-ui-last.js');
            $gui->addJS('/v4/public/bootstrap/js/bootstrap.file-input.min.js');
            $gui->addJSEndBody('/v4/public/js/UTL/chosen.jquery.min.js');
        }

        /**
         * Añade los datos necesarios para la primera pestaña (Mi Cuenta)
         * @param GBLTemplate $tpl
         */
        private function setTabMiCuenta($tpl) {
            	$tpl->setVar('C_PLAN',$this->objPlan->getTipoPlanNombre());
		$objTipo=new USUTipoPlanMDL($this->objPlan->getTipoPlan());
		$periocidad=($objTipo->getPlazo()==12 ? LANGBase::__('Anual') : LANGBase::__('Mensual'));
		$tpl->setVar('C_TIPO',$periocidad);
		
		$tpl->setVar('C_NOMBRE',$this->plan);
                $objCuota= CUOTFactory::getInstance($this->plan);
                $objCiclo= $objCuota->getCiclo();
                if ($objCiclo->cicloValido()){
                    $tpl->setVar('C_DESDE',$objCiclo->getFechaInicio()->getInTimeZone());
                    $tpl->setVar('C_HASTA',$objCiclo->getFechaFin()->getInTimeZone());
                }else{
                    
                }
        }        
        
        private function setTabDatos($tpl,$permisos) {
            // Datos fiscales
            try{
                $this->objMDL = CLIClienteMDL::getClienteFromPlan($this->plan);
                $hide=($this->objMDL->getIdPais()!='ES' ? 'hide' : '' );
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_ID',$this->objMDL->getIdCliente());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_RAZON_SOCIAL',$this->objMDL->getRazonSocial());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_CIF',$this->objMDL->getNif());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_DIRECCION1',$this->objMDL->getDireccion1());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_DIRECCION2',$this->objMDL->getDireccion2());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_CP',$this->objMDL->getCP());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_LOCALIDAD',$this->objMDL->getLocalidad());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_PROVINCIA',$this->objMDL->getProvincia());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION', 'D_PAIS', $this->objMDL->getPais());
                
               
                $this->BDBase=new BDBase($this->plan);
                $this->BDBase->BD1->setCharset('utf8');
                
		$lang='';
		if (LANGBase::getInstance()->getCurrentLang()=='en') $lang='_en';
		
                if($permisos >= USUPermisos::ADMINISTRACION){ 
                    $ddlPais=$this->BDBase->BD1->CreaDDL('pais', 'SELECT iso_a2,pais'.$lang.' FROM paises', true, $this->objMDL->getIdPais());
                     $ddlProvincia=$this->BDBase->BD1->CreaDDL('ddl_provincia', 'SELECT nombre,nombre FROM provincias WHERE iso_a2=\'ES\' ORDER BY nombre ASC',false,$this->objMDL->getProvincia(),$hide);
                }else{
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION' ,'READONLY', 'readonly');
                    $ddlPais=$this->BDBase->BD1->CreaDDL('pais', 'SELECT iso_a2,pais'.$lang.' FROM paises', true, $this->objMDL->getIdPais(),"","disabled");
                     $ddlProvincia=$this->BDBase->BD1->CreaDDL('ddl_provincia', 'SELECT nombre,nombre FROM provincias WHERE iso_a2=\'ES\' ORDER BY nombre ASC',false,$this->objMDL->getProvincia(),$hide,"disabled");
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION', 'BOTON_CLASS', 'hidden');
                }
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','DDL_PROVINCIA',$ddlProvincia);
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','DDL_PAIS',$ddlPais);
                $tpl->setVarBlock('BLOQUE_ADMINISTRACION','DDL_TIMEZONE',UTLDateTime::getDDLTimeZones('timezone',$this->objPlan->getTimeZone(),'',$this->objMDL->getIdPais()));

                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_TELEFONO',$this->objMDL->getTelefono());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_WEB',$this->objMDL->getWeb());
                $tpl->setVarBlock('BLOQUE_VISUALIZACION','D_EMAIL',$this->objMDL->getEmail());
                

                //Contacto de facturación
                $contactos=$this->objMDL->getContactos(3);
                if(count($contactos)>0){
                    $objContacto=$contactos[0];
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION','F_ID',$objContacto->getId());
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION','F_NOMBRE',$objContacto->getNombre());
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION','F_TELEFONO',$objContacto->getTelefono());
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION','F_EMAIL',$objContacto->getEmail());
                }

                if (!$this->objMDL->isFullOk() && $permisos>= USUPermisos::ADMINISTRACION){
                    $tpl->setVarBlock('BOTONES_ADMINISTRACION','BTN_ACTUALIZA_CLASS','disabled');
                }
            }catch(TeException $e){
                $tpl->setVarBlock('BOTONES_ADMINISTRACION','BTN_ACTUALIZA_CLASS','disabled');
                
                $this->BDBase=new BDBase($this->plan);
                $this->BDBase->BD1->setCharset('utf8');

                $locales=  LANGBase::getInstance()->getIdiomasNavegador();

                $pais_detectado='ES';
                foreach($locales as $locale){
                        $aux=strpos($locale,'-');
                        if ($aux!==false){
                                $pais_detectado=strtoupper(substr($locale,$aux+1));
                                break;
                        }
                }
                $hide=($pais_detectado!='ES' ? 'hide' : '' );
                if($permisos >= USUPermisos::ADMINISTRACION){
		    $lang='';
		    if (LANGBase::getInstance()->getCurrentLang()=='en') $lang='_en';
                    $ddlPais=$this->BDBase->BD1->CreaDDL('pais', 'SELECT iso_a2,pais'.$lang.' FROM paises', true, $pais_detectado);
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION','DDL_PAIS',$ddlPais);
                    
                    $ddlProvincia=$this->BDBase->BD1->CreaDDL('ddl_provincia', 'SELECT nombre,nombre FROM provincias WHERE iso_a2=\'ES\' ORDER BY nombre ASC',false,'',$hide);
                    $tpl->setVarBlock('BLOQUE_VISUALIZACION','DDL_PROVINCIA',$ddlProvincia);
                    $tpl->setVarBlock('BLOQUE_ADMINISTRACION','DDL_TIMEZONE',UTLDateTime::getDDLTimeZones('timezone',$this->objPlan->getTimeZone(),'',$pais_detectado));
                }
            }
        }
        
        private function setTabPedidos($tpl) {
            // Tabla de pedidos
            $tpl->setVarBlock('BLOQUE_VISUALIZACION','TABLA_PEDIDOS',$this->gridPedidos->getOutput());
            $tpl->setVar('ENLACES_VISUALIZACION',$tpl->parseBlock('ENLACES_VISUALIZACION'));
            $tpl->setVar('BLOQUE_VISUALIZACION',$tpl->parseBlock('BLOQUE_VISUALIZACION'));
        }
        
        private function setModal($tpl,$tpl_modal) {
            $tpl_modal->setVar('C_PLAN',$this->objPlan->getTipoPlanNombre());
	    $objTipo=new USUTipoPlanMDL($this->objPlan->getTipoPlan());
	    $periocidad=($objTipo->getPlazo()==12 ? LANGBase::__('Anual') : LANGBase::__('Mensual'));
	    $tpl_modal->setVar('C_TIPO',$periocidad);
            $tpl_modal->setVar('C_NOMBRE',$this->objPlan->getId()."-".$this->plan);
            
            $objCuota= CUOTFactory::getInstance($this->plan);
            $objCiclo= $objCuota->getCiclo();
            if ($objCiclo->cicloValido()){
                $tpl_modal->setVar('C_DESDE',$objCiclo->getFechaInicio()->getInTimeZone());
                $tpl_modal->setVar('C_HASTA',$objCiclo->getFechaFin()->getInTimeZone());
                //Suscripción
                switch($this->objPlan->getTipoPlanCategoria()){
                    case USUTipoPlanMDL::CATEGORIA_GRATUITO:
                    case USUTipoPlanMDL::CATEGORIA_BONO:
                        $tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION',$tpl->parseBlock('BLOQUE_SUSCRIPCION_NO_DISPONIBLE'));
                        break;
                    case USUTipoPlanMDL::CATEGORIA_LIMITADO:
                    case USUTipoPlanMDL::CATEGORIA_ILIMITADO:
		    case USUTipoPlanMDL::CATEGORIA_IDEM:
                        $suscripcion_activa=0;

                        if (substr($this->objPlan->getCodigoSuscripcion(),0,2)==FACTPedido::MODO_PAGO_RECURRENTE_PAYPAL){
                            $profile_id=substr($this->objPlan->getCodigoSuscripcion(),3);
                            $data= PAYPAL\Utilidades::getSuscriptionPaypalStatus($profile_id);

                            if(strtolower($data['STATUS']) == "active"){
                                    $obj=new UTLDateTime($data['NEXTBILLINGDATE'],'UTC');
                                    $tpl->setVarBlock('BLOQUE_SUSCRIPCION_ACTIVA', 'FECHA',$obj->getInTimeZone());
                                    $tpl->setVarBlock('BLOQUE_SUSCRIPCION_ACTIVA', 'IMPORTE',$data['REGULARAMT']);

                                    $tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION',$tpl->parseBlock('BLOQUE_SUSCRIPCION_ACTIVA'));
                                    $suscripcion_activa=1;
                            }
                        }else{
				//Mostramos la suscripción sin paypal y permitimos cancelar
				if ($this->objPlan->getCodigoSuscripcion()!==0){
					
					$suscripcion_activa=1;
					
					//Los pedidos se generan a las 11:00h
					$fecha_suscripcion_desde = clone $objCiclo->getFechaFin();
					$fecha_suscripcion_desde->sub(new DateInterval('P7D'));
					
					$fecha_suscripcion_desde->setTime(11, 0, 0);
					
					if ($objCiclo->getFechaFin()->getHour()>10){
						$fecha_suscripcion_desde->add(new DateInterval('P1D'));
					}
					
					if ($fecha_suscripcion_desde<UTLDateTime::now()){
						$tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION', LANGBase::__('Suscripción activa, pedido automático cursado el ').$fecha_suscripcion_desde->getInTimeZone());
					}else{
						//$tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION', LANGBase::__('Suscripción activa, próximo pedido automático el '.$fecha_suscripcion_desde->getInTimeZone()));
						$objTipoPlan=new USUTipoPlanMDL($this->objPlan->getTipoPlan());

						$objDescuento=new CARRCodDescuento($this->objPlan->getCodigoDescuento());
						if($objDescuento->isCodActive()){
							$descuento=$objDescuento->getDescuento();
							$importe = ($objTipoPlan->getPrecio()-($objTipoPlan->getPrecio()*($descuento/100)))*(($this->objMDL->getImpuesto()/100)+1);
						}else{
							$importe = $objTipoPlan->getPrecio()*(($this->objMDL->getImpuesto()/100)+1);
						}


						$tpl->setVarBlock('BLOQUE_SUSCRIPCION_ACTIVA', 'FECHA',$fecha_suscripcion_desde->getInTimeZone());
						$tpl->setVarBlock('BLOQUE_SUSCRIPCION_ACTIVA', 'IMPORTE',round($importe,2));
						$tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION',$tpl->parseBlock('BLOQUE_SUSCRIPCION_ACTIVA'));
					}
				}
			}
                        if ($suscripcion_activa==0){
                            //Solo se puede activar suscripcion si aún no se ha generado pedido para el nuevo ciclo
                            $fecha_suscripcion_desde = clone $objCiclo->getFechaFin();
                            $fecha_suscripcion_desde->sub(new DateInterval('P7D'));
                            $fecha_suscripcion_desde->setTime(9,0,0);

                            $tmpObjDate=  UTLDateTime::now();
                            $tmpObjDate->add(new DateInterval('P1D'));							

                            if ($tmpObjDate < $fecha_suscripcion_desde){
                                $objTipoPlan=new USUTipoPlanMDL($this->objPlan->getTipoPlan());
                                $objDescuento=new CARRCodDescuento($this->objPlan->getCodigoDescuento());

                                $descuento=0;
                                $meses=0;

                                $fecha_fin=$objDescuento->getValidezFin();

                                if($objDescuento->isCodActive() && $fecha_fin > $objCiclo->getFechaFin()){
                                        $descuento=$objDescuento->getDescuento();
                                        $importe = ($objTipoPlan->getPrecio()-($objTipoPlan->getPrecio()*($descuento/100)))*(($this->objMDL->getImpuesto()/100)+1);
                                        $fecha_fin=$objDescuento->getValidezFin();
                                        $objDiff=$fecha_fin->diff($fecha_suscripcion_desde,false);
                                        $dias=$objDiff->days;
                                        $años=$objDiff->format('%y');
                                        if ($años==0){
                                                $meses=$objDiff->format('%m');
                                        }else{
                                                $meses=$objDiff->format('%m')+(12*$años);
                                        }
                                        //se suma uno para que incluya el propio inicial
                                        if ($objDiff->format('%d')!==0){
                                                $meses++;
                                        }

                                        $tpl_modal->setVar('SUSCRIPCION_DESDE',$fecha_suscripcion_desde->getInTimeZone());
                                        $tpl_modal->setVar('SUSCRIPCION_DURACION',LANGBase::__('Duración:').' '.$meses.' '.LANGBase::__('meses').' '.LANGBase::__('(hasta finalización del descuento en vigor)'));
                                }else{
                                        $importe = $objTipoPlan->getPrecio()*(($this->objMDL->getImpuesto()/100)+1);
                                        $tpl_modal->setVar('SUSCRIPCION_DESDE',$fecha_suscripcion_desde->getInTimeZone());
                                }
                                $tpl_modal->setVar('IMPORTE_SUSCRIPCION',round($importe,2));
                                $tpl_modal->setVar('BOTON_SUSCRIPCION', '<div id="json_boton">'.json_encode(array(round($importe,2),$fecha_suscripcion_desde->getDateTimeBD(),$meses)).'</div>');
                                $tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION',$tpl->parseBlock('BLOQUE_SUSCRIPCION_DISPONIBLE'));
                            }else{
                                $tpl->setVarBlock('BLOQUE_SUSCRIPCION_NO_DISPONIBLE','DETALLE',  LANGBase::__('Solo puede solicitar suscripción si quedan más de 7 días hasta la finalización del ciclo'));
                                $tpl->setVarBlock('BLOQUE_ADMINISTRACION','SUSCRIPCION',$tpl->parseBlock('BLOQUE_SUSCRIPCION_NO_DISPONIBLE'));
                            }
                        }
                        break;
                    }

            }else{
                $tpl->setVar('C_DESDE',LANGBase::__('Sin ciclo actual'));
                $tpl_modal->setVar('C_DESDE',LANGBase::__('Sin ciclo actual'));
            } 
        }
        
        function setAsistente($tpl_modal) {
            // Asistente Cambio de plan
            // # tipo/categorias de planes
            $categorias = USUCategoriasPlanes::getCategorias();
            $strListado='';
            $listado_planes_alto_volumen='';
            foreach ($categorias as $categoria){
                    if(	$categoria==USUTipoPlanMDL::CATEGORIA_GRATUITO &&
                            $this->objPlan->getTipoPlanCategoria()==USUTipoPlanMDL::CATEGORIA_GRATUITO){
                            continue;
                    }
                    $listado_planes='';
                    $planes= USUCategoriasPlanes::getTiposPlanes($categoria);
		    
		    $iva=$this->BDBase->CONFIG['masiva'];
		    try{
			if($this->objMDL instanceof CLIClienteMDL &&  $this->objMDL->getIdCliente()){
				$iva=($this->objMDL->getImpuesto()/100)+1;
			}	
		    }catch(TeException $e){
			//aún no habrá cliente
		    }
		    
                    foreach($planes as $plan){
                        
                            $precio = round($plan['precio']*$iva,2). " &euro;";

                            if ($categoria==USUTipoPlanMDL::CATEGORIA_BONO){
				    $periocidad=LANGBase::__('Anual');
				    $precio.= " / ".LANGBase::__('Año');
			    }else{
				    $periocidad=LANGBase::__('Mensual');
				    $precio.= " / ".LANGBase::__('Mes');
			    }
			    
			    $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'ID_PLAN', $plan['id']);
                            $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'NOMBRE_PLAN', $plan['nombre']);
                            $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'PRECIO_PLAN', $precio);
                            $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'PRECIO_BRUTO', $plan['precio']);
                            $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'ENVIOS_BRUTO', $plan['envios']);
                            $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'CONTACTOS_BRUTO', $plan['contactos']);
                            $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'TIPO_BRUTO', $periocidad);

                            if ($plan['contactos']!=-1){
                                    $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'CONTACTOS', $plan['contactos']);
                            }else{
                                    $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'CONTACTOS_ILIMITADOS', LANGBase::__('Ilimitados'));
                            }

                            if ($plan['envios']!=-1){
                                    $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'ENVIOS', $plan['envios']);
                            }else{
                                    $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'ENVIOS_ILIMITADOS', LANGBase::__('Ilimitados'));
                            }

                            if ($plan['id']==$this->objPlan->getTipoPlan() && $this->objPlan->getTipoPlanCategoria()!="bono"){
                                    //El plan actual no puede marcarse
                                    $tpl_modal->setVarBlock('BLOQUE_ITEM_LISTADO_PLANES', 'DISABLED', 'disabled="disabled"');
                            }

			    //Los planes limitados de mas de 50.000 contactos se les agrupa en Alto volumen y gran volumen
                            if(((int)$plan['contactos'])>50000 && $categoria== USUTipoPlanMDL::CATEGORIA_LIMITADO){
                                    $listado_planes_alto_volumen.=$tpl_modal->parseBlock('BLOQUE_ITEM_LISTADO_PLANES',true);
                            }else{
                                    $listado_planes.=$tpl_modal->parseBlock('BLOQUE_ITEM_LISTADO_PLANES',true);
                            }
                    }

                    /*
                     * Para que lo detecte el generador de po (traducciones)
                     * LANGBase::__('gratuito');
                     * LANGBase::__('ilimitado');
                     * LANGBase::__('limitado');
		     * LANGBase::__('idem');
                     * LANGBase::__('bono');
                     */
                    $tpl_modal->setVarBlock('BLOQUE_TIPO', 'IDTIPO', $categoria);
                    $tpl_modal->setVarBlock('BLOQUE_TIPO', 'TIPO', ucfirst(LANGBase::__($categoria)));
                    $tpl_modal->setVarBlock('BLOQUE_TIPO', 'LISTADO', $listado_planes);
                    $strListado.=$tpl_modal->parseBlock('BLOQUE_TIPO',true);
            }
            $tpl_modal->setVarBlock('BLOQUE_TIPO', 'IDTIPO', 'limitado-altovolumen');
            $tpl_modal->setVarBlock('BLOQUE_TIPO', 'TIPO', ucfirst(LANGBase::__('limitado-altovolumen')));
            $tpl_modal->setVarBlock('BLOQUE_TIPO', 'LISTADO', $listado_planes_alto_volumen);
            $strListado.=$tpl_modal->parseBlock('BLOQUE_TIPO',true);

            $tpl_modal->setVar('BLOQUE_TIPO',$strListado);
        }
        
        
	/**
	 * @return string
	 * @see GBLOutput::getOutput()
	 */
	public function getOutput(){
		$gui=GUIController::getInstance();		
                $tpl_version=$gui->getTPLVersion();
		$tpl = new GBLTemplate('USUClienteOU.tpl', 'v4/private/class/USU/TPL/'.$tpl_version, 'v4/private/data/precompilados/usu/'.$tpl_version);
		
                $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), $this->plan);
                
                $this->setTabMiCuenta($tpl);
              
    		if($permisos->getPermisosCuenta() >= USUPermisos::VISUALIZACION){
                    $this->addJS($gui);
                    $this->setTabDatos($tpl,$permisos->getPermisosCuenta());
                    $this->setTabPedidos($tpl);
                    
                    if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
                        $tpl_modal = new GBLTemplate('USUClienteModalOU.tpl', 'v4/private/class/USU/TPL/'.$tpl_version, 'v4/private/data/precompilados/usu/'.$tpl_version);
                        $this->setModal($tpl,$tpl_modal);
                        $this->setAsistente($tpl_modal);
                                
                        if (USUPlanMDL::getInstance($this->plan)->getImagenGUI()!=false){ 
                            $tpl->setVarBlock('EDITAR_IMAGEN', 'SRC_IMAGE', '/v3/uploads/'.$this->plan.'/cabeceras/'.USUPlanMDL::getInstance($this->plan)->getImagenGUI());
                            $tpl->setVarBlock('BLOQUE_ADMINISTRACION','EDITAR_IMAGEN', $tpl->parseBlock('EDITAR_IMAGEN'));
                            $tpl->setVarBlock('BLOQUE_ADMINISTRACION','MOSTRAR_SUBIR', 'hidden');
                        }else{
                            $tpl->setVarBlock('BLOQUE_ADMINISTRACION','MOSTRAR_SUBIR', '');
                        }

                        $tpl->setVar('BOTONES_ADMINISTRACION',$tpl->parseBlock('BOTONES_ADMINISTRACION'));
                        $tpl->setVar('ENLACES_ADMINISTRACION',$tpl->parseBlock('ENLACES_ADMINISTRACION'));
                        $tpl->setVar('BLOQUE_ADMINISTRACION',$tpl->parseBlock('BLOQUE_ADMINISTRACION'));
                        $tpl->setVar('MODAL_ADMINISTRACION',$tpl_modal->parse());
                    }
                }

		return $tpl->parse();
	}
	
	private function creaBotonPayPalSuscripcion($importe,  UTLDateTime $fecha,  $meses=0){
		
		
		$subscription_details = array(
			'max_failed_payments'=> 0,
			'add_to_next_bill'   => false,
			'description'        => LANGBase::__('Suscripcion a plan mensual %%',array($this->objPlan->getTipoPlanNombre())),
			'initial_amount'     => '0.00',
			'amount'             => number_format($importe,2,'.',''),
			'period'             => 'Month',
			'frequency'          => '1',
			'total_cycles'       => $meses,
			'start_date'         => $fecha->getInTimeZone('GMT','Y-m-d\TH:i:s\Z')
		);
		
		PAYPAL\Utilidades::setCredentials();
		GBLSession::_setValor('PAYPAL_SUSCRIPCION', $subscription_details);
		$paypal_subscription = new PAYPAL\Subscription( $subscription_details );
		
		$locale=LANGBase::getInstance()->getCurrentLocale();
		if ($locale=='ca_ES'){ $locale='es_ES';}
		
		return $paypal_subscription->get_buy_button(array('locale'=>  $locale)).$paypal_subscription->get_script();
	}
	
	/**
	 *
	 * @param type $dataGET
	 * @param type $dataPOST 
	 */
	public function runAjax($dataGET, $dataPOST) {
                                
		$data=array('ok'=>false);
		
		switch($dataPOST['modulo']){
                        
			case 'CUENTA_PEDIDOS_TABLA':
                            try{
                                $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                if($permisos->getPermisosCuenta() >= USUPermisos::VISUALIZACION){
                                    $this->generaGridPedidos();
				    $this->gridPedidos->initParamsAjax($dataPOST);
                                    $html_grid=$this->gridPedidos->getOutput();
                                    $data=array('table'=>str_replace(array("\n","\r","\t"),'',$html_grid));
                                }else{
                                    UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
                                }
                            }catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
				}
                            break;
			case 'CUENTA_PEDIDOS_DETALLE':
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::VISUALIZACION){
					$pedidoOU=new FACTPedidoOU($dataGET['id']);
					if($pedidoOU->objPedido->objPlan->getPlanName()!=GBLSession::getPlan()){
						UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
					}
					$data['html']=$pedidoOU->getOutput();
                                    }else{
                                       UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>'); 
                                    }
				}catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
				}
				break;
			case 'CUENTA_PEDIDOS_VERFACTURA':
                              
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::VISUALIZACION){
                                        $objfact = new FACTFactura(null, $dataGET['id']);
                                        if ($objfact->objPedido->objPlan->getPlanName()!=GBLSession::getPlan()){
                                                UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
                                        }
					$file=$objfact->getRutaPDF();
					if (is_file($file)){
						header("Content-Type: application/pdf");
						header("Content-Disposition: inline; filename=\"factura_".$objfact->getID().".pdf\"");
						echo file_get_contents($file);
						die();
					}else{
						UTLHttp::sendErrorInternoDeServidor('', new TeException('No se ha encontrado el documento PDF de la factura',__LINE__,__CLASS__));
					}
                                    }else{
                                       UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>'); 
                                    }
				}catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
				}
				break;
			case 'PERSONALIZAR_CABECERA_ELIMINAR':
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
					$objPlan=USUPlanMDL::getInstance($this->plan);
					$objPlan->setImagenGUI('');
					$objPlan->saveToDB();
					$data['ok']=true;
                                    }else{
                                        UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');   
                                    }
				}catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
				}
				break;
			case 'PERSONALIZAR_CABECERA_SUBIR_IMAGEN':
				$permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
                                    try{
					$objUSUUsuarios = new USUUsuarios($this->plan);
					$name = $objUSUUsuarios->uploadImg('imagen');
					if($name!=''){
						$objPlan=USUPlanMDL::getInstance($this->plan);
						$objPlan->setImagenGUI($name);
						$objPlan->saveToDB();
						$data['imagen_nueva'] = true;
					}
					$data['ok']=true;
                                    }catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
                                    }   
                                    //Salida a un iframeAjax
                                    echo "<script type='text/javascript'>top.USUCliente.respuestaImgCabecera(".json_encode($data).");</script>";
                                    die();
                                }else{
                                    UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
                                    $data=array('ok'=>false,'error'=>$e->getMessage());
                                }
				break;
			case 'PERSONALIZAR_CABECERA_ACTUALIZAR_IMAGEN':
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
					$objPlan=USUPlanMDL::getInstance($this->plan);
					$objPlan->clearSinglenton();
					$data['src_imagen'] = $objPlan->getRaizPiezas().'/v3/uploads/'.$this->plan.'/cabeceras/'.$objPlan->getImagenGUI();
					$data['ok'] = true;
                                    }else{
                                        UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');   
                                    }
				}catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
				}
				break;
			case 'CUENTA_CANCELAR':
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
					$objPlan=USUPlanMDL::getInstance($this->plan);
					$objPlan->setBorrado(self::CUENTA_CANCELADA);
					$objPlan->saveToDB();
					
					$objUsuario=USUUsuarioMDL::getUsuarioActivo();
					
					TICKBase::init()->iniciaTicket($objUsuario->getEmail(), 'Solicitud de cancelar cuenta', 'Se ha solicitado la cancelación del plan "'.$objPlan->getPlanName().'" desde la sección cuenta, el plan ha sido cancelado por el usuario '.$objUsuario->getUser(),'Ninguno',$objPlan->getId());
					$data['ok'] = true;
                                    }else{
                                        UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');   
                                    }
				}catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage());
				}
				break;
			case 'CUENTA_CAMBIO_TIMEZONE':
				if (isset($dataPOST['timezone'])){
                                    try{
                                        $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                        if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
						$objPlan=USUPlanMDL::getInstance($this->plan);
						$objPlan->setTimeZone($dataPOST['timezone']);
						$objPlan->saveToDB();
						$objPlan->clearSinglenton();
						$data['ok'] = true;
                                        }else{
                                            UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
                                        }
                                    }catch(TeException $e){
                                        $data=array('ok'=>false,'error'=>$e->getMessage());
                                    }
				}
				break;
			case 'CUENTA_CAMBIO_DATOS':
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
					$dataPOST['nif']=str_replace(array('-',' ','.'),'',$dataPOST['nif']);
					$objCliente=new CLICliente($this->plan);
					$objCliente->modificaFicha($dataPOST);
					$data=array('ok'=>true);
                                    }else{
                                        UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
                                    }
				}catch(TeException $e){
					$data=array('ok'=>false,'error'=>$e->getMessage(),'cod'=>$e->getCode());
				}
				
				break;
			case 'CUENTA_CANCELAR_SUSCRIPCION':
				try{
                                    $permisos=USUPermisos::getInstance(GBLSession::getUsuario(), GBLSession::getPlan());
                                    if($permisos->getPermisosCuenta() >= USUPermisos::ADMINISTRACION){
					$cod=$this->objPlan->getCodigoSuscripcion();
					$aviso=new AVIAvisoCancelacionSuscripcion($this->plan);
					if (substr($cod,0,2)==FACTPedido::MODO_PAGO_RECURRENTE_PAYPAL){
						$profile_id=substr($cod,3);
						PAYPAL\Utilidades::cancelSuscriptionPaypal($profile_id);
					}else{
						$this->objPlan->setCodigoSuscripcion(0);
						$this->objPlan->saveToDB();
					}
					$aviso->send();
					$data=array('ok'=>true);
                                    }else{
                                        UTLHttp::sendForbidden('<h1>403: '.LANGBase::__('No tiene permisos para el contenido solicitado').'</h1>');
                                    }
				}catch(Exception $e){
					$data=array('ok'=>false,'error'=>$e->getMessage(),'cod'=>$e->getCode());
				}
				break;
			case 'CUENTA_BOTON_SUSCRIPCION':
				
				$datos=json_decode($dataPOST['data'], true);
				
				$importe=$datos[0];
				$fecha_suscripcion_desde= new UTLDateTime($datos[1]);
				$meses=$datos[2];
				
				$boton=$this->creaBotonPayPalSuscripcion($importe,$fecha_suscripcion_desde,$meses);
				
				$data=array('ok'=>true,'html'=>$boton);
				
				break;
			}			
		return json_encode($data);
	}
}

?>
