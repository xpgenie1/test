<?php
require_once 'v4/private/class/UTL/UTLIni.php';
require_once 'v4/private/class/UTL/UTLTeException.php';

define('CONFURLS',$_SERVER['DOCUMENT_ROOT'].'/v4/private/conf/urlshort_redirecciones.ini');
define('URL_SHARE','http://www.teenvio.com/v3/estadisticas/lee.php');
/**
 * Clase de utilidades para las urls cortas, contiene los metodos de get y save de urls
 * @author VJChamorro
 * @package UTL
 */
class UTLUrlShort{

	/**
	 * Devuelve la url correspondiente al código pasado
	 * @param string $cod
	 * @return string
	 */
	public static function getURL($cod){		
		UTLIni::addIniFile(CONFURLS,"URLS");
		return UTLIni::getConfig($cod,'URLS');
	}
	
	/**
	 * Devuelve la url correspondiente al código pasado (para shares sociales)
	 * @param string $cod
	 * @return string
	 */
	public static function getURLShare($cod){
		
		$parametros=explode("&",base64_decode(substr($cod,1)));
		if (is_array($parametros) && count($parametros)>3){
			$url = URL_SHARE;
			$url.= "?id_envio="		.$parametros[1];
			$url.= "&id_c="			.$parametros[0];
			$url.= "&id_contacto="	.$parametros[2];
			$url.= "&key="			.$parametros[3];
			return $url;
		}
		return false;
	}
	
	/**
	 * Guarda la url con su generación corta en el fichero de configuración,
	 * devuelve la generación corta
	 * @param string $url
	 * @throws TeException
	 * @return string
	 */
	public static function saveURL($url){
		
		$rs=@fopen(CONFURLS,"a");
		if ($rs===false) throw new TeException("Error al abrir el fichero de configuración de urls cortas", 1,__CLASS__);
		
		$corta="0".substr(md5($url),28);
		$conf=UTLIni::addIniFile(CONFURLS,"URLS");
		
		while(isset($conf['URLS'][$corta])){
			$corta="0".substr(md5($url),28)."a";
		}
		
		if (false===@fwrite($rs, $corta."=".$url)){
			throw new TeException("Error al guardar en el fichero de configuración de urls cortas", 2,__CLASS__);
		}
		@fclose($rs);
		
		return $corta;
	}

}
?>