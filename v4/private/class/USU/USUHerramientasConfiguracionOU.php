<?php
require_once 'v4/private/class/GBL/GBLOutput.php';
require_once 'v4/private/class/GBL/GBLTemplate.php';
require_once 'v4/private/class/GUI/GUIController.php';
require_once 'v4/private/class/CONT/CONTContactos.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/USU/USUPlanMDL.php';
require_once 'v4/private/class/PER/PERPermisos.php';
require_once 'v4/private/class/LANG/LANGBase.php';

/**
 * Objeto Output de Configuracion de Auxiliares
 * @author Javier Fernández Gutiérrez <javi.fernandez@ipdea.com>
 * @package CONF
 */

class USUHerramientasConfiguracionOU implements GBLOutput{
	
	/**
	 * @var string 
	 */
	private $plan;
	
	/**
	 * @var GUIController
	 */
	private $gui;
	
	/**
	 * @param string $plan
	 */
	public function __construct($plan) {
		
		if (PERPermisos::checkSection('herramientas')==false && PERPermisos::checkSection('api')==false) throw new TeException('Permiso denegado',403,'PERPermisos');
		
		$this->plan=$plan;
                $this->gui=GUIController::getInstance();
		$this->gui->addCSS('/v4/public/css/GUI/toggle_switch.css');
	}
	
	/**
	 * @return string
	 */
	public function getOutput() {
		
                
		$tpl=new GBLTemplate('USUHerramientasConfiguracionOU.tpl', 'v4/private/class/USU/TPL/'.$this->gui->getTPLVersion(), 'v4/private/data/precompilados/usu/'.$this->gui->getTPLVersion());
		
		$objPlan = USUPlanMDL::getInstance($this->plan);
		
		if (PERPermisos::checkSection('herramientas')){
			$this->gui->addJS('/v4/public/js/USU/herramientas_configuracion.js');

			$objContactos = new CONTContactos($this->plan);
			$value_aux = $objContactos->getCampos();
	
			UTLIni::addIniFile('v4/private/conf/cont_campos.ini', 'CAMPOS');
			$categorias=UTLIni::getConfig('CATEGORIAS', 'CAMPOS');

			$str_base= '';
			$str_aux = '';
			foreach($categorias as $cat=>$txtcampos){

			       if ($cat=='auxiliares'){
					$campos=explode(',',$txtcampos);
					foreach($campos as $clave){
						$alias_nuestro=$clave;
						if (substr($clave,0,5)=='dato_'){
							$pos=substr($clave,5);
							$alias_nuestro=LANGBase::__('Campo Auxiliar %%',array($pos));
						}                                       

						$tpl->setVarBlock('BLOQUE_AUXILIARES','CAMPO_ALIAS', $alias_nuestro);
						$tpl->setVarBlock('BLOQUE_AUXILIARES','CAMPO', $clave);
						if ($value_aux[$clave]==$alias_nuestro) $value_aux[$clave]='';
						$tpl->setVarBlock('BLOQUE_AUXILIARES','VALOR', $value_aux[$clave]);
						$str_aux.=$tpl->parseBlock("BLOQUE_AUXILIARES",true);
				       }
				}elseif($cat!='metadatos'){
					$campos=explode(',',$txtcampos);
					$clave_propia ="";
					
					foreach($campos as $clave){
						if(!array_key_exists($clave, $value_aux)){continue;}
						$alias_nuestro=$clave;
						if ($clave == 'dato_99'){//clave_propia
							
							$alias_nuestro= LANGBase::__('Clave Propia');
							$tpl->setVarBlock('BLOQUE_CLAVE_PROPIA','CAMPO_CLAVE_PROPIA', LANGBase::__('Clave Propia'));
							if ($value_aux[$clave]==$alias_nuestro) $value_aux[$clave]='';
							$tpl->setVarBlock('BLOQUE_CLAVE_PROPIA','VALOR_CLAVE_PROPIA', $value_aux[$clave]);
							$tpl->setVarBlock('BLOQUE_CLAVE_PROPIA','VALOR_PERSONALIZACION_CLAVE_PROPIA', $clave);
							if($objPlan->isVisibleClavePropia()){
								$tpl->setVarBlock('BLOQUE_CLAVE_PROPIA','CLAVE_PROPIA_CHECKED','ui-flipswitch-active');
								$tpl->setVarBlock('BLOQUE_CLAVE_PROPIA','CLAVE_PROPIA_VALUE_CHECKED','checked="checked"');
							}
							$tpl->setVar("BLOQUE_CLAVE_PROPIA", $tpl->parseBlock("BLOQUE_CLAVE_PROPIA",true));
							
						}else{
							$label=$value_aux[$clave];
							if ($cat=='empresa'){
								$label=str_replace('Empresa - ','',$label);
								$label=LANGBase::__('Empresa').' - '.LANGBase::__($label);
							}
							$tpl->setVarBlock('BLOQUE_GENERAL','CAMPO', $clave);
							$tpl->setVarBlock('BLOQUE_GENERAL','VALOR', $label);
							$str_base.=$tpl->parseBlock("BLOQUE_GENERAL",true);
						}
				       }
				       $str_base.=$clave_propia;
				}
			}
			
			$tpl->setVar('BLOQUE_GENERAL',$str_base);
			$tpl->setVar('BLOQUE_AUXILIARES',$str_aux);			
			$tpl->setVar('URL_BAJA',$objPlan->getURLBaja());
			$tpl->setVar('EMAIL_BAJA',(!empty($objPlan->getParametro('notificacion_baja_email'))) ? $objPlan->getParametro('notificacion_baja_email') : '' );
			$tpl->setVar('NUM_REBOTES',(!empty($objPlan->getParametro('baja_n_rebotes'))) ? $objPlan->getParametro('baja_n_rebotes') : '0' );

			$porcentaje_contactos=$objPlan->getParametro('porcentaje_aviso_contactos');
			$porcentaje_envios=$objPlan->getParametro('porcentaje_aviso_envios');
			if (is_null($porcentaje_contactos)) $porcentaje_contactos=80;
			if (is_null($porcentaje_envios)) $porcentaje_envios=80;

			$tpl->setVar('PORCENTAJE_CONTACTOS', $porcentaje_contactos);
			$tpl->setVar('PORCENTAJE_ENVIOS', $porcentaje_envios);
			
			$idioma_avisos=$objPlan->getIdiomaPreferente();
			
			$ddlIdiomas='<select name="idioma_preferente">';
			foreach(LANGBase::getInstance()->getIdiomasDisponibles() as $idioma=>$texto){
				$ddlIdiomas.='<option value="'.$idioma.'" '.(($idioma_avisos==$idioma) ? 'selected="selected"': '').'>'.$texto.'</option>';
			}
			$ddlIdiomas.='</select>';
			$tpl->setVar('DDL_IDIOMAS_AVISOS', $ddlIdiomas);
			
			$tpl->setVar('BLOQUE_HERRAMIENTAS',$tpl->parseBlock('BLOQUE_HERRAMIENTAS'));
		}
		
		$tpl->setVar('HOST_SMTPAPI',$objPlan->getHostApi());
		$tpl->setVar('HOST_POSTSOAPAPI',$objPlan->getRaizSecure());
		$tpl->setVar('PLAN',$this->plan);
		
		$now=  UTLDateTime::now();
		$mespasado = UTLDateTime::now();
		$mespasado->sub(new DateInterval('P1M'));
		$tpl->setVar('MES_ACTUAL', $now->getYear().'-'.$now->getMonth());
		$tpl->setVar('MES_ANTERIOR', $mespasado->getYear().'-'.$mespasado->getMonth());
		
                $mbcaObj = MBCAConf::getInstance();
                $comercial_nombre = $mbcaObj->getComercialNombre();
                $tpl->setVar('COMERCIAL_NOMBRE',$comercial_nombre);
                if(!empty($mbcaObj->getURLAPIRESTDOC())){
                     $tpl->setVarBlock('BLOQUE_API_REST_DOC','COMERCIAL_NOMBRE',$comercial_nombre);
                    $tpl->setVarBlock('BLOQUE_API_REST_DOC', 'URL_DOC_API_REST',$mbcaObj->getURLAPIRESTDOC());
                    $tpl->setVar('BLOQUE_API_REST_DOC',$tpl->parseBlock('BLOQUE_API_REST_DOC'));
                }
                if(!empty($mbcaObj->getURLAPISMTPDOC())){
                        $tpl->setVarBlock('BTN_API','URL', $mbcaObj->getURLAPISMTPDOC());
                        $tpl->setVarBlock('BTN_API','COMERCIAL_NOMBRE',   $comercial_nombre.'/SMTP-API');
                        $tpl->setVarBlock('BTN_API','TEXTO', LANGBase::__('Documentación SMTP-API'));
                        $btn_doc = $tpl->parseBlock('BTN_API');
                        $tpl->setVarBlock('BLOQUE_API_SMTP_DOC', 'BTN_DOC',$btn_doc);
                        if(!empty($mbcaObj->getURLAPISMTPEj())){
                                $tpl->setVarBlock('BTN_API','URL', $mbcaObj->getURLAPISMTPEj());
                                $tpl->setVarBlock('BTN_API','COMERCIAL_NOMBRE',   $comercial_nombre.'/SMTP-API');
                                $tpl->setVarBlock('BTN_API','TEXTO', LANGBase::__('SMTP-API en GitHub'));
                                $btn_eje = $tpl->parseBlock('BTN_API');
                                $tpl->setVarBlock('BLOQUE_API_SMTP_DOC', 'BTN_EJE',$btn_eje);
                        }
                    
                        $tpl->setVar('BLOQUE_API_SMTP_DOC',$tpl->parseBlock('BLOQUE_API_SMTP_DOC'));
                }
                if(!empty($mbcaObj->getURLAPIPOSTDOC())){
                        $tpl->setVarBlock('BTN_API','URL', $mbcaObj->getURLAPIPOSTDOC());
                        $tpl->setVarBlock('BTN_API','COMERCIAL_NOMBRE',   $comercial_nombre.'/POST-API');
                        $tpl->setVarBlock('BTN_API','TEXTO', LANGBase::__('Documentación POST-API'));
                        $btn_doc = $tpl->parseBlock('BTN_API');
                        $tpl->setVarBlock('BLOQUE_API_POST_DOC', 'BTN_DOC',$btn_doc);
                        if(!empty($mbcaObj->getURLAPIPOSTEj())){
                                $tpl->setVarBlock('BTN_API','URL', $mbcaObj->getURLAPIPOSTEj());
                                $tpl->setVarBlock('BTN_API','COMERCIAL_NOMBRE',   $comercial_nombre.'/POST-API');
                                $tpl->setVarBlock('BTN_API','TEXTO', LANGBase::__('POST-API en GitHub'));
                                $btn_eje = $tpl->parseBlock('BTN_API');
                                $tpl->setVarBlock('BLOQUE_API_POST_DOC', 'BTN_EJE',$btn_eje);
                        }

                    $tpl->setVar('BLOQUE_API_POST_DOC',$tpl->parseBlock('BLOQUE_API_POST_DOC'));
                }
                if($mbcaObj->isTeenvio()){
                        $tpl->setVar('BLOQUE_TEENVIO',$tpl->parseBlock('BLOQUE_TEENVIO'));
                }
                
                return $tpl->parse();
	}
        
        
        /**
 	 * @param array $dataGET
 	 * @param array $dataPOST
 	 * @return string
 	 */
 	public function runAjax($dataGET, $dataPOST){
 		$data=array('ok'=>false);
 	
 		switch($dataGET['modulo']){
			
                        case 'HERRAMIENTAS_AUXILIARES':
                                try{
                                      unset($dataPOST['modulo']);
                                      $objContactos = new CONTContactos($this->plan);
                                      $objContactos->setCampos($dataPOST);
                                      $data['ok'] = true;
                                }catch(TeException $e){
                                        $data['ok'] = false;
                                        $data['error']='Error al intentar guardar los alias para los campos auxiliares';
                                }

                                break;
			 case 'HERRAMIENTAS_GENERALES':
                                try{
					unset($dataPOST['modulo']);
					$objPlan=USUPlanMDL::getInstance($this->plan);
					$objPlan->setVisibleClavePropia($dataPOST['visible_clave_propia']);
					$objPlan->saveToDB();
				}catch(TeException $e){
                                        $data['ok'] = false;
                                        $data['error']='Error al intentar guardar la visibilidad';
                                }
				try{
					unset($dataPOST['visible_clave_propia']);
					$objContactos = new CONTContactos($this->plan);
					$objContactos->setCampos($dataPOST);
					$data['ok']=true;
                                }catch(TeException $e){
                                        $data['ok'] = false;
                                        $data['error']=$e;//'Error al intentar guardar los datos de los campos generales';
                                }

                                break;
			case 'HERRAMIENTAS_URLBAJA':
				try{
					$objPlan=USUPlanMDL::getInstance($this->plan);
					$objPlan->setURLBaja($dataPOST['url']);
					if (trim($dataPOST['email'])!=''){
						$objPlan->setParametro('notificacion_baja_email', trim($dataPOST['email']));
					}
					if (isset($dataPOST['num_rebotes'])){
						$objPlan->setParametro('baja_n_rebotes',(int) $dataPOST['num_rebotes']);
					}
					$objPlan->saveToDB();
					$data['ok']=true;
				} catch (TeException $ex) {
					$data['ok'] = false;
					$data['error']='Error al intentar guardar la url para bajas';
					$data['detalle']=$ex->getMessage();
				}
				break;
			case 'HERRAMIENTAS_AVISOS':
				
				try{
					$objPlan=USUPlanMDL::getInstance($this->plan);
					$objPlan->setParametro('porcentaje_aviso_contactos', $dataPOST['porcentaje_aviso_contactos']);
					$objPlan->setParametro('porcentaje_aviso_envios', $dataPOST['porcentaje_aviso_envios']);
					$objPlan->setIdiomaPreferente($dataPOST['idioma_preferente']);
										
					$data['ok']=true;
				} catch (TeException $ex) {
					$data['ok'] = false;
					$data['error']='Error al intentar guardar los Avisos';
					$data['detalle']=$ex->getMessage();
				}
				break;
                }
		
 		return json_encode($data);
 	}
 	
}

?>