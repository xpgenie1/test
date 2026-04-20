<?php
require_once 'v4/private/class/GBL/GBLTemplate.php';
require_once 'v4/private/class/UTL/UTLExportar.php';
require_once 'v4/private/class/GBL/GBLOutput.php';


/**
 * 
 * @author Chema
 * @package UTL
 *
 */
class UTLExportarOU implements GBLOutput{
	
	/**
	 * 
	 * @var GBLTemplate
	 */
	private $tpl= null;
	
	/**
	 * 
	 * @var string
	 */
	private $plan="";
	
	
	
	
	/**
	 * 
	 * @param $email Email donde llegara el correo
	 * @param $descripcion Descripcion para poder buscar la respuesta
	 */
	public function __construct($plan){
		
		$this->plan=$plan;
		
	}
	
	/**
	 * Genera la salida HTML
	 */
	public function getOutput(){
		
		$this->tpl= new GBLTemplate("UTLExportarOU.tpl","v4/private/class/UTL/TPL","v4/private/data/precompilados/utl");
				
		return $this->tpl->parse();
	}
	

}
?>