<?php
require_once 'v4/private/class/UTL/UTLHttp.php';
/**
 * @package UTL
 * @author Víctor J. Chamorro - victor@ipdea.com
 *
 * Paquete Utilidades - Teenvio.com V4
 * 
 **/

class UTLUtilidades{
	
	/**
	 * Devuelve true si estamos en modo debug
	 * @return boolean 
	 */
	static function isDebug(){
		return (isset($_GET['debug']));	
	}
	
	/**
	 * Devuelve true si estamos en modo debug SQL
	 * @return boolean 
	 */
	static function isDebugSQL(){
		return (isset($_GET['debugsql']) && $_GET['debugsql']!=="0");
	}
	
	/**
	 * Devuelve el nivel de debug
	 * @return int
	 */
	static function getDebugLevel(){
		return (int) (isset($_GET['debug']) && is_numeric($_GET['debug'])) ? $_GET['debug'] : 0;
	}
	
	/**
	 * Imprime Mensajes de Debug con trazas si se indica
	 * @param string $txt
	 * @param string $class
	 */
	static function echoDebug($txt,$class=null){
		
		if (is_null($class)) $class=__CLASS__;
		if (isset($_GET['debugfilter']) && $_GET['debugfilter']!==$class) return false;
		
		if (!self::isCLI() && !headers_sent()) UTLHttp::sendCharsetUTF8();
		if (isset($_GET['trace']) && !self::isCLI()) echo "<div style='cursor:pointer'>";
		if (!self::isCLI()) echo "<pre style='background:white;text-align:left;' ";
		if (isset($_GET['trace']) && !self::isCLI()) echo "onclick='if (this.parentNode.lastChild.style.display==\"\") this.parentNode.lastChild.style.display=\"none\"; else this.parentNode.lastChild.style.display=\"\";'";
		if (!self::isCLI()) echo " >";
		if (!self::isCLI()) $txt=str_replace('<','&lt;',$txt);
		
		$txt=str_replace('&lt;strong>','<strong>',$txt);
		$txt=str_replace('&lt;/strong>','</strong>',$txt);
		
		echo UTLUtilidades::getMicroTime()." [".$class."]"." ".$txt."\n";
		if (!self::isCLI()) echo "</pre>\n";
		if (isset($_GET['trace'])){
			if (!self::isCLI()) echo "<pre style='cursor:auto;display:none;background:#ddd;text-align:left;'>";
			$trace=debug_backtrace();
			unset($trace[0]);
			foreach($trace as $n=>$dump){
				$class='';
				if (isset($dump['class'])) $class=$dump['class'].'::';
				//Preparo los parámetros
				foreach($dump['args'] as &$arg){
					if (gettype($arg)=="string"){
						if (strlen($arg)>150) $arg=substr($arg,0,150).'...';
						if (substr($arg,0,1)!=='"') $arg='"'.$arg.'"';
					}elseif(gettype($arg)=="object" && get_class($arg)!==false ){
						$arg=get_class($arg)." Object";
					}else{
						$arg=gettype($arg);
					}
				}
				echo '#'.$n.' '.$class.$dump['function'].'('.str_replace('<','&lt;',implode(', ',$dump['args'])).') llamada desde ['.$dump['file'].':'.$dump['line'].']'."\n";
			}
			if (isset($_GET['trace_memory'])){
				echo "\n\tHasta ahora se ha consumido ".round(memory_get_peak_usage()/1024,2)." Kbytes de memoria pico y ".round(memory_get_usage()/1024,2)." Kbytes a su finalización.";
			}
			if (!self::isCLI()) echo "</pre>";
		}
		if (isset($_GET['trace']) && !self::isCLI()) echo "</div>";
	}
	
	/**
	 * Devuelve el número actual en microsegundos timestamp
	 * @return number
	 */
	static function getMicroTime(){
		$arraytime = explode(" ", microtime());
		return ( (float) $arraytime[0] + (float) $arraytime[1]);
	}
	
	/**
	 * Devuelve la ip real, intentando sacar la ip real aunque tenga proxys
	 * @return string
	 */
	static function getRealIP(){
		GBLTeenvio::setDisplayErrors(0);
		error_reporting(0);
   
		if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' )
		{
		$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
			$_SERVER['REMOTE_ADDR']
			:
			( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
		
		// los proxys van añadiendo al final de esta cabecera
		// las direcciones ip que van "ocultando". Para localizar la ip real
		// del usuario se comienza a mirar por el principio hasta encontrar
		// una dirección ip que no sea del rango privado. En caso de no
		// encontrarse ninguna se toma como valor el REMOTE_ADDR
		
		$entries = preg_split('/[, ]/', $_SERVER['HTTP_X_FORWARDED_FOR']);
		
		reset($entries);
		while (list(, $entry) = each($entries))
		{
			$entry = trim($entry);
			if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) )
			{
			// http://www.faqs.org/rfcs/rfc1918.html
			$private_ip = array(
				'/^0\./',
				'/^127\.0\.0\.1/',
				'/^192\.168\..*/',
				'/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
				'/^10\..*/');
		
			$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
		
			if ($client_ip != $found_ip)
			{
			$client_ip = $found_ip;
			break;
			}
			}
		}
		}
		else
		{
		$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
			$_SERVER['REMOTE_ADDR']
			:
			( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
		}
			error_reporting(E_ALL);
		return $client_ip;
	}
	
	/**
	 * Chequea si una ip está dentro de la red CIDR especificada
	 * 
	 * @param string $ip Ip a chequear
	 * @param string $ipcidr La IP de red en formato CIDR (192.168.1.0/24)
	 * @return boolean
	 */
	static function ipMatch($ip, $ipcidr) {
		$parts=explode('/', $ipcidr);
		$subnet=$parts[0];
		
		if ($subnet=='0.0.0.0') return true;
		
		$mask=32;
		if (count($parts)==2){
			$mask=(int) $parts[1];
		}
		
		if(((ip2long($ip) & ($mask = ~ ((1 << (32 - $mask)) - 1))) == (ip2long($subnet) & $mask))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Devuelve un string codificado en base64 con ofuscación
	 * @param string $str
	 * @return string 
	 */
	static function base64ofuscateEncode($str){
		$base=base64_encode($str);
		$str=substr($base,0,2);
		$str.=substr(md5($base),3,2).substr($base,2);
		return $str;
	}
	
	/**
	 * Decodifica una cadena base64 ofuscada por base64ofuscateEncode
	 * @param string $str
	 * @return string 
	 */
	static function base64ofuscateDecode($str){
		$base=$str;
		$str=substr($base,0,2);
		$str.=substr($base,4);
		return base64_decode($str);
	}
	
	/**
	 * Devuelve true si se esta ejecutando mediante CLI (Script consola, no web)
	 * @return boolean
	 */
	static function isCLI(){
		return (PHP_SAPI == 'cli');
	}
	
	/**
	 * Limpia los caracteres de una cadena para el envio de SMS
	 * @param string $str
	 * @return string
	 */
	static function limpiaCaracteresSMS($str){
		$trans = array("á" => "a", "é" => "e", "í" => "i", "ó" => "o", "ú" => "u", "ç" => "c", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O", "Ú" => "U", "Ç" => "C", '"'=>"'","·"=>".", "#"=>"");
		$str = strtr($str, $trans);
		return $str;
	}
	
	/**
	 * Devuelve la ruta completa de una ruta dada desde el include_path
	 * Busca el fichero en todas las rutas del include_path y devuelve un path completo
	 * @param string $path
	 * @return string
	 */
	static function getFullPath($path){
		if (is_file($path) || is_dir($path)){
			return $path;
		}
		
		$include_path=ini_get('include_path');
		
		$paths=explode(PATH_SEPARATOR,$include_path);
		
		foreach($paths as $single_path){
			$single_path=trim($single_path);
			if (is_file($single_path."/".$path) || is_dir($single_path."/".$path)){
				return str_replace('//','/',$single_path."/".$path);
			}
		}
		return false;
	}
	
	/**
	 * Intenta detectar la codificación a base de probar a encontar acentos o eñes
	 * @param string $bruto
	 * @return string 
	 */
	static function detectaCodificacion($bruto){
		
		$codificaciones=array('utf-8'=>'utf-8','iso-8859-15'=>'utf-8');
			
		foreach($codificaciones as $origen=>$encoding){
		
			$a_buscar=array(
			    'á', 'à', 'ä', 'â', 'Á', 'À', 'Â', 'Ä',
			    'é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë',
			    'í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î',
			    'ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô',
			    'ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü',
			    'ñ','€','Ñ','¿','¡','ç','Ç');
				
			if ($origen=='utf-8'){
				$tmp=$bruto;
			}else{
				$tmp=@iconv($origen, $encoding, $bruto);
			}
			
			foreach($a_buscar as $caracter){
				if (strpos($tmp,$caracter)!==false){
					return $origen;
				}				
			}
		}
		return 'utf-8';
	}
        
        /**
         * Devuelve los permisos de un fichero/directorio más su propietaryo y grupo 
	 * (-rw-r--r--33:33)
         * @param type $file
         * @return string
         */
        static function getFilePermissions($file){
                
                $permisos = fileperms($file);

                if (($permisos & 0xC000) == 0xC000) {
                    // Socket
                    $info = 's';
                } elseif (($permisos & 0xA000) == 0xA000) {
                    // Enlace Simbólico
                    $info = 'l';
                } elseif (($permisos & 0x8000) == 0x8000) {
                    // Regular
                    $info = '-';
                } elseif (($permisos & 0x6000) == 0x6000) {
                    // Especial Bloque
                    $info = 'b';
                } elseif (($permisos & 0x4000) == 0x4000) {
                    // Directorio
                    $info = 'd';
                } elseif (($permisos & 0x2000) == 0x2000) {
                    // Especial Carácter
                    $info = 'c';
                } elseif (($permisos & 0x1000) == 0x1000) {
                    // Tubería FIFO
                    $info = 'p';
                } else {
                    // Desconocido
                    $info = 'u';
                }

                // Propietario
                $info .= (($permisos & 0x0100) ? 'r' : '-');
                $info .= (($permisos & 0x0080) ? 'w' : '-');
                $info .= (($permisos & 0x0040) ?
                            (($permisos & 0x0800) ? 's' : 'x' ) :
                            (($permisos & 0x0800) ? 'S' : '-'));

                // Grupo
                $info .= (($permisos & 0x0020) ? 'r' : '-');
                $info .= (($permisos & 0x0010) ? 'w' : '-');
                $info .= (($permisos & 0x0008) ?
                            (($permisos & 0x0400) ? 's' : 'x' ) :
                            (($permisos & 0x0400) ? 'S' : '-'));

                // Resto
                $info .= (($permisos & 0x0004) ? 'r' : '-');
                $info .= (($permisos & 0x0002) ? 'w' : '-');
                $info .= (($permisos & 0x0001) ?
                            (($permisos & 0x0200) ? 't' : 'x' ) :
                            (($permisos & 0x0200) ? 'T' : '-'));

                $info=$info.fileowner($file).":".filegroup($file);
                
                return $info;
                
        }
	
	/**
	 * Crea una carpeta creando los directorios padre si es necesario, si la ruta ya existe devuelve directamente true, por ejemplo:
	 * - UTLUtilidades::mkdir('v3/uploads', 'teenviov3/piezas/123/imgs');
	 * @param string $base Ruta partiendo de include_path, siempre debe existir (v3/uploads/, v4/private/data/, etc)
	 * @param string $path Parte de la ruta en duda incluyendo la carpeta que deseamos crear
	 * @param int $permisos Permisos en octal, por defecto 0775 (rwxrwxr-x)
	 * @return boolean
	 */
	static function mkdir($base,$path,$permisos=0775){
		$path_base=self::getFullPath($base);
		if (!$path_base) return false;
		
		if (is_dir($path_base.'/'.$path)) return true;
		
		$partes=explode('/',$path);
		$ruta='/';
		foreach($partes as $parte){
			$ruta.=$parte.'/';
			if (!is_dir($path_base.$ruta)){
				if (!@mkdir($path_base.$ruta)) return false;
				chmod($path_base.$ruta,$permisos);
			}
		}
		
		if (!is_dir($path_base.'/'.$path)) return false;
		
		return true;
	}
	
	/**
	 * Remplaza la última coincidencia encontrada en la cadena
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 * @return string
	 */
	static function str_lreplace($search, $replace, $subject){
		
		$pos = strrpos($subject, $search);

		if($pos !== false){
		    $subject = substr_replace($subject, $replace, $pos, strlen($search));
		}

		return $subject;
	}
	
	/**
	 * Remplaza la primera coincidencia encontrada en la cadena
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 * @return string
	 */
	static function str_freplace($search,$replace,$subject){
		return preg_replace('/'.preg_quote($search,'/').'/', $replace, $subject, 1);
	}
	
	/**
	 * Remplaza la primera coincidencia encontrada en la cadena (case-insensitive)
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 * @return string
	 */
	static function str_ifreplace($search,$replace,$subject){
		return preg_replace('/'.preg_quote($search,'/').'/i', $replace, $subject, 1);
	}
	
	/**
	 * Partiendo de la cadena en UTF-8 la devuelve en ISO-8859-15
	 * @param string $str
	 * @return string
	 */
	static function utf8_decode_to_iso($str){
		return iconv('UTF-8','ISO-8859-15//TRANSLIT',$str);
	}
	
	/**
	 * Partiendo de la cadena en ISO-8859-15 la devuelve en UTF-8
	 * @param string $str
	 * @return string
	 */
	static function utf8_encode_from_iso($str){
		return iconv('ISO-8859-15//TRANSLIT','UTF-8',$str);
	}
	
	/**
	 * Retorna el tamaño de un directorio en bytes
	 * @param string $dir
	 */
	static function dirSize($dir){
		$size=0;		
		if (is_dir($dir)){
			$size = 0;
			foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
				$size += is_file($each) ? filesize($each) : self::dirSize($each);
			}
		}
		return $size;
	}
}

?>
