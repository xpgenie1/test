<?php
require_once "v4/private/class/GBL/GBLOutput.php";
require_once 'v4/private/class/GBL/GBLTemplate.php';
require_once 'v4/private/class/GBL/GBLSession.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/GUI/GUIController.php';
require_once 'v4/private/class/LIM/LIMCheck.php';
require_once 'v4/private/class/GRID/GRIDGridOU.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/USU/USUUsuarios.php';
require_once 'v4/private/class/USU/USUGrupos.php';
require_once 'v4/private/class/USU/USUGrupoMDL.php';
require_once 'v4/private/class/USU/USUUsuarioMDL.php';
require_once 'v4/private/class/USU/USUPlanMDL.php';
require_once 'v4/private/class/BDB/BDBase.php';
require_once 'v4/private/class/PER/PERPermisos.php';
require_once 'v4/private/class/PER/PERPermisosMDL.php';
require_once 'v4/private/class/UTL/UTLLog.php';

class USUListadoOU implements GBLOutput,  AJAXRequest{

	/**
	 * @var string
	 */
	private $tpl;

	/**
	 * @var GRIDGridOU
	 */
	private $grid;

	/**
	 * @var GRIDGridOU
	 */
	private $grid_grupos;

	/**
	 * @var string
	 */
	private $tpl_version;

	/**
	 * @var string
	 */
	private $plan;

	/**
	 * @var USUUsuarios
	 */
	private $objUsuarios;
	
	/**
	 * @var USUUsuarioMDL
	 */
	private $objCurrentUser;
	
	public function __construct($plan){
		if (empty($plan)) throw new TeException("No se ha pasado al constructor el plan.", __LINE__, __CLASS__);
		$this->plan = $plan;
		$this->objCurrentUser = USUUsuarioMDL::getUsuarioActivo();
		$this->tpl_version = GUIController::getInstance()->getTPLVersion();
		$this->tpl = new GBLTemplate('USUListadoOU.tpl','v4/private/class/USU/TPL/'.$this->tpl_version,'v4/private/data/precompilados/usu/'.$this->tpl_version);
		$this->gui=GUIController::getInstance();
			
		$bdBase=new BDBase($this->plan);
		
		$SQL="Select id,nombre as Nombre,apellidos as Apellidos, user as 'Usuario.Plan',email as Email,id as Administrador, borrado as Estado from usuarios where borrado!=1 ";
		
		if (!PERPermisos::checkSection('usuarios')){
			$SQL .=" AND id=".$this->objCurrentUser->getId();
		}
			
		$this->grid = new GRIDGridOU($SQL,$bdBase->BD2);
		$this->grid->setName('tablaUsuarios');
		$this->grid->setTplVersion($this->tpl_version);
		$this->grid->setCamposBusqueda(array('user'=>LANGBase::__('Usuario'), 'email'=>LANGBase::__('Email')));
		if ($this->objCurrentUser->isAdmin()){
			$this->grid->setAccionesRegistro(array('eliminar'=>LANGBase::__('Eliminar'), 'reenviar'=>LANGBase::__('Reenviar validación')));
		}
		$this->grid->setColumnasExcluidas(array('id'));
		$this->grid->setOrder('id','desc');
		$this->grid->setPagesize(10);
		$this->grid->setPage(1);
		$this->grid->addJSCSS($this->gui);
		$this->grid->setParseoColumna(function($campo,$data){
			switch(strtolower($campo)){
				case 'fin':
					$obj=new UTLDateTime($data);
					$data=$obj->toString().'h';
					break;
				case 'inicio':
					$obj=new UTLDateTime($data);
					$data=$obj->toString().'h';
					break;
				case 'estado':
					switch($data){
						case 0:
                                                        $data= LANGBase::__("Validado");
                                                break;
                                                case 3:
                                                        $data= LANGBase::__("Sin validar");
                                                        break;
					}
					break;
				case 'usuario.plan':
					$data.='.'.$this->plan;
					break;
				case 'administrador':
					$objUser=new USUUsuarioMDL(null,  GBLSession::getPlan(),(int)$data);
					if ($objUser->isAdmin()){
						$data='<i class="icon-check"></i> '.LANGBase::__("Si");
					}else{
						$data='<i class="icon-check-empty"></i> '.LANGBase::__("No");
					}
					break;
			}
			return $data;
		});
		
		/** Intenta crear la tabla de grupos de usuarios */
		$this->objUsuarios=new USUUsuarios($this->plan);
		$this->objUsuarios->checkUsersGroupsTable();
		
		if ($this->objCurrentUser->isAdmin() && USUPlanMDL::getInstance($this->plan)->isGratuito()==false){
			
			$this->grid_grupos = new GRIDGridOU("Select id as Id, nombre as Grupo,descripcion as 'Descripción', IF(id<100,'system','custom') as Tipo, usuarios as Usuarios FROM usuarios_grupos WHERE borrado=0",$bdBase->BD2);
			$this->grid_grupos->setName('tablaUsuariosGrupos');
			$this->grid_grupos->setTplVersion($this->tpl_version);
			$this->grid_grupos->setCamposBusqueda(array('descripcion'=>LANGBase::__('Descripción'),'nombre'=>  LANGBase::__('Nombre')));
			$this->grid_grupos->setAccionesRegistro(array('editar'=>  LANGBase::__('Editar'), 'eliminar'=>LANGBase::__('Eliminar')));
			$this->grid_grupos->setColumnasExcluidas(array('id'));
			$this->grid_grupos->setOrder('id','asc');
			$this->grid_grupos->setPagesize(10);
			$this->grid_grupos->setPage(1);
			$this->grid_grupos->addJSCSS($this->gui);
			$this->grid_grupos->setColumnasExcluidas(array('Elementos'));
			$this->grid_grupos->setParseoColumna(function($campo,$data,$fila){
				switch(strtolower($campo)){
					case 'descripción':
						$data= USUGrupos::getDescripcionInLocale($data);
						break;
					case 'usuarios':
						$data=((strlen(trim($data))===0) ? 0 : substr_count($data,',')+1);
						
						if ($fila['Id']>99){
							$id=$fila['Id'];
							$subSQL="
								select sum(num) as numero from (
									SELECT count(*) as num from `contactos` WHERE grupo = '$id' and borrado=0 
									UNION
									SELECT count(*) from `grupos` WHERE grupo = '$id' and borrado=0 
									UNION
									SELECT count(*) from `piezas` WHERE grupo = '$id' and borrado=0 
									UNION
									SELECT count(*) from `envios` WHERE grupo = '$id' and borrado=0 
									UNION
									SELECT count(*) from `envios_sms` WHERE grupo = '$id' and borrado=0 
									UNION
									SELECT count(*) from `remitentes` WHERE grupo = '$id' and borrado=0
								) as a";
							$rs=BDBase::$staticBD2->query($subSQL);

							if ($rs && $rs->num_rows==1){
								$elementos=  $rs->fetch_row()[0];
								$data.='<span class="num_elementos" data-numelementos="'.$elementos.'"></span>';
							}
						}
						break;
					case 'tipo':
						switch($data){
							case 'system':
								$data= LANGBase::__('Perfil - Grupo del sistema');
								break;
							case 'custom':
								$data= LANGBase::__('Grupo personalizado');
								break;
						}
						break;
					case 'elementos':
						
						
						break;
								
				}
				return $data;
			});
		
		}
		
		if ($this->objCurrentUser->isAdmin()===false){
			//Mostramos los administradores del plan
			$objGrupo=new USUGrupoMDL($this->plan,1);
			$admins=$objGrupo->getUsuarios();
			$htmlAdmins="";
			foreach($admins as $id_admin){
				$objAdmin=new USUUsuarioMDL(null,$this->plan,$id_admin);
				$htmlAdmins.='<tr><td>&nbsp;</td><td>'.$objAdmin->getNombre().'</td><td>'.$objAdmin->getApellidos()."</td><td>".$objAdmin->getUser().".".$this->plan.'</td><td>'.$objAdmin->getEmail()."</td></tr>";
			}
			$this->tpl->setVarBlock('BLOQUE_ADMINS','ADMINS', $htmlAdmins);
			$this->tpl->setVar('BLOQUE_ADMINS',$this->tpl->parseBlock('BLOQUE_ADMINS'));
			$this->tpl->setVar('BLOQUE_IS_NOT_ADMIN',$this->tpl->parseBlock('BLOQUE_IS_NOT_ADMIN'));
		}
	}
	
	/**
	 * Genera la salida HTML
	 * @see GBLOutput::getOutput()
	 */
	public function getOutput(){
			
		$this->gui->addJS('/v4/public/js/UTL/jquery-last.js');
		$this->gui->addJS('/v4/public/js/LIM/LIMAvisos.js');
		$this->gui->addJS('/v4/public/js/USU/listado.js');
		$this->gui->addJS('/v4/public/js/UTL/jquery-ui-last.js');
		
		$this->gui->addCSS('/v4/public/css/UTL/jquery-ui-last.css');
		$this->gui->addCSS('/v4/public/css/GUI/toggle_switch.css');
		
                $this->tpl->setVar('TABLA',$this->grid->getOutput());
		
		$objPlan=USUPlanMDL::getInstance($this->plan);
		
		if ($this->objCurrentUser->isAdmin() && $objPlan->isGratuito()==false){
			$this->tpl->setVar('TABLA_GRUPOS',$this->grid_grupos->getOutput());
		}
		
		UTLIni::addIniFile('v4/private/conf/usu_campos.ini', 'CAMPOS');
		$categorias=UTLIni::getConfig('CATEGORIAS', 'CAMPOS');
                $mbcaObj= MBCAConf::getInstance($this->plan);
                try{
                        UTLIni::addIniFile('v4/private/conf/'.$mbcaObj->getCSSPrefijoValue().'oauth.ini','OAUTH');
                        $oauths=  UTLIni::$conf['OAUTH'];
                }catch(TeException $e){  
                        $oauths=  array();
                }
                
		
		$readonly=array('id','fecha_mod','fecha_add', 'email', 'password');
		
		$campos_teenvio = $this->objUsuarios->getCampos();
		$campos_teenvio['user']=  LANGBase::__('Usuario');
		$campos_teenvio['id']='id';
		$campos_teenvio['fecha_add']=  LANGBase::__('Fecha de inserción');
		$campos_teenvio['fecha_mod']=  LANGBase::__('Fecha de modificación');
			
		foreach($categorias as $cat=>$txtcampos){
			$campos=explode(',',$txtcampos);
			$html='';
			
			foreach($campos as $clave){
				if ($clave=="password"){
					$bloque='BLOQUE_PASSWORD';
					$this->tpl->setVarBlock($bloque,'READONLY', 'readonly');
					$html.=$this->tpl->parseBlock($bloque,true);
				}
			
	
				if (!isset($campos_teenvio[$clave])) continue;

				//Bloques textarea
				$bloque='BLOQUE_INPUT_CONTACTOS';
				if ($clave=="observaciones"){
					$bloque='BLOQUE_TEXT_CONTACTOS';
				}
	
				//Quitamos la coletilla "Empresa - "
				$label=$campos_teenvio[$clave];
				if ($cat=='empresa'){
					$label=str_replace('Empresa - ','',$label);
				}
		
				
				//Campos de solo lectura
				if (in_array($clave, $readonly)){
					$this->tpl->setVarBlock($bloque,'READONLY', 'readonly');
				}
				
				//Limitacion de longitud
				if ($clave=="user"){
					$this->tpl->setVarBlock($bloque,'MAXLENGTH', 'maxlength="20"');
				}
				
				$this->tpl->setVarBlock($bloque,'LABEL', LANGBase::__($label));
				$this->tpl->setVarBlock($bloque,'NOMBRE', $clave);
				$html.=$this->tpl->parseBlock($bloque,true);
				
				if ($clave=="user"){
					$bloque='BLOQUE_PLAN';
					$this->tpl->setVarBlock($bloque,'PLAN',$this->plan);
					$html.=$this->tpl->parseBlock($bloque,true);
				}
			}
			
			
			$this->tpl->setVar('PLAN', $this->plan);
			$this->tpl->setVar('CAMPOS_'.strtoupper($cat),$html);
		}
		//Conexiones con redes sociales
		$str_sociales='';
		foreach($oauths as $red_social=>$data){
			
			$this->tpl->setVarBlock('BLOQUE_OAUTH_PROVIDER','NOMBRE',  strtolower($red_social));
			$this->tpl->setVarBlock('BLOQUE_OAUTH_PROVIDER','CLASS',  $data['class']);
			$str_sociales.=$this->tpl->parseBlock('BLOQUE_OAUTH_PROVIDER');
		}
		if(!empty($str_sociales)){
                        $this->tpl->setVarBlock('BLOQUE_EXTERNAL_OAUTH','BOTONES_OAUTH',$str_sociales);
                        $this->tpl->setVar('BLOQUE_EXTERNAL_OAUTH',$this->tpl->parseBlock('BLOQUE_EXTERNAL_OAUTH'));
                }
                
                //Limitaciones de seguridad
                $objCheck = new LIMCheck($this->plan);
                $langbase = LANGBase::getInstance();
                $idioma = $langbase->getCurrentLocale();
                if(!$objCheck->usarSecureNet()) $this->tpl->setVar('SECURE_NET','disabled');
                
                if(!$objCheck->usarDoubleOptEmail()){
                        $this->tpl->setVarBlock('BLOQUE_DOUBLE_OPT_EMAIL_DISABLED','LANG',$idioma);
                        $this->tpl->setVar('BLOQUE_DOUBLE_OPT_EMAIL_DISABLED',$this->tpl->parseBlock('BLOQUE_DOUBLE_OPT_EMAIL_DISABLED'));
                }else{
                        $this->tpl->setVar('BLOQUE_DOUBLE_OPT_EMAIL_ENABLED',$this->tpl->parseBlock('BLOQUE_DOUBLE_OPT_EMAIL_ENABLED'));
                }
                
                if(!$objCheck->usarDoubleOptSMS()){ 
                        $this->tpl->setVarBlock('BLOQUE_DOUBLE_OPT_SMS_DISABLED','LANG',$idioma);
                        $this->tpl->setVar('BLOQUE_DOUBLE_OPT_SMS_DISABLED',$this->tpl->parseBlock('BLOQUE_DOUBLE_OPT_SMS_DISABLED'));
                }else{
                        $this->tpl->setVar('BLOQUE_DOUBLE_OPT_SMS_ENABLED',$this->tpl->parseBlock('BLOQUE_DOUBLE_OPT_SMS_ENABLED'));
                }
                
                if(!$objCheck->usarDoubleOpySecureNet()) $this->tpl->setVar('DOUBLE_OPT_SECURE_NET','disabled');
                
                
		//Grupos de usuarios
		if ($this->objCurrentUser->isAdmin() && $objPlan->isGratuito()==false){
			
			$this->tpl->setVar('BLOQUE_IS_ADMIN_A',$this->tpl->parseBlock('BLOQUE_IS_ADMIN_A'));
			$this->tpl->setVar('BLOQUE_IS_ADMIN_B',$this->tpl->parseBlock('BLOQUE_IS_ADMIN_B'));
			$this->tpl->setVar('BLOQUE_IS_ADMIN_C',$this->tpl->parseBlock('BLOQUE_IS_ADMIN_C'));
			
			$objUSUGrupos=new USUGrupos($this->plan);
			$gruposPlan=$objUSUGrupos->getGrupos();
			$html_grupos_system='';
			$html_grupos_personalizados='';

			foreach ($gruposPlan as $grupo){
				
				$this->tpl->setVarBlock('BLOQUE_GRUPO', 'GRUPO_ID',$grupo['id']);
				$this->tpl->setVarBlock('BLOQUE_GRUPO', 'GRUPO_NOMBRE',$grupo['nombre']);
				$this->tpl->setVarBlock('BLOQUE_GRUPO', 'GRUPO_DESCRIPCION',USUGrupos::getDescripcionInLocale($grupo['descripcion']));
				
				if ($grupo['id']<100){
					$html_grupos_system.=$this->tpl->parseBlock('BLOQUE_GRUPO', true);
				}else{
					$html_grupos_personalizados.=$this->tpl->parseBlock('BLOQUE_GRUPO', true);
				}
			}

			$this->tpl->setVar('GRUPOS_SISTEMA',$html_grupos_system);
			
			if ($html_grupos_personalizados!=''){
				$this->tpl->setVarBlock('BLOQUE_GRUPOS_PERSONALIZADOS', 'GRUPOS_PERSONALIZADOS', $html_grupos_personalizados);
				$this->tpl->setVar('BLOQUE_GRUPOS_PERSONALIZADOS',$this->tpl->parseBlock('BLOQUE_GRUPOS_PERSONALIZADOS'));
			}

			//Permisos por defecto

			$html_acordeon='';
			
			foreach(PERPermisosMDL::OBJ_LIST as $bloque){
				$this->tpl->setVarBlock('BLOQUE_ACORDEON_PERMISOS_DEFECTO', 'PREFIJO',$bloque);
				$this->tpl->setVarBlock('BLOQUE_ACORDEON_PERMISOS_DEFECTO', 'DDL_GRUPOS_USUARIOS',$objUSUGrupos->getDDLGrupos('perdefault_'.$bloque.'_grupoitem'));
				$this->tpl->setVarBlock('BLOQUE_ACORDEON_PERMISOS_DEFECTO', 'NOMBRE',  LANGBase::__(str_replace('_',' ',ucfirst($bloque))));
				
				$html_acordeon.=$this->tpl->parseBlock('BLOQUE_ACORDEON_PERMISOS_DEFECTO');
			}
			
			$this->tpl->setVar('BLOQUE_ACORDEON_PERMISOS_DEFECTO',$html_acordeon);
			
		}
		
		return $this->tpl->parse();
	
	}

	/**
	 * @param array $dataGET
	 * @param array $dataPOST
	 * @return string
	 */
	public function runAjax($dataGET, $dataPOST){
		$data=array('ok'=>false);

		switch($dataGET['modulo']){
			case 'USUARIOS_TABLA':

				$this->grid->initParamsAjax($dataPOST);
				$html_grid=$this->grid->getOutput();
				$data=array('table'=>str_replace(array("\n","\r","\t"),'',$html_grid));
				break;
					
			case 'USUARIOS_TABLA_GRUPO':
				if ($this->objCurrentUser->isAdmin() && USUPlanMDL::getInstance($this->plan)->isGratuito()==false){
					$this->grid_grupos->initParamsAjax($dataPOST);
					$this->grid_grupos->setColumnasExcluidas(array('Elementos'));
					$html_grid=$this->grid_grupos->getOutput();
					$data=array('table'=>str_replace(array("\n","\r","\t"),'',$html_grid));
				}else{
					$data=array('table'=>'');
				}
				break;
			case 'USUARIOS_DATOS':
				if (isset($dataGET['id'])){
					try{
						$objUsuario=new USUUsuarioMDL(null,$this->plan,$dataGET['id']);
				
						$data['ok']=true;
						$data['id_sesion'] = USUUsuarioMDL::getUsuarioActivo()->getId();
						$data['campos']=$objUsuario->toDBArray();
						unset($data['campos']['password']);
						$data['campos']['fecha_add']=$objUsuario->getFechaAlta()->getDate();
						$data['campos']['fecha_mod']=$objUsuario->getFechaModificacion()->getDate();
						$data['campos']['estado']=$objUsuario->getEstado();
						$data['grupos']=array_keys($objUsuario->getGrupos());
						
						# Redes sociales
						$rsoArray=$objUsuario->getRedesSocialesOAuth();
						$data['rso']=array();
						foreach($rsoArray as $rsoData){
							if (isset($rsoData['data'])){
								$data['rso'][$rsoData['oauth_provider']]=$rsoData['data'];
							}
						}
						# Seguridad
						$data['aviso_en_login']=$objUsuario->avisaAlHacerLogin();
						$data['caduca_password']=$objUsuario->caducaPassword();
						$data['doble_factor_email']=$objUsuario->getDobleFactorAutenticacionEmail();
						$data['doble_factor_sms']=$objUsuario->getDobleFactorAutenticacionSMS();
						$data['red_segura']=$objUsuario->getRedSegura();
						$data['red_permitida']=$objUsuario->getRedPermitida();
						
						# Perfisos por defecto
						$data['permisos_defecto']=array();
						foreach (PERPermisosMDL::OBJ_LIST as $obj){
							$permisos=$objUsuario->getPermisosDefecto($obj);
							$permisos['propietario']=(int) $permisos['permisos'][0];
							$permisos['grupo']=(int) $permisos['permisos'][1];
							$permisos['resto']=(int) $permisos['permisos'][2];
							unset($permisos['obj']);
							$data['permisos_defecto'][$obj]=$permisos;
						}
						
					}catch (TeException $ex) {
						$data['ok'] = false;
						$data['error']=LANGBase::__('Error al sacar los datos del usuarios');
						$data['ex']=$ex->getMessage();
					}
				}
			break;
			case 'USUARIOS_GUARDAR':
				try{
					$data['ok'] = true;
					$objCheck = new LIMCheck($this->plan);

					//Nuevo usuario, compruebo que no este limitado
					if (!$objCheck->nuevoUsuario()){
						$data['ok'] = false;
						$data['limit'] = true;
					}
						
					if ($data['ok'] == true || isset($dataGET['id']) && is_numeric($dataGET['id'])){
						if(isset($dataGET['id']) && is_numeric($dataGET['id']) && $dataGET['id']>0){
							$id_usuario = $dataGET['id'];
						}else{ 
							$id_usuario = null; 
						
						}
						
						$logueado=  USUUsuarioMDL::getUsuarioActivo();
						
						$objUsuario=new USUUsuarioMDL(null,$this->plan,$id_usuario);
						$ok = true;
						$user_old =$objUsuario->getUser();//Para cerrar sesión si cambia el nombre d user
						
						$permisos_default=array();
						
						foreach($dataPOST as $campo=>$valor){
							if ($campo=='id') continue;
							if (substr($campo,0,6)=='group_') continue;
							if ($campo=='grupos') continue;
							if ($id_usuario===null && $campo=='nombre' && trim($valor)==''){
								//Si no pasa nombre, se guarda el nombre de usuario como nombre para que no quede en blanco
								$valor=$dataPOST['user'];
							}
							
							# User
							if ($campo=='user'){
								if ($valor!=$objUsuario->getUser()){
									$objUSUsuario = new USUUsuarios($this->plan);
									$ok = $objUSUsuario->checkUserName($valor);
								}
							}
							# Red Social
							if (substr($campo,0,4)=='rso_'){
								if ($valor==''){
									//Red social revocada
									$rso=  substr($campo, 4);
									try{
										$oauth=HOMELogginOAuthFactory::getInstance($rso);

										$data=$oauth->getOAuthTokenData($objUsuario->getUser(), $this->plan);
										if (isset($data['oauth_token'], $data['oauth_secret'])){
											$oauth->invalidateAuth($data['oauth_token'], $data['oauth_secret']);
										}
										$oauth->deleteOAuthUser($objUsuario->getUser(), $this->plan);
									}catch(TeException $e){}
								}
							}
							# Password
							if ($campo=='password'){
								if( !isset($id_usuario)){
									$randompass = BDB\Utilidades::generarPassword(8);
									$valor = $randompass;
								}
								GBLSession::_setValor('passtemp',$valor);
							}

							# Seguridad
							if ($campo=='email_aviso_login'){
								$objUsuario->setAvisaAlHacerLogin((boolean)$valor);
							}
							
							if ($campo=='caduca_password'){
								if ($valor && $objUsuario->getFechaCaducidadPassword()===null){
									$objUsuario->setCaducaPassword(true);
								}
								if ($valor == false && $objUsuario->getFechaCaducidadPassword() !==null){
									$objUsuario->setCaducaPassword(false);
								}
							}
							
							if ($campo=='doble_factor_email'){
								$objUsuario->setDobleFactorAutenticacionEmail($valor==="1");
							}
							
							if ($campo=='doble_factor_sms'){
								$objUsuario->setDobleFactorAutenticacionSMS($valor==="1");
							}
							
							if ($campo=='red_segura'){
								$objUsuario->setRedSegura($valor);
							}
							
							if ($campo=='red_permitida'){
								$objUsuario->setRedPermitida($valor);
							}
							
							# Permisos por defecto
							if ($logueado->isAdmin() && substr($campo, 0,11)=='perdefault_'){
								
								$last_part=strrpos($campo,'_');
								$obj=substr($campo,11,$last_part-11);
								$ambito=substr($campo,$last_part+1);
								
								if ($ambito=='propietario' || $ambito=='grupo' || $ambito=='resto' || $ambito=='grupoitem'){
									if (!isset($permisos_default[$obj])) $permisos_default[$obj]=array();
									$permisos_default[$obj][$ambito]=$valor;
								}
								
							}
							
							# datos personales y de empresa
							$objUsuario->setCampo($campo, $valor);

						}
						
						# Permisos por defecto
						if ($logueado->isAdmin()){
							foreach ($permisos_default as $obj=>$ambito){

								$permisos=$ambito['propietario'].$ambito['grupo'].$ambito['resto'];
								$grupo=$ambito['grupoitem'];

								$objUsuario->setPermisosDefecto($obj,$permisos, $grupo);

							}
						}

						#datos personales y de empresa
						if ($ok==true){
							$data['ok']=true;
							$data['logout'] = false;
							
							$id=$objUsuario->saveToDB();
							
							$data['id']=$id;
							$data['id_orig']=$id_usuario;
							
							# Grupos de usuarios asignados
							if ($logueado->isAdmin() && USUPlanMDL::getInstance($this->plan)->isGratuito()==false && isset($dataPOST['grupos']) && is_array($dataPOST['grupos']) && count($dataPOST['grupos'])>0){
								
								$objGrupos=new USUGrupos($this->plan);
								
								$grupos_originales=array();

								if ($id_usuario!==null){
									#Elimino los grupos desmarcados si el id_usuario viene originalmente (usuario existente)
									$grupos_originales=array_keys($objUsuario->getGrupos());
									foreach($grupos_originales as $id){
										if (!in_array($id,$dataPOST['grupos'])){
											#Grupo a eliminar
											$objGrupos->delUser($id_usuario, $id);
										}
									}
								}
								#agrego los nuevos grupos
								foreach($dataPOST['grupos'] as $id_grupo){
									if (!in_array($id_grupo,$grupos_originales)){
										$objGrupos->addUser($objUsuario->getId(), $id_grupo);
									}
								}
							}
							
							UTLLog::guardaLog('USUUsuarios_'.$this->plan, ($id_usuario===null) ? 'NUEVO' : 'ACTUALIZADO', json_encode(array('ficha'=>$objUsuario->toDBArray(),'grupos'=>$dataPOST['grupos'])));
							
							if (GBLSession::getUsuario()==$user_old){
								if ($user_old != $dataPOST['user'] && $dataPOST['id']==$objUsuario->getId()) $data['logout'] = true;
							}
						}else{
						      $data['ok']=false;
						      $data['id']=0;
						      $data['id_orig']=0;
						      $data['detalle']=LANGBase::__('Ya existe un usuario con ese nombre');
						}
					}
					
				} catch (TeException $ex) {
					$data['ok'] = false;
					$data['id']=0;
					$data['id_orig']=0;
					$data['error']=LANGBase::__('Error al guardar los datos del usuario');
					$data['detalle']=$ex->getMessage();
					$data['cod_error']=$ex->getCode();
				}
			break;
			case 'USUARIO_REGENERA_TOKEN':
				try{
					if (isset($dataGET['id'])){
						$id_usuario=$dataGET['id'];
						
						$objUsuario=new USUUsuarioMDL(null,$this->plan,$id_usuario);
						
						$oauthToken = new HOMELogginOAuthToken();
						$token=$oauthToken->generateToken($this->plan, $objUsuario->getUser());

						$data['ok'] = true;
						$data['token']= $token;
					}else{
						throw new TeException('No se ha pasado los datos necesarios', __LINE__,__CLASS__);
					}
				} catch (TeException $ex) {
					$data['ok'] = false;
					$data['error']=LANGBase::__('Error al guardar los datos del usuario');
					$data['detalle']=$ex->getMessage();
					$data['cod_error']=$ex->getCode();
				}
			break;
			case 'USUARIO_DATOS_GRUPO':
				if (isset($dataGET['id'])){
					try{
						$objGrupo=new USUGrupoMDL($this->plan,$dataGET['id']);
						
						$data['ok']=true;
						$data['id']=$objGrupo->getId();
						$data['nombre']=$objGrupo->getNombre();
						$data['descripcion']=$objGrupo->getDescripcion();
						$data['tipo']=$objGrupo->getTipo();
						$data['usuarios']=$objGrupo->getUsuarios();
						
					}catch (TeException $ex) {
						$data['ok'] = false;
						$data['error']=LANGBase::__('Error al sacar los datos del grupo de usuarios');
						$data['ex']=$ex->getMessage();
					}
				}
			break;
			case 'USUARIO_GUARDAR_GRUPO':
				if (isset($dataPOST['id'])){
					try{
						$objGrupo=new USUGrupoMDL($this->plan);
						$objGrupo->setNombre($dataPOST['nombre']);
						$objGrupo->setDescripcion($dataPOST['descripcion']);
						$objGrupo->setTipo(USUGrupoMDL::TYPE_CUSTOM);
						if (trim($dataPOST['id'])!==''){
							$objGrupo->setId((int) $dataPOST['id']);
						}
						$data['id']=$objGrupo->saveToDB();
						$data['ok'] = true;
					} catch (TeException $ex) {
						$data['ok'] = false;
						$data['error']=LANGBase::__('Error al guardar los datos del grupo de usuarios');
						$data['detalle']=$ex->getMessage();
						$data['cod_error']=$ex->getCode();
					}
				}
			break;
			case 'USUARIO_ELIMINAR_GRUPO':
				if (isset($dataGET['id'])){
					try{
						$objGrupo=new USUGrupoMDL($this->plan,$dataGET['id']);
						$objGrupo->setBorrado(1);
						$objGrupo->saveToDB();
						$data['ok'] = true;
					} catch (Exception $ex) {
						$data['ok'] = false;
						$data['error']=LANGBase::__('Error al eliminar el grupo de usuarios');
						$data['detalle']=$ex->getMessage();
						$data['cod_error']=$ex->getCode();
					}
				}
			break;
			case 'USUARIO_REENVIAR_VALIDACION':
				try{
					//Se tiene que regenerar la contraseña (y guardarla en la BBDD) 
					$valor = BDB\Utilidades::generarPassword(8);
					GBLSession::_setValor('passtemp',$valor);
					$objUsuarioMDL=new USUUsuarioMDL(null,$this->plan,$dataPOST['id']);
					$objUsuarioMDL->setCampo('password', $valor);
					$objUsuarioMDL->saveToDB();
					//Se reenvía
					$objUsuario= new USUUsuarios($this->plan);
					$objUsuario->enviaValidacionUser($dataPOST['id']);
					$data['ok'] = true;
			
				}catch(TeException $e){
					$data['ok'] = false;
					$data['error']=LANGBase::__('Error al validar el usuarios');
				}
			break;
			case 'USUARIOS_ELIMINAR':
				try{
					$ids=$dataPOST['ids'];
					if (is_array($ids) && count($ids)==1 && $ids[0]=='-1'){
						$ids=$this->grid->getIdsSession();
					}
					$eliminar = new USUUsuarios($this->plan);
					$eliminar->eliminaUsuarios($ids);
					$id_usuario_actual= $this->objCurrentUser->getId();
					
					if (in_array($id_usuario_actual, $ids)){
						//Estoy inmolandome!
						$data['logout'] = true;
					}
					$data['ok'] = true;
				}catch(TeException $e){
					$data['ok'] = false;
					$data['error']=LANGBase::__('Error al intentar eliminar los usuarios. ');
                                        //$data['desc']=$e->getMessage();
					
					if ($e->getCode()!==403){
						$data['desc']=$e->getMessage();
					} else {
						$data['desc']=LANGBase::__('No puede eliminarse el único usuario administrador que existe.');
						$data['detalle']=$e->getMessage();
					}
					
				}
			break;
		}
		echo json_encode($data);
	}
		
}

?>
