<?php
/**
 * Clase para el tratamiento de las url con el seguimiento de Google Analytics
 * 
 * @author Víctor J. Chamorro <victor@ipdea.com>
 * @package UTL
 * @version 20120521
 * 
 */

class UTLUrlAnalytics{
	
	/**
	 * Array de configuración
	 * @var array
	 */
	private $conf;
	
	/**
	 * Url a tratar
	 * @var string
	 */
	private $url;
	
	/**
	 * Objeto con el envio en curso
	 * @var ENVMDLEnvio
	 */
	private $objMDLEnvio;
	
	/**
	 * Id del enlace que será tratado
	 * @var int
	 */
	private $idEnlace;
	
	
	/**
	 * Crea un Objeto UTLUrlAnalytics para el trabajo con los parámetros de las campañas de 
	 * Google Analytics
	 * 
	 * @param string $url
	 * @param ENVMDLEnvio $objMDLEnvio
	 * @param int $idEnlace
	 * 
	 */
	public function __construct($url,$objMDLEnvio,$idEnlace){
		if (empty($url)) 		throw new TeException("No se ha pasado la url en el constructor", 1,__CLASS__);
		if (empty($idEnlace) && $idEnlace != "0")	throw new TeException("No se ha pasado el idEnlace en el constructor", 2,__CLASS__);
		if (!$objMDLEnvio instanceof ENVMDLEnvio) 
								throw  new TeException("No se ha pasado el objeto del envio", 3,__CLASS__);
		
		$this->url = $url;
		$this->idEnlace=$idEnlace;
		$this->objMDLEnvio =& $objMDLEnvio;
		
	}
	
	/**
	 * Carga la configuración procedente del fichero ini
	 */
	private function getConf(){
		UTLIni::addIniFile($_SERVER["DOCUMENT_ROOT"]."/v4/private/conf/analytics_params.ini","ANALYTICS");
		$this->conf=UTLIni::getConfig("ANALYTICS","ANALYTICS");
	}
	
	/**
	 * Devuelve true si encuentra el parámetro utm_source enl a url, false en caso contrario
	 * @return boolean
	 */
	private function checkUtm_source(){
		return (strpos($this->url,"utm_source=")!==false);
	}
	
	/**
	 * Devuelve un string con la url cargada y parseada con los parámetros
	 * @return string
	 */
	public function addParams(){
		//No debe existir un parámetro utm_source
		if ($this->checkUtm_source()===false){
			//Sacamos los parámetros de la configuracion del ini
			$this->getConf();
			
			if (substr($this->url,-1)=='"') $this->url=substr($this->url,0,-1);
			
			//Detecta si hay un ancla en la url para pasarlo al final
			$ancla='';
			if (strpos($this->url,'#')!==false){
				$partes=explode('#',$this->url);
				$this->url=$partes[0];
				$ancla='#'.$partes[1];
				if (count($partes)>2){
					for($i=2;$i<count($partes);$i++){
						$ancla.='#'.$partes[$i];
					}
				}
			}
			
			if (strpos($this->url,'?')===false){
				$this->url.='?';
			}else{
				$this->url.='&';
			}
		
			//Barremos ahora los parámetros a insertar, parseándolos
			foreach($this->conf as $param=>$value){
				if (trim($value)!="") $this->url.=$param."=".$this->parseaVariables($value)."&";
			}
			
			//le quitamos el último carácter, ya sea & o ?
			$this->url=substr($this->url,0,-1).$ancla.'"';
			
		}
		
		return $this->url;
	}
	
	/**
	 * Parsea las variables del envio/link
	 * @param string $var
	 * @return string
	 */
	private function parseaVariables($var){
		$var=str_replace("[id_envio]", $this->objMDLEnvio->getIdEnvio(), $var);
		$var=str_replace("[id_enlace]",$this->idEnlace,$var);
		$var=str_replace("[asunto]",urlencode($this->objMDLEnvio->getAnalyticsUtm_campaign()),$var);
		return $var;
	}
	
	/**
	 * Retorna la url en el estado actual
	 * @return string
	 */
	public function getUrl(){
		return $this->url;
	}
	
	
} 

?>
