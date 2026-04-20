<?php
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/BACK/MON/BACKMONBase.php';
require_once 'v4/private/class/UTL/UTLUtilidades.php';

class UTLPuenteSSH{
	
	/**
	 * Revisa la situación del puente ssh. Si está caido intentará reestablecerlo.
	 * Devuelve true en el caso de que el puente esta establecido o haya si do posible su reconexión
	 * @return boolean
	 */
	public static function revisaPuente(){
		
		//Comprobamos si tenemos conexión con la máquina 247
		if (!is_dir(UTLUtilidades::getFullPath("/data/main"))){
/*			//No esta conectado, intentamos montarlo:
			$time_limit_previo=GBLTeenvio::$time_limit;
			GBLTeenvio::setTimeLimit(10);
			system('umount /var/www/teenvio/v3/perl');
			//@system('sshfs root2@teconecto.com:/var/www/html/teenvio/v2/perl /var/www/teenvio/v3/perl');
			system('mount /var/www/teenvio/v3/perl');

			GBLTeenvio::setTimeLimit($time_limit_previo);
			//Comprobamos si tenemos conexión con la máquina 247
			if (!is_dir("/var/www/teenvio/v3/perl/envios")){
			*/
				try{
					throw new TeException("Error al intentar acceder al recurso NFS entre servidores", 1, __CLASS__);
				}catch(TeException $e){
					BACKMONBase::sendToXMPP($e->getMessage());
					return false;
				}				
			/*}
*/
		}
		return true;
	}
}
?>