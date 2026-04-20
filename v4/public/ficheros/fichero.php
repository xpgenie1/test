<?php
require_once 'v4/private/class/UTL/UTLHttp.php';
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/FICH/FICHFicheros.php';

if (!isset($_GET['file'],$_GET['plan'])){
	UTLHttp::sendNotFound();
}

// Muestra o descarga un fichero
try{

	if (isset($_GET['file']) && $_GET['file']!=''){
		
		$objFicheros = new FICHFicheros($_GET['plan']);
		$propiedades = $objFicheros->getPropiedadesHash($_GET['file']);
		
		$file_path=$objFicheros->getFilesPath().'ficheros/'.$propiedades['PROPIEDADES']['ruta'];
		if (file_exists($file_path)) {
			
			if (isset($_GET['mode'])){
				
				$finfo = new finfo(FILEINFO_MIME | FILEINFO_SYMLINK);
				$content_type= $finfo->file($file_path);
				if (!$content_type){
					$content_type='application/download';
				}
				
				UTLHttp::sendContentTypeInline($propiedades['PROPIEDADES']['nombre'],$content_type); //Inline
				
			}else{
				UTLHttp::sendContentTypeDownload($propiedades['PROPIEDADES']['nombre']); //download
			}
			
			header("Cache-Control: private, max-age=10800, pre-check=10800");
			header("Pragma: ");
			header("Expires: " .gmdate('D, d M Y H:i:s \G\M\T',strtotime(" 1 day")));

			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($file_path) ) {
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file_path)).' GMT', true, 304);
				die();
			}else{
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file_path)).' GMT');	
			}
			
			echo file_get_contents($file_path);
			die();
		}
	}
	UTLHttp::sendNotFound();
}catch(TeException $e){
	if ($e->getClassName()=='FICHFicheros' && ($e->getCode()==4041 || $e->getCode()==4042)){
		UTLHttp::sendNotFound(true,  'El fichero solicitado no existe o ha sido borrado');
	}else{	
		UTLHttp::sendErrorInternoDeServidor('', $e);
	}
}

?>