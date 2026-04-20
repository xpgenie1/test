<?php
require_once 'v4/private/class/UTL/UTLLog.php';
require_once 'v4/private/class/GBL/GBLSession.php';

/**
 * Clase de Excepciones para Teenvio
 * Genera un log con todas las excepciones
 * 
 * @author Victor J. Chamorro <victor@ipdea.com>
 * @package UTL
 * @copyright Ipdea Land, S.L. / Teenvio
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
 *
 * You should have received a copy of the GNU LESSER General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class TeException extends Exception{
	
	private $classname="";

	/**
	 * Devuelve un nuevo objeto de Excepción TeException
	 * Si $_GET['trace'] == 1 añadirá la traza al log
	 * @param string $mensaje
	 * @param int $codigo
	 * @param string $clase
	 */
	function __construct($mensaje,$codigo,$clase=""){
		if ($clase=="") $clase=__CLASS__;
		parent::__construct($mensaje,(int) $codigo);
		
		if (GBLSession::getPlan()){
			$this->message= GBLSession::getPlan().' '.$this->message;
		}
		
		//Si nos llega el parámetro trace, guardamos la traza en el log
		if (isset($_GET['trace']) && $_GET['trace']=="1") $this->message.="\n".$this->getTraceAsString();
		
		$this->classname=$clase;
		$this->guardaLog();
	}
	
	/**
	 * Registra la excepción en el log
	 */
	private function guardaLog(){
		UTLLog::guardaLog($this->classname, $this->classname, ": [".$this->code." - ".$this->file." - ".$this->line."]: ".$this->message);
	}
	
	/**
	 * Devuelve el nombre de la clase que lanzó la excepción o una cacena en blanco si no se indicó.
	 * @return string
	 */
	public function getClassName(){
		return $this->classname;
	}

}

function gestor_excepciones($excepcion) {
	try{
		throw new TeException("FATAL ERROR: Excepción no capturada: " . $excepcion->getMessage(),0);
	}catch(TeException $e){
	}catch(Exception $e){}
	echo "FATAL ERROR: Excepción no capturada: " . $excepcion->getMessage(). "\n";
	if (UTLUtilidades::isDebug()) echo $excepcion->getTraceAsString();
}

/**
 * Control de errores generales 
 */
function gestor_errores($errno, $errstr, $errfile, $errline){
	
	// error was suppressed with the @-operator
	if (0 === error_reporting()) { return false;}
	
	try{
		throw new TeException("GLOBAL ERROR: ".$errstr." - ".$errno.", en el fichero $errfile y la línea $errline",99,"");
	}catch(TeException $e){}
}


set_exception_handler('gestor_excepciones');
set_error_handler("gestor_errores");

?>