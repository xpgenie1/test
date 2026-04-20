<?php
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/UTL/UTLUtilidades.php';
require_once 'v4/private/class/BACK/MON/BACKMONBase.php';
require_once 'v4/private/class/LANG/LANGBase.php';
require_once 'v4/private/class/MBCA/MBCAConf.php';

/**
 * Clase de utilidades HTTP
 * @author Victor J Chamorro - victor@ipdea.com
 * @copyright Ipdea Land, S.L.
 *
 * LGPL v3 - GNU LESSER GENERAL PUBLIC LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU LESSER General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU LESSER General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class UTLHttp{
	
	static $cookies=array();
	
	/**	
	 * @var boolean
	 */
	static $enableCookies=true;

	static function enableCookies(){
		self::$enableCookies=true;
	}
	
	static function disableCookies(){
		self::$enableCookies=false;
	}
	
	/**
	 * Clase de métodos estáticos, no es posible su instanciación
	 */
	private function __construct(){
	}

	/**
	 * Envia el código de estado 404 Not Found
	 * Por defecto además envía la página de error 404 personalizada
	 * Termina la ejecución con un die()
	 *
	 * @param boolean $pagina_error
         * @param string $txt
	 */
	public static function sendNotFound($pagina_error=true,$txt=""){
		header('HTTP/1.0 404 Not Found');
		if ($pagina_error){
		    $html='';
		    if (substr($_SERVER['HTTP_HOST'], strlen('.teenvio.com')*-1)=='.teenvio.com'){
			$html=file_get_contents ('v4/public/utl/error_404.html',true);
		    }else{
			$html=file_get_contents ('v4/public/utl/error_404_generico.html',true);
		    }
                    $html=str_replace('<!-- DETALLES -->',$txt,$html);

                    self::sendCharsetUTF8();
                    die($html);
		}
		die();
	}

	/**
	 * Redireccion HTTP 301 Moved Permanently
	 * a la url indicada. Interrumpe la ejecución del script.
	 * @param string $url
	 */
	public static function sendRedirect301($url){
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.trim($url));
		die();
	}

	/**
	 * Redireccion HTTP 302 Moved Temporarily
	 * a la url indicada. Interrumpe la ejecución del script.
	 * @param string $url
	 */
	public static function sendRedirect302($url){
		header('HTTP/1.1 302 Found');
		header('Location: '.trim($url));
		die();
	}

	/**
	 * Envía la cabecera para UTF8.
	 * Content-Type: text/html; charset=UTF-8
	 * @param string $content_type 'text/html'
	 */
	public static function sendCharsetUTF8($content_type='text/html'){
		if (!headers_sent()) header('Content-Type: '.$content_type.'; charset=UTF-8');
	}
	
	/**
	 * Envía la cabecera para ISO-8859-15.
	 * Content-Type: text/html; charset=ISO-8859-15
	 * @param string $content_type 'text/html'
	 */
	public static function sendCharsetISO($content_type='text/html'){
		if (!headers_sent()) header('Content-Type: '.$content_type.'; charset=ISO-8859-15');
	}
	
	/**
	 * Envía la cabecera para XML utf8
	 * Content-Type: text/xml; charset=UTF-8	 
	 */
	public static function sendContentTypeXML(){
		if (!headers_sent()) header('Content-Type: text/xml; charset=UTF-8');
	}
	
	/**
	 * Envía la cabecera de idioma "Content-Language"
	 * @param string $lang
	 */
	public static function sendContentLanguage($lang){
		if (!headers_sent()) header('Content-Language: '.$lang);
	}
	
	/**
	 * Envía la cabecera para descargar
	 * Content-Type: application/download
	 * @param string $name Nombre del fichero a descargar UTF-8
	 * @param string $contentType Opcional, por defecto application/download
	 * @see https://tools.ietf.org/html/rfc6266
	 */
	public static function sendContentTypeDownload($name=null,$contentType='application/download'){
		header("Content-Type: ".$contentType);
		if (empty(LANGBase::$instance)){
			//Desactivo las cookies y seteo el locale para la conversion UTF-8 -> ASCII 
			$cookies=self::$enableCookies;
			if ($cookies==true) self::disableCookies ();
			LANGBase::getInstance();
			if ($cookies==true) self::enableCookies ();
		}
		if (!empty($name)) header('Content-Disposition: attachment; filename="'.iconv('UTF-8','ASCII//TRANSLIT',$name).'"; filename*=UTF-8\'\''.rawurlencode($name));
	}
	
	/**
	 * Envía la cabecera para mostrar
	 * Content-Type: application/download
	 * @param string $name Nombre del fichero UTF-8
	 * @param string $contentType Opcional, por defecto application/download
	 * @see https://tools.ietf.org/html/rfc6266
	 */
	public static function sendContentTypeInline($name=null,$contentType='application/download'){
		header("Content-Type: ".$contentType);
		if (empty(LANGBase::$instance)){
			//Desactivo las cookies y seteo el locale para la conversion UTF-8 -> ASCII 
			$cookies=self::$enableCookies;
			if ($cookies==true) self::disableCookies ();
			LANGBase::getInstance();
			if ($cookies==true) self::enableCookies ();
		}
		if (!empty($name)) header('Content-Disposition: inline; filename="'.iconv('UTF-8','ASCII//TRANSLIT',$name).'"; filename*=UTF-8\'\''.rawurlencode($name));
	}
	
	/**
	 * Envía cabecera de prohibido (HTTP 403) y muere
	 * Si se pasa text, manda ese string antes de morir.
	 * @param $txt
	 */
	public static function sendForbidden($txt=""){
		header('HTTP/1.1 403 Forbidden');
		die($txt);
	}
        
	/**
	 * Envía cabecera de error interno de servidor (HTTP 500) y muere
	 * Si se pasa text, manda ese string antes de morir.
	 * @param $txt
	 */
	public static function sendErrorInternoDeServidor($txt="",$e=null){
		header('HTTP/1.1 500 Internal server error');
		
		if (substr($_SERVER['HTTP_HOST'], strlen('.teenvio.com')*-1)!='.teenvio.com'){
			die('<html><h1>Error 500 Internal server error</h1><!-- '.$txt.' -->');
		}
		
		if ($txt==''){
			$plantilla="v4/public/utl/error_500.html";
			try{
				$mbca = MBCAConf::getInstance();
				if ($mbca->isTeenvio()!==true){
					$plantilla="v4/public/utl/error_500_generico.html";
				}
			} catch (TeException $ex) { }
			
			$txt=file_get_contents ($plantilla,true);
		}
		
		if ($e instanceof Exception){
			
			$detalles=$e->getMessage()."\n".$e->getTraceAsString();
			
			if (BDBase::$staticBD1 instanceof BDB\BD){
				$detalles= str_replace(BDBase::$staticBD1->DB_PASSWORD,'********',$detalles);
			}
			
			$txt=str_replace('<!-- DETALLES_QP -->', urlencode("Hola,\n\nmi usuario es: _______._______ y el error es:\n\n".$detalles),$txt);
			
			$detalles=str_replace("\n", "<br>", $detalles);
			$txt=str_replace('<!-- DETALLES -->',$detalles,$txt);

		}
		self::sendCharsetUTF8();
		die($txt);
	}
	
	/**
	 * Envía cabecera de solicitud errónea (HTTP 400) y muere
	 * Si se pasa text, manda ese string antes de morir.
	 * @param $txt
	 */
	public static function sendBadRequest($txt=""){
		header('HTTP/1.1 400 Bad request');
		die($txt);
	}
	
	/**
	 * Envía las cabeceras Access-Control-Allow-* para peticiones CORS (llamadas js ajax entre dominios distintos)
	 * @param string $origen
	 * @param array $metodos
	 */
	public static function sendAccessControlAllow($origen='*',$metodos=array('POST','GET')){
		header('Access-Control-Allow-Origin: '.$origen);
		header('Access-Control-Allow-Methods: '.implode(', ',$metodos));
		if ($origen!='*') header('Access-Control-Allow-Credentials: true');
	}
	
	/**
	 * Devuelve true en el caso de que se detecte que el USER_AGENT contiene MSIE (Microsoft Internet Explorer)
	 * @return boolean
	 */
	public static function isIE(){
		if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;
		return (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false || strpos($_SERVER['HTTP_USER_AGENT'],'Trident')!==false);
	}
        
        /**
	 * Devuelve true en el caso de que se detecte que el USER_AGENT se reconozca como Safari
	 * @return boolean
	 */
	public static function isSafari(){
		if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;
		return (strpos($_SERVER['HTTP_USER_AGENT'],'Safari')!==false);
	}
	
	/**
	 * Inicia una sesión a menos que fuese iniciada con anterioridad
	 */
	public static function sessionStart(){
		if (session_id()=="" && !headers_sent()){
			return @session_start();
		}
	}

	/**
	 * Devuelve el user agent del navegador o cadena en blanco en caso de no llegar
	 * @return string
	 */
	public static function getUserAgent(){
		if (isset($_SERVER['HTTP_USER_AGENT'])){
			return $_SERVER['HTTP_USER_AGENT'];
		}else{
			return "";
		}
	}
	
	/**
	 * Setea una cookie
	 * @param string $cookie_name
	 * @param string $var_name
	 * @param string $value
	 * @param int $seconds Tiempo en segundos, 0 significa la sesión actual
	 * @param string $domain Dominio para la cookie, por defecto *.teenvio.com
	 * @return boolean
	 */
	public static function setCookieVar($cookie_name,$var_name,$value,$seconds=0,$domain='.teenvio.com'){
		
		if (self::$enableCookies==false) return false;
		
		if (!isset(self::$cookies[$cookie_name][$var_name][$value][$seconds])){
			$time=0;
			if($seconds>0){
				$time=time()+$seconds;
			}
			if(setcookie($cookie_name.'['.$var_name.']',$value,$time, '/',$domain, self::isHTTPS())){
				if (!isset(self::$cookies[$cookie_name])) self::$cookies[$cookie_name]=array();
				if (!isset(self::$cookies[$cookie_name][$var_name])) self::$cookies[$cookie_name][$var_name]=array();
				if (!isset(self::$cookies[$cookie_name][$var_name][$value])) self::$cookies[$cookie_name][$var_name][$value]=array();
				if (!isset(self::$cookies[$cookie_name][$var_name][$value][$seconds])) self::$cookies[$cookie_name][$var_name][$value][$seconds]=1;
				
				return true;
			}else{
				return false;
			}
		}
	}
	
	/**
	 * Borra la cookie con el nombre pasado
	 * @param string $cookie_name
	 * @param string $cookie_domain
	 * @param bool $secure
	 * @param bool $httponly
	 */
	public static function deleteCookie($cookie_name,$cookie_domain=".teenvio.com",$secure=null,$httponly=false){
		if ($secure === null) $secure=self::isHTTPS();
		return setcookie($cookie_name,'',0,'/',$cookie_domain,$secure,$httponly);
	}
	
	/**
	 * Devuelve el valor de una variable de una cookie
	 * @param string $cookie_name
	 * @param string $var_name
	 * @return string
	 */
	public static function getCookieVar($cookie_name,$var_name){
		if ($var_name===''){
			if (isset($_COOKIE[$cookie_name])){
				return $_COOKIE[$cookie_name];
			}else{
				return null;
			}
		}elseif (isset($_COOKIE[$cookie_name][$var_name])){
			return $_COOKIE[$cookie_name][$var_name];
		}else{
			return null;
		}
	}
	
	/**
         * Esta función chequea si existe o no un Thunbnails y te devuelve el existente o genera uno nuevo.
         * 
	 * @param string $url URL de la web a capturar
	 * @param string $md5_to_check Si se pasa se verifica el md5
	 * @param boolean $cache Usa el cache por url
	 * @param boolean $delete Elimina la imagen temporal
	 * @return string Imagen png en bruto
	 * @throws TeException
	 */
	public static function getThumbnails($url,$md5_to_check='',$cache=true,$delete=false){
		
		$md5=md5($url.'teThumbnails');
		
		if ($md5_to_check != '' && $md5!=$md5_to_check){
			throw new TeException('Fallo al chequear el md5 para obtener la miniatura',__LINE__,__CLASS__);
		}
		//cogemos la ruta donde se van a almacenar los thumbnails
		$path=  UTLUtilidades::getFullPath('v4/private/data/html_thumbnails');
		$full_path=$path.'/'.substr($md5,0,1);
		$full_name=$full_path.'/'.$md5.'.png';
		
		// Si existe previamente y tiene menos de 5 minutos, usamos el existente
		if ($cache && is_file($full_name)){// && (filemtime($full_name)+(60*5) >  time())){
			return file_get_contents($full_name); 
		}
		
		if (UTLHttp::isUrlValid($url)===false){
			throw new TeException('Fallo al intentar abrir el url para obtener la miniatura: '.$url,__LINE__,__CLASS__);
		}
		
		if (!is_dir($full_path)){ mkdir($full_path,0777,true);}
		
		if (is_dir($full_path)){
			/**
			 * sudo apt-get install xvfb xauth cutycapt
			 */
			$command='xvfb-run --server-args="-screen 0, 1280x1200x24" cutycapt --url="'.$url.'" --out='.$full_name;
		
			exec($command);
			if (is_file($full_name)){
				if ($delete===true){
					$data=file_get_contents($full_name);
					@unlink($full_name);
					return $data;
				}else{
					return file_get_contents($full_name);
				}
			}else{
				throw new TeException('Fallo al generar la miniatura: '.$command,__LINE__,__CLASS__);
			}
		}else{
			throw new TeException('Fallo al generar las carpetas para '.__METHOD__,__LINE__,__CLASS__);
		}
		
	}
	
	/**
	 * Chequea si la url devuelve un recurso válido (Status 200), sigue las redirecciones de ser necesario
	 * Si el protocolo es file:// comprueba que sea un fichero válido
	 * @param string $url
	 * @return boolean
	 */
	public static function isUrlValid($url) {
		
		if (substr($url,0,7)=='file://'){
			return is_file(substr($url,7)) && is_readable(substr($url,7));
		}else{
		
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects
			curl_setopt($ch, CURLOPT_AUTOREFERER, 0); // set referer on redirect
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 8); //timeout in seconds
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:4.0) Teenvio.com (boot)",
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
				"Accept-Encoding: gzip, deflate",
				"Connection: keep-alive",
				"Pragma: no-cache",
				"Cache-Control: no-cache"
			));
			curl_exec($ch);
			$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			return ($status==200);
		}
	}
	
	/**
	 * Devuelve la URL final (Sigue las redirecciones HTTP);
	 * @param type $url
	 * @return string
	 */
	public static function followURL($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects
		curl_setopt($ch, CURLOPT_AUTOREFERER, 0); // set referer on redirect
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 8); //timeout in seconds
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:4.0) Teenvio.com (boot)",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			"Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
			"Accept-Encoding: gzip, deflate",
			"Connection: keep-alive",
			"Pragma: no-cache",
			"Cache-Control: no-cache"
		));
		curl_exec($ch);
		$target = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		if ($target) return $target;
		return '';
	}
	
	/**
	 * Devuelve la informacion obtenida de GoogleSafeBrowsing.
	 * Si devuelve información (matches) es que hay alertas de seguridad en dicha url
	 * @param string $url
	 * @return array
	 * @throws TeException
	 */
	private static function lookup_GoogleSafeBrowsing_v4($url){
		$data = '{
		  "client": {
		    "clientId": "teenviosafebrowsinglookupapi",
		    "clientVersion": "1.0"
		  },
		  "threatInfo": {
		    "threatTypes":      ["MALWARE", "SOCIAL_ENGINEERING","UNWANTED_SOFTWARE","POTENTIALLY_HARMFUL_APPLICATION"],
		    "platformTypes":    ["ALL_PLATFORMS"],
		    "threatEntryTypes": ["URL"],
		    "threatEntries": [
		      {"url": "'.$url.'"}
		    ]
		  }
		}';

		/**
		 * Google Cloud Plataform - APIs y servicios
		 * usuario <victor@ipdea.com>
		 * https://console.cloud.google.com/apis/dashboard?project=teenviosafebrowsinglookupapi&duration=PT1H
		 * https://developers.google.com/safe-browsing/v4/lookup-api
		 */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://safebrowsing.googleapis.com/v4/threatMatches:find?key=AIzaSyCcviyV4ynIvNSSx3gxg5LIUp5c8LdbFRA");
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", 'Content-Length: ' . strlen($data)));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$data=curl_exec($ch);
		if ($data===false){
			throw new TeException('Fallo al preguntar a safebrowsing.googleapis.com',__LINE__,__CLASS__);
		}
		$response = json_decode($data, true);
		curl_close ($ch);
		return $response;
	}

	/**
	 * Deuelve true si Safe Browsing de google no lo considera un peligro, false si se considera URL insegura
	 * Requiere conexión con Memcached
	 * @param string $url
	 * @return boolean
	 */
	public static function IsSafeUrl($url,$plan=''){
		
		$url64=base64_encode($url);
		
		/*if (!class_exists('Memcache') ){
			throw new \TeException('Fallo al conectarse al servidor Memcached, extensión Memcache de php no disponible',__LINE__,__CLASS__);
		}
		
		require_once 'v4/private/class/BDB/BDBMemcached.php';
		$memcache=BDB\Memcached::getInstance();
		*/
		if (is_file('/data/blacklists/urls/'.$url64)){
			if (\UTLUtilidades::isDebug()) \UTLUtilidades::echoDebug("\n\t".'Ccomprobación de url segura: UNSAFE blacklist file: '.$plan.' - '.$url, __CLASS__);
			return false;
		}
		/*
		if ($memcache->get($url64)===$url){
			if (\UTLUtilidades::isDebug()) \UTLUtilidades::echoDebug("\n\t".'Ccomprobación de url segura: SAFE whitelist memcached: '.$url, __CLASS__);
			return true;
		}
		
		$response=self::lookup_GoogleSafeBrowsing_v4(self::followURL($url));
		
		if (isset($response['matches'][0]['threatType'])){
			@file_put_contents('/data/blacklists/urls/'.$url64,$url);
			BACKMONBase::sendToXMPP('URL Bloqueada: '.$url.' Plan: '.$plan);
			if (\UTLUtilidades::isDebug()) \UTLUtilidades::echoDebug("\n\t".'Ccomprobación de url segura: UNSAFE GoogleSafeBrowsingApi: '.$url, __CLASS__);
			return false;
		}else{
			//Guardamos la url como ok durante al menos 300 segundos (5 minutos)
			$memcache->set($url64,$url,false,300);
			if (\UTLUtilidades::isDebug()) \UTLUtilidades::echoDebug("\n\t".'Ccomprobación de url segura: SAFE GoogleSafeBrowsingApi: '.$url, __CLASS__);
			return true;
		}
		*/
	}
	
	/**
         * Raiz actual con el protocolo incluido y sin barra al final (por ejemplo: https://app.teenvio.com)
         * @return String
        */
        public static function getBaseUrl(){
	    if (!UTLUtilidades::isCLI())	{
		return ( self::isHTTPS() ? 'https://' : 'http://' ).$_SERVER['HTTP_HOST'];
	    }else{
	        return '';
	    }
        }
	
	/**
	 * La petición se detecta como https
	 * @return boolean
	 */
	public static function isHTTPS(){
		return  ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on' )  ||
			( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'])=='https' );
	}
	
	/**
	 * Dirección ip Remota
	 * @return string
	 */
	public static function getRemoteAddr(){
		
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			return $_SERVER['HTTP_CLIENT_IP'];
		}else if (!empty($_SERVER['HTTP_X_REAL_IP'])){
			return $_SERVER['HTTP_X_REAL_IP'];
		}else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //if from a proxy
			$ips=explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return array_pop($ips);
		}else if (!empty($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		}else{
			return '';
		}
	}
	
}
?>
