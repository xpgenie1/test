<?php
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/UTL/UTLHttp.php';
require_once 'v4/private/class/GBL/GBLSession.php';
require_once 'v4/private/class/FICH/FICHFicherosOU.php';


try{
	

	$salida = new FICHFicherosOU(GBLSession::getPlan());
	$salida->setModeEmbed();
	UTLHttp::sendCharsetUTF8();
	echo $salida->getOutput();

}catch(TeException $ex){
	if ($ex->getClassName()=='PERPermisos' && $ex->getCode()===403){
		UTLHttp::sendRedirect302('/v4/public/home/#permisos_insuficientes=1');
	}
	UTLHttp::sendErrorInternoDeServidor('', $ex);
}
?>