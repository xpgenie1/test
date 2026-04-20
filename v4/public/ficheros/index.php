<?php
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/UTL/UTLHttp.php';
require_once 'v4/private/class/GBL/GBLSession.php';
require_once 'v4/private/class/FICH/FICHFicherosOU.php';


GBLSession::checkLogin();

$page= GUIController::getInstance();

$page->setSeccion(LANGBase::__('Herramientas'));
$page->setSubseccion(LANGBase::__('Ficheros'));
$page->addCSS('/v4/public/css/base.css');
$page->addJSEndBodyDeclaration('
		if (typeof(jQuery)=="function") jQuery.noConflict();
	');

try{
        $salida = new FICHFicherosOU(GBLSession::getPlan());
        if (isset($_GET['mode']) && $_GET['mode']=='window'){
                $salida->setModeWindow();
                UTLHttp::sendCharsetUTF8();
                echo $salida->getOutput();
                die();
        }else{
        
                
                if (isset($_GET['search'])) $salida->setSearch($_GET['search']);
                $page->setContent($salida->getOutput());
        }
}catch(TeException $ex){
	if ($ex->getClassName()=='PERPermisos' && $ex->getCode()===403){
		UTLHttp::sendRedirect302('/v4/public/home/#permisos_insuficientes=1');
	}
	UTLHttp::sendErrorInternoDeServidor('', $ex);
}

$page->setCharsetUTF8();
echo $page->getOutput();
?>