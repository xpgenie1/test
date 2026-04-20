<?php
require_once 'v4/private/class/UTL/UTLIni.php';
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/UTL/UTLHttp.php';

define("INI_MODULOS_AJAX","v4/private/conf/modulos_ajax.ini");

/**
 * Clase controlador de peticiones AJAX
 * Todas las peticiones Ajax del sitio deben apuntar a este controlador
 * @author Victor J Chamorro - victor@ipdea.com
 */
class AJAXController{

	private $dataGET;
	private $dataPOST;

	function __construct(&$dataGET,&$dataPOST){
		$this->dataGET=$dataGET;
		$this->dataPOST=$dataPOST;
	}

	public function run(){
		
		if (isset($this->dataGET['modulo'])) $this->dataPOST['modulo']=$this->dataGET['modulo'];
		if (isset($this->dataPOST['modulo'])){
			$modulo=strtoupper($this->dataPOST['modulo']);
			try{
				
				if (substr($modulo,0,3)=='CRM'){
					session_name('CRMTEENVIO');
				}
				
				UTLIni::addIniFile(INI_MODULOS_AJAX,"MODULOS");
				if (isset(UTLIni::$conf['MODULOS'][$modulo])){
				
					if (!is_file($_SERVER['DOCUMENT_ROOT']."/".UTLIni::$conf['MODULOS'][$modulo]['file']))
						throw new TeException("Error al instanciar la clase del modulo $modulo. El fichero no existe",1);
						
					require_once(UTLIni::$conf['MODULOS'][$modulo]['file']);
					
					$class=UTLIni::$conf['MODULOS'][$modulo]['class'];
					if (class_exists($class,false)){
						
						//Clase para el manejo de clases nativa de PHP5							
						$obj = new ReflectionClass($class);
						$params=array();
						
						//Miramos si la clase requiere parámetros en su constructor
						if (isset(UTLIni::$conf['MODULOS'][$modulo]['params'])){
							$tmp_params=explode(",", UTLIni::$conf['MODULOS'][$modulo]['params']);
							foreach($tmp_params as $parametro){
								if (isset($this->dataGET[$parametro])){
									$params[]=$this->dataGET[$parametro];
								}elseif (isset($this->dataPOST[$parametro])){
									$params[]=$this->dataPOST[$parametro];
								}elseif($parametro=='plan'|| $parametro=='cliente'){
									require_once 'v4/private/class/GBL/GBLSession.php';
									$params[]=GBLSession::getPlan();
								}else{
									$params[]='';
								}
							}
						}
						//Creamos la nueva instancia con los parámetros necesarios
						$objController=$obj->newInstanceArgs($params);
						
						if (method_exists($objController,'runAjax')){
							$return = $objController->runAjax($this->dataGET,$this->dataPOST);
							if ($return!==false){
								UTLHttp::sendCharsetUTF8();
								echo $return;
							}
						}else{
							throw new TeException("Error al llamar al método 'runAjax' de la clase $class desde AjaxController",4,__CLASS__);
						}
					}else{
						throw new TeException("Error al instanciar la clase $class desde AjaxController",3,__CLASS__);
					}
				}else{
					UTLHttp::sendForbidden("\n\nLlamada ilegal, modulo $modulo no válido");
				}
			
			}catch(TeException $e){
				if ($e->getClassName()=='PERPermisos' && $e->getCode()==403){
					echo json_encode(array('ok'=>false,'cod_error'=>$e->getCode()));
					die();
				}
				UTLHttp::sendErrorInternoDeServidor('',$e);
				//UTLHttp::sendErrorInternoDeServidor("\n\nExcepcion en AJAXController \nCod:".$e->getCode()."\nClass:".$e->getClassName());
			}
			
			
		}else{
			UTLHttp::sendForbidden("\n\nLlamada ilegal, faltan par&aacute;metros o &eacute;stos son incorrectos");
		}
	}
	
	/**
	 * Método que debe comprobar si el usuario está autorizado a lanzar peticiones AJAX
	 * Comprueba que exista una sesión declarada para evitar peticiones no autorizadas
	 */
	private function CompruebaUsuario(){
	
	}
}

?>