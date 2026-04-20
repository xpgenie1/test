<?php
require_once 'v4/private/class/USU/USUPlanMDL.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/UTL/UTLIni.php';

/**
* Clase para el manejo de Ficheros
* @author Javier Fernández Gutiérrez <javi.fernandez@ipdea.com>
* @package FICH
*/

class FICHFicheros{
	
	const HASH_FILE='_$86541_';
	
	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * @var string
	 */
	private $filesPath='';
	
	/**
	 * @param string $plan
	 * @throws TeException
	 */
	public function __construct($plan) {
		if (empty($plan)){
			throw new TeException('No se ha pasado el plan al constructor',__LINE__,__CLASS__);
		}
		$this->plan=$plan;
		$this->filesPath=$this->getFilesPath();
	}
	
	/**
	 * @return string
	 */
	public function getFilesPath(){
		
		$path=UTLUtilidades::getFullPath(USUPlanMDL::FILES_PATH_BASE).'/'.$this->plan;
		if (is_dir($path)){
			return $path.'/';
		}else{
			return USUPlanMDL::getInstance($this->plan,true)->getFilesPath().'/';
		}
	}
	
	/**
	 * Devuelve un array con las carpetas y los ficheos
	 * @param string $path
	 */
	public function mostrarFicheros($path){
		
		if (!is_dir($this->filesPath.'ficheros')){
			if (!mkdir($this->filesPath.'ficheros')) throw new TeException('No se ha podido crear la carpeta ficheros', __LINE__,__CLASS__);
		}
		
		$real_path = $this->filesPath.'ficheros';
		
		if ($real_path!='/') $real_path= $real_path.'/'.$path;
		
		$array_ficheros =array();
		$array_ficheros['carpetas'] = array();
		$array_ficheros['ficheros'] = array();
		
                if (!is_dir($real_path)){
                        $real_path=  utf8_decode($real_path);
                }
		
		$this->checkAccess('', $path);
                
		$dir = @opendir($real_path);
		
		if ($dir == false){
			throw new TeException('Fallo al abrir la carpeta '.$real_path,__LINE__,__CLASS__);
		}
		while (false !== ($elemento = readdir($dir))){
                        if ($elemento == ".." || $elemento == "." || $elemento=='.metadatos') continue;
                        
                        $strNombre=$elemento;
                        json_encode($elemento);
                        if (json_last_error()==JSON_ERROR_UTF8){
                                $strNombre=utf8_encode($elemento);
                        }
			if(is_dir($real_path."/".$elemento)){
				
				$array_ficheros['carpetas'][$elemento] = array();
				$array_ficheros['carpetas'][$elemento]['nombre'] = $strNombre;
				$array_ficheros['carpetas'][$elemento]['fecha_add'] = date("d/m/Y H:i:s", filemtime($real_path.'/'.$elemento));
				
			}else{
				$array_ficheros['ficheros'][$elemento] = array();
				$array_ficheros['ficheros'][$elemento]['nombre'] = $strNombre;
				$array_ficheros['ficheros'][$elemento]['fecha_add'] = date("d/m/Y H:i:s", filemtime($real_path.'/'.$elemento));
				$array_ficheros['ficheros'][$elemento]['type'] = mime_content_type($real_path.'/'.$elemento);
				$tamano_bytes = filesize($real_path.'/'.$elemento);
				
				if ($tamano_bytes > 1024){
					if ($tamano_bytes > 1048576){
						$tamano = $tamano_bytes / 1048576;
						$tamano = round($tamano,2)." MB";
					}else{
						$tamano = $tamano_bytes / 1024;
						$tamano = round($tamano,2)." KB";
					}
				}else{
					$tamano = round($tamano_bytes,2)." Bytes";
				}
				
				$array_ficheros['ficheros'][$elemento]['size'] = $tamano;
				$hash = $this->creaHash($elemento, $path, USUUsuarioMDL::getUsuarioActivo()->getUser());
				$array_ficheros['ficheros'][$elemento]['hash'] = $hash;
			}
		}
		
		return $array_ficheros;
	}
	
	/**
	 * Sube un archivo a la ruta indicada, es necesario pasarle el nombre del 'file'
	 * @param string $filename
	 * @param string $path
	 */
	
	public function uploadFile($filename,$path=null){
		
		$real_path = $this->filesPath.'ficheros';
		
		if ($path!=null){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path.'/'.$path;
		}
		
		if (!isset($_FILES[$filename]['tmp_name'])) return '';
		
		if(!is_uploaded_file($_FILES[$filename]['tmp_name'])){
			return '';
			//throw new TeException('El fichero no parece un fichero subido autorizado o ha excedido el tamaño máximo.', __LINE__,__CLASS__);
		}
		
		//if (!is_dir($real_path)) 
				
		if (!is_dir($real_path)){
			throw new TeException('No se ha podido generar la carpeta para ficheros', __LINE__,__CLASS__);
		}
		
		//Máximo 500Kb
// 		if (filesize($_FILES[$filename]['tmp_name'])>512*1024){
// 			throw new TeException('El fichero subido excede de los 500Kb', __LINE__,__CLASS__);
// 		}
		
		$name_file = $this->checkName($_FILES[$filename]['name'], $path);
			
		if (!copy($_FILES[$filename]['tmp_name'],  $real_path.$name_file)){
			throw new TeException('No se ha podido copiar el fichero subido', __LINE__,__CLASS__);
		}
		
		$this->creaHash($name_file, $path, USUUsuarioMDL::getUsuarioActivo()->getUser());
		return true;
			
	}
	
	/**
	 * Crea una carpeta
	 * @param string $name
	 * @param string $path
	 * @throws TeException
	 */
	
	public function creaCarpeta($name, $path=null){
		
		if (strpos($name, '../')!==false) $name = str_replace('../', '', $name);
		if (strpos($name, './')!==false) $name = str_replace('./', '', $name);
		if (strpos($name, '/')!==false) $name = str_replace('_', '', $name);
		
		$real_path = $this->filesPath.'ficheros/';
		
		if ($path!=null){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path.$path;
		}
		
		$this->checkAccess('', $path);

		$real_name = $this->checkNameFolder($name, $path);
		if (!@mkdir($real_path."/".$real_name)){ //creo la carpeta con el nombre pasado
			throw new TeException('No se ha podido crear la carpeta: '.$real_name.' en '.$real_path, __LINE__,__CLASS__);
		}
	}
	
	/**
	 * Elimina un fichero
	 * @param string $hash
	 * @param string $path
	 * @throws TeException
	 */
	
	public function eliminaFichero($hash, $path=null){
	
		$real_path = $this->filesPath.'ficheros';
	
		if ($path!=null){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path.$path;
		}
		
		$this->checkAccess('', $path);
		
		$name = $this->getPropiedadesHash($hash);
		$name = $name['PROPIEDADES']['nombre'];
		if (!@unlink($real_path."/".$name)){ //elimino el fichero
			throw new TeException('No se ha podido eliminar  el fichero en '.$real_path."/".$name, __LINE__,__CLASS__);
		}
		
		$this->eliminaHash($hash);
		
	}
	
	/**
	 * Elimina una carpeta y todo su contenido
	 * @param string $path
	 * @param string $name
	 * @throws TeException
	 */
	
	public function eliminarCarpeta($path){
		if (strpos($path, '../')!==false) $path = str_replace('../', '', $path);
		if (strpos($path, './')!==false) $path = str_replace('./', '', $path);
			
		$real_path = $this->filesPath.'ficheros';
	
		if ($path!=null){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path.$path;
		}
		
		$this->checkAccess('', $path);

                exec("rm -rf ".escapeshellarg($real_path));
                /* Refrescamos la caché porque lo hemos eliminado desde fuera de php*/
		clearstatcache();
		
                if (is_dir($real_path)){
			throw new TeException('No se ha podido eliminar  la carpeta', __LINE__,__CLASS__);
		}
	}
	
	
	
	/**
	 * Renombra un fichero
	 * @param string $path
	 * @param string $name
	 * @param string $name_old
	 * @throws TeException
	 */
	
	public function renombraFichero($path, $name, $name_old){
		if (strpos($path, '../')!==false) $path = str_replace('../', '', $path);
		if (strpos($path, './')!==false) $path = str_replace('./', '', $path);
		
		$real_path_base = realpath($this->filesPath.'ficheros');
		
		if ($path!=''){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path_base.$path;
		}		
		
		$this->checkAccess($name, $path);
		
		$name = $this->checkName($name,$path);
                
                $file_orig=$real_path.$name_old;
                if (!is_file($real_path.$name_old)){
                        $file_orig=$real_path.utf8_decode($name_old);
                }

		if (!rename($file_orig, $real_path.$name)){ 
                        throw new TeException('No se ha podido renombrar el archivo en '.$real_path, __LINE__,__CLASS__);
		}
		
		$hash = md5($name.str_replace('/', '', $path).self::HASH_FILE); //se puede poner en un getHash();
		$this->eliminaHash($hash);
		$this->creaHash($name,$path,USUUsuarioMDL::getUsuarioActivo()->getUser());
	}
	
	/**
	 * Renombra una carpeta
	 * @param string $path
	 * @param string $name
	 * @param string $name_old
	 * @throws TeException
	 */
	
	public function renombraFolder($path, $name, $name_old){
		if (strpos($path, '../')!==false) $path = str_replace('../', '', $path);
		if (strpos($path, './')!==false) $path = str_replace('./', '', $path);
			
		$real_path_base = realpath($this->filesPath.'ficheros');
		
		if ($path!=''){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path_base.$path;
		}		
		
		$this->checkAccess($name, $path);
		
		$name = $this->checkNameFolder($name, $path);
	
                $file_orig=$real_path.$name_old;
                if (!is_file($real_path.$name_old)){
                        //$file_orig=$real_path.utf8_decode($name_old);
			$file_orig=$real_path.($name_old);
                }
                
		if (!rename($file_orig, $real_path.$name)){
			throw new TeException('No se ha podido renombrar la carpeta '.$file_orig.' - '.$real_path.$name, __LINE__,__CLASS__);
		}
		
	}
	
	
	/**
	 * Comprueba si ya existe un fichero con ese nombre y lo renombra
	 * @param string $name
	 * @param string $path
	 * @throws TeException
	 */
	
	public function checkName($name, $path='/'){

		$real_path = $this->filesPath.'ficheros/';
		
		if ($path!='/'){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path.$path;
		}

		if (strpos($name, '../')!==false) $name = str_replace('../', '', $name);
		if (strpos($name, './')!==false) $name = str_replace('./', '', $name);
		
		$name_file = substr($name, 0, strripos($name, '.'));
		$ext_file = substr($name, strripos($name, '.'));
		
		$cont=0;
		
		while (is_file($real_path.$name)){
			$cont++;
			$name = $name_file."_".$cont.$ext_file;
		}

		return $name;
	}
	
	/**
	 * Comprueba si ya existe una carpeta con ese nombre y lo renombra
	 * @param string $name
	 * @param string $path
	 * @throws TeException
	 */
	
	public function checkNameFolder($name, $path='/'){
	
		$real_path = $this->filesPath.'ficheros/';
		
		if ($path!='/'){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path.$path;
		}
		
		$cont=0;
		$name_file = $name;
		while (is_dir($real_path."/".$name)){
			$cont++;
			$name = $name_file."_".$cont;
		}
		
		return $name;
	}
	
	
	public function checkAccess($name,$path='/'){
		
		$real_path_base = realpath($this->filesPath.'ficheros');
		
		if ($path!=''){ //he recibido los parametros para la subcarpeta
			$real_path = $real_path_base.$path;
		}		
		
		$name_path= realpath(UTLUtilidades::getFullPath($real_path.substr($name,0,strrpos($name,'/'))));
		
                if(substr($name_path,0,strlen($real_path_base))!=$real_path_base){
                        throw new TeException('Acceso denegado, se está intentando acceder a un recurso no permitido', __LINE__,__CLASS__);
		}
		
	}
		
	/**
	 *  Crea el hash del fichero subido
	 * @param string $name
	 * @param string $path
	 * @param string $user
	 * @throws TeException
	 */
	
	public function creaHash($name, $path='/', $user=null){

		$real_path = $this->filesPath.'ficheros/.metadatos/';
		
		//Siempre tiene que acabar en /
		if (substr($path,-1)!='/') $path.='/';
		
		if (!is_dir($real_path)){
			if (!@mkdir($real_path)) throw new TeException('No se ha podido crear la carpeta metadatos '.$real_path, __LINE__,__CLASS__);
		}
		
		if ($user==null) $user = "Desconocido";
		
		$hash = md5($name.str_replace('/', '', $path).self::HASH_FILE);
		
		$hash_folder = '.'.substr($hash , 0,1).'/'; //carpeta donde se guardará el hash
		
		if (!is_dir($real_path.$hash_folder)){
			if (!@mkdir($real_path.$hash_folder)) throw new TeException('No se ha podido crear la carpeta hash '.$real_path, __LINE__,__CLASS__); 
		}
		
                
               
		if (!is_file($real_path.$hash_folder.$hash.'.ini')){
                        
			$datos = array();
			$datos['PROPIEDADES']=array();
			$datos['PROPIEDADES']['nombre'] = $name;
                        $datos['PROPIEDADES']['ruta'] = $path.$name;
                        $datos['PROPIEDADES']['extension'] = substr($name, strripos($name, '.'));
                        $datos['PROPIEDADES']['fecha_add'] = UTLDateTime::now()->getDateTimeBD();
			$datos['PROPIEDADES']['autor'] = $user;
                        
                        UTLIni::addEmptyFile($real_path.$hash_folder.$hash.'.ini', 'ficheros');
                        UTLIni::$conf['ficheros']=$datos;
                        UTLIni::writeINI('ficheros');			
		}

		return $hash;
		
	}
	
	/**
	 *  Crea el hash del fichero subido
	 * @param string $hash
	 * @throws TeException
	 */
	
	private function eliminaHash($hash){
	
		$real_path = $this->filesPath.'ficheros/.metadatos/';
	
		if (!is_dir($real_path)){
			if (!mkdir($real_path)) throw new TeException('No se ha podido crear la carpeta metadatos '.$real_path, __LINE__,__CLASS__);
		}
		
		$hash_folder = '.'.substr($hash , 0,1).'/'; //carpeta donde se guardará el hash
	
		if (is_file($real_path.$hash_folder.$hash.'.ini')){
			if (!unlink($real_path.$hash_folder.$hash.'.ini')) throw new TeException('No se ha podido borrar el hash '.$real_path, __LINE__,__CLASS__);
		}
	
	
	}
	
	/**
	 * A partir de un hash Devuelve un array con los datos del fichero hash
	 * @param string $hash
	 * @throws TeException
	 */
	public function getPropiedadesHash($hash){
		
		$real_path = $this->filesPath.'ficheros/.metadatos/';
		if (!is_dir($real_path)){
			if (!mkdir($real_path)) throw new TeException('No se ha podido crear la carpeta metadatos '.$real_path, __LINE__,__CLASS__);
		}
		
		$ruta=$real_path.'.'.substr($hash,0,1);
		
		if (!is_dir($ruta)) throw new TeException('No se ha encontrado la carpeta donde está el hash '.$hash, 4041,__CLASS__); 
		if (!is_file($ruta.'/'.$hash.'.ini')) throw new TeException('No se ha encontrado el fichero hash '.$hash, 4042,__CLASS__); 
		UTLIni::addIniFile($ruta.'/'.$hash.'.ini', 'PROPIEDADES');
		$propiedades =UTLIni::$conf['PROPIEDADES'];
		return  $propiedades;
	}
	
		
}
?>