<?php
require_once 'v4/private/class/UTL/UTLLog.php';
/**
 *
 * Clase estática encargada del manejo de procesos en segundo plano
 * 
 * @package UTL
 * @author Victor J Chamorro - victor@ipdea.com
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
class UTLBackgroundProcess{
	
	/**
	 * Clase estática, no se puede instanciar
	 */
	private function __construct() {}
	
	/**
	 * Coleccion de procesos lanzados
	 * @var array:[process]int
	 */
	private static $currentProcess=array();
	
	/**
	 * Inicia un proceso en segundo plano, devuelve su PID.
	 *  Si el proceso se ejecuta en web, el usaurio que ejecute el proceso será apache (www-data),
	 *  si es un script cli, será el mismo usuario que ejecute el cli
	 * @param string $command Comando a ejecutar
	 * @param string $alias Alias opcional con el que identificar el proceso
	 * @param string $salida Ruta donde será redirigida la salida estándar del ejecutable
	 * @param string $errores Ruta donde será redirigida la salida de errores
	 * @return int
	 */
	public static function startProcess($command,$alias='',$salida='/dev/null',$errores='/dev/null'){
		if (empty($alias)) $alias=$command;
		
		$PID = trim(shell_exec("nohup $command > $salida 2> $errores < /dev/null & echo $!"));
		self::$currentProcess[$alias]=$PID;
		UTLLog::guardaLog(__CLASS__, 'SART_PROCESS', 'Nuevo Proceso lanzado con PID '.$PID.': '.$command);
		
		return $PID;
		
	}
	
	/**
	 * Mata un proceso lanzado
	 * @param string $alias
	 * @return boolean
	 */
	public static function killProcess($alias){
		if (isset(self::$currentProcess[$alias])){
			self::killPID(self::$currentProcess[$alias]);
			return true;
		}else{
			false;
		}
	}
	
	/**
	 * Mata el proceso cuyo $PID es pasado
	 * @param int $PID 
	 */
	public static function killPID($PID){
		exec("kill $PID");
	}
	
	/**
	 * Comprueba si un pid está o no ejecutándose
	 * @param int $PID
	 * @return boolean
	 */
	public static function isRunningPID($PID){
		exec("ps $PID", $ProcessState);
		return(count($ProcessState) >= 2);
	}
	
	/**
	 * Comprueba si un proceso lanzado por UTLBackgroundProcess::startProcess está ejecutándose
	 * @param string $alias
	 * @return boolean 
	 */
	public static function isRunningProcess($alias){
		if (isset(self::$currentProcess[$alias])){
			return self::isRunningPID(self::$currentProcess[$alias]);
		}else{
			false;
		}
	}
	
	/**
	 * Comprueba la lista de procesos ejecutados y devuelve aquellos que siguen vivos
	 * @return array
	 */
	public static function getCurrentProcess(){
		foreach(self::$currentProcess as $alias=>$pid){
			if (!self::isRunningPID($pid)) unset(self::$currentProcess[$alias]);
		}
		
		return self::$currentProcess;
	}
	
	/**
	 * Obtiene información del proceso del pid pasado
	 * @param int $pid
	 * @param string $ps_opt
	 * @return string
	 */
	public static function getPidInfo($pid, $ps_opt="u"){

		$ps=explode("\n", shell_exec("ps ".$ps_opt."p ".$pid));

		if(count($ps)<2){
		   return null;
		}

		foreach($ps as $key=>$val){
		   $ps[$key]=explode(" ", ereg_replace(" +", " ", trim($ps[$key])));
		}
		
		$pidinfo=array();

		foreach($ps[0] as $key=>$val){
		   $pidinfo[$val] = $ps[1][$key];
		   unset($ps[1][$key]);
		}

		if(is_array($ps[1])){
		   $pidinfo[$val].=" ".implode(" ", $ps[1]);
		}
		return $pidinfo;
	}

}

?>