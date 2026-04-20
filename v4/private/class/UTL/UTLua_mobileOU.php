<?php
require_once 'v4/private/class/GBL/GBLTemplate.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/GBL/GBLOutput.php';
require_once 'v4/private/class/UTL/UTLSendEmail.php';
require_once 'v4/private/class/UTL/UTLLog.php';
/**
 * 
 * @author Chema
 * @package UTL
 *
 */
class UTLua_mobileOU implements GBLOutput, AJAXRequest{
	
	/**
	 * 
	 * @var GBLTemplate
	 */
	private $tpl= null;
	
	/**
	 * 
	 * @var string
	 */
	private $email="";
	
	/**
	 * 
	 * @var string
	 */
	private $descripcion="";
	
	
	/**
	 * 
	 * @param $email Email donde llegara el correo
	 * @param $descripcion Descripcion para poder buscar la respuesta
	 */
	public function __construct($email='',$descripcion=''){
		
		$this->email=$email;
		$this->descripcion=$descripcion;
		
		
	}
	
	private function enviaEmailPixel(){
		
		$sendmail=new UTLSendEmail();
		$sendmail->setFrom("info@teenvio.com");
		$sendmail->setTo($this->email);
		$sendmail->setCuerpoHTML("\n\n<html><h1>Correo de detenci&oacute;n de dispositivos</h1><p>".$this->descripcion."</p><p>Por favor, indica que deseas mostrar las im&aacute;genes</p><img src='http://www.teenvio.com/es/wp-content/uploads/2011/10/teenvio-email-marketing.png'/><br/>Gracias.<br/><img src='http://pre.teenvio.com/v4/public/useragent/ua_pixel.php?desc=".$this->descripcion."'/></html>\n\n");
		$sendmail->setCuerpoPlano("Correo de prueba plano: ".$this->descripcion);
		$sendmail->setAsunto("Correo detectar dispositivos de teenvio");

		if ($sendmail->send()){
			return "ok";
		}else{
			return "ko";
		}
		
	}
	
	public function getPixel($descripcion){
		
			UTLLog::guardaLog('UTLua_mobileOU','USER_AGENT',$_SERVER['HTTP_USER_AGENT']." - ".$descripcion);
			$gif = @file_get_contents('v4/public/img/fondo.gif',true);
			if ($gif !== false){
				return $gif;
			}else{
				throw new TeException("Error al recuperar el pixel transparente",1,__CLASS__);
			}
		
	}
	
	/**
	 * Genera la salida HTML
	 */
	public function getOutput(){
		
		$this->tpl= new GBLTemplate("UTLua_mobileOU.tpl","v4/private/class/UTL/TPL","v4/private/data/precompilados/utl");
		GUIController::getInstance()->addJS("/v4/public/js/UTL/jquery-last.js");
		GUIController::getInstance()->addJS("/v4/public/js/UTL/UTLua_mobile.js");
		
		return $this->tpl->parse();
	}
	
	public function runAjax($dataGET,$dataPOST){
		
		$descripcion=$this->enviaEmailPixel();
		return json_encode(array('descripcion'=>$descripcion));
	}
}


?>