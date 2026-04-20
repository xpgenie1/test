<?php
require_once 'v4/private/class/UTL/UTLUtilidades.php';
require_once 'v4/private/class/UTL/UTLTeException.php';
require_once 'v4/private/class/LANG/LANGBase.php';
require_once 'v4/private/class/GBL/GBLSession.php';

/**
 * Clase para el manejo de fechas en PHP, incluyendo el manejo de Zonas Horarias.
 * Extiende la clase nativa DateTime (PHP 5 >= 5.2.0)
 * @author Victor J Chamorro - victor@ipdea.com
 * @package UTL
 * @see http://es1.php.net/manual/es/class.datetime.php
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
 */
class UTLDateTime extends DateTime{
	
	/**
	 * @var string Formato de fecha y hora común
	 */
	private $format='';
	
	/**
	 * @var string Formato de fecha corto
	 */
	private $format_date='';
	
	/**
	 * @var string Formato de hora corto
	 */
	private $format_hour='';
	
	/**
	 * @var string Formato de fecha en texto largo
	 */
	private $format_long='';
	
	/**
	 * @var string Formato de fecha y hora en texto largo
	 */
	private $format_long_hour='';
	
	/**
	 * Crea un objeto UTLDateTime
	 * @param string $strdatetime Fecha y hora en el formato AAAA-MM-DD HH:MM:SS (like MySQL DATETIME)
	 * @param string $timezone Por defecto Europe/Madrid
	 * @throws TeException
	 */
	public function __construct($strdatetime="",$timezone="Europe/Madrid"){
		
		//Miramos si el TimeZone es correcto
		$datetimezone=null;
		try{
			$datetimezone=new DateTimeZone($timezone);
		}catch (Exception $e){
			try{throw new TeException("Error al intentar establecer '$timezone' como TimeZone, se utilizará Europe/Madrid".$e->getMessage(), __LINE__,__CLASS__);
			}catch(TeException $e){
				if (UTLUtilidades::isDebug()) UTLUtilidades::echoDebug("\n\tError al intentar establecer '$timezone' como TimeZone, se utilizará Europe/Madrid", __CLASS__);
				$datetimezone=new DateTimeZone('Europe/Madrid');
			}
		}
		try{
			parent::__construct($strdatetime,$datetimezone);
		}catch(Exception $e){
			throw new TeException($e->getMessage(), $e->getCode(), 'DateTime');
		}
	}
	
	private function initFormats(){
		
		$langBase=  LANGBase::getInstance();
		
		$this->format=		$langBase->___('d/m/Y H:i\h');
		
		$this->format_date =	$langBase->___('d/m/Y');
		$this->format_hour =	$langBase->___('H:i');
			
		$this->format_long =	$langBase->___('j \d\e F \d\e Y');
		$this->format_long_hour=$langBase->___('j \d\e F \d\e Y H:i\h');
	}
	
	/**
	 * Obtiene la fecha en el TimeZone seleccionado formateado con el patron pasado
	 * @param string $newTimeZone  [opcional, si no se pasa se autodetectará]
	 * @param string $format Formato, por defecto DD/MM/AAAA HH:MM
	 * @throws TeException
	 * @return string DD/MM/AAAA HH:MM
	 */
	public function getInTimeZone($newTimeZone="",$format=""){
		
		if (empty($format)){
			$this->initFormats();
			$format=$this->format;
		}
		
		if (empty($newTimeZone)){
			$newTimeZone=GBLSession::getTimezone();
		}
		
		//Miramos si el TimeZone es correcto
		$datetimezone=null;
		try{
			$datetimezone=new DateTimeZone($newTimeZone);
		}catch (Exception $e){
			try{
				throw new TeException("Error al intentar establecer '$newTimeZone' como TimeZone, se utilizará Europe/Madrid ".$e->getMessage(), __LINE__,__CLASS__);
			}catch(TeException $e){
				if (UTLUtilidades::isDebug()) UTLUtilidades::echoDebug("\n\tError al intentar establecer '$newTimeZone' como TimeZone, se utilizará Europe/Madrid", __CLASS__);
				$datetimezone=new DateTimeZone('Europe/Madrid');
			}
		}
		$this->setTimezone($datetimezone);
		return $this->format($format);
	}
	
	/**
	 * Obtiene la fecha en el TimeZone seleccionado y formateada, partiendo siempre de la zona Europe/Madrid
	 * @param string $strdatetime Fecha original con el uso horiario de Europe/Madrid
	 * @param string $newtimezone nueva Zona horaria  [opcional, si no se pasa se autodetectará]
	 * @return string DD/MM/AAAA HH:MM
	 * @throws TeException
	 */
	static function toNewTimeZoneFormat($strdatetime='',$newtimezone=''){
		$obj=new UTLDateTime($strdatetime);
		return $obj->getInTimeZone($newtimezone);
	}
	
	/**
	 * Devuelve el objeto con la fecha actual
	 * @return UTLDateTime
	 */
	static function now(){
		return new UTLDateTime();
	}
	
	/**
	 * Devuelve la fecha en formato AAAA-MM-DD HH:MM:SS (like MySQL DATETIME)
	 * @return string
	 */
	public function getDateTimeBD(){
		return $this->format("Y-m-d H:i:s");
	}
	
	/**
	 * Devuelve el año en formato AAAA
	 * @return string
	 */
	public function getYear(){
		return $this->format("Y");
	}
	
	/**
	 * Devuelve el mes en formato MM
	 * @return string
	 */
	public function getMonth(){
		return $this->format("m");
	}
	
	/**
	 * Devuelve el día del mes en formato DD
	 * @return string
	 */
	public function getDay(){
		return $this->format("d");
	}
	
	/**
	 * Devuelve la hora del día en formato HH
	 * @return string
	 */
	public function getHour(){
		return $this->format("H");
	}
	
	/**
	 * Devuelve el minuto de la hora del día en formato MM
	 * @return string
	 */
	public function getMinute(){
		return $this->format("i");
	}
	
	/**
	 * Devuelve el segundo del mes de la hora del día en formato SS
	 * @return string
	 */
	public function getSecond(){
		return $this->format("s");
	}
	
	/**
	 * Devuelve solo la fecha
	 * @return string
	 */
	public function getDate(){
		$this->initFormats();
		return $this->format($this->format_date);
	}
	
	/**
	 * Devuelve solo la hora, sin segundos
	 * @return type
	 */
	public function getTime(){
		$this->initFormats();
		return $this->format($this->format_hour);
	}
	
	/**
	 * Método mágico que es llamado directamente al intentar utilizar una instancia como un string (echo $instanciaUTLDateTime) d/m/Y H:i
	 * @return string 
	 */
	public function __toString(){
		return $this->toString();
	}
	
	/**
	 * Devuelve la fecha en formato string (d/m/Y H:i)
	 * @return string
	 */
	public function toString(){
		$this->initFormats();
		return $this->format($this->format);
	}
	
	/** 
	 * @see DateTime::format()
	 */
	public function format($format) {
		
		$langBase=  LANGBase::getInstance();
		
		$english = array('January', 'February', 'March', 'April', 'May', 'June', 'July','August','September','October','November','December');
		$spanish = array($langBase->___('enero'),$langBase->___('febrero'),$langBase->___('marzo'),$langBase->___('abril'),$langBase->___('mayo'),$langBase->___('junio'),$langBase->___('julio'),$langBase->___('agosto'), $langBase->___('septiembre'),$langBase->___('octubre'),$langBase->___('noviembre'),$langBase->___('diciembre'));
		return str_replace($english, $spanish, parent::format($format));
	}
	
	/**
	 * Devuelve la fecha en formato largo
	 * @param string $newTimeZone [opcional, si no se pasa se autodetectará]
	 */
	public function getDateLong($newTimeZone=''){
		$this->initFormats();
		return $this->getInTimeZone($newTimeZone,$this->format_long);		
	}
	
	/**
	 * Devuelve la fecha en formato largo
	 * @param string $newTimeZone [opcional, si no se pasa se autodetectará]
	 */
	public function getDateLongHour($newTimeZone=''){
		$this->initFormats();
		return $this->getInTimeZone($newTimeZone,$this->format_long_hour);
	}
	
	/**
	 * Carga el objeto con la fecha y hora pasados en el formato indicado
	 * @param string $strDateTime
	 * @param string $format [d/m/Y h:i:s]
	 * @throws TeException
	 */
	public function setDateTime($strDateTime,$format='d/m/Y H:i:s'){
		
		$exp_in=explode('/',str_replace(array(' ',':','-'),'/',$format));
                if (count($exp_in)==1){
                    $exp_in=str_split($format);
                }
		
		$exp=str_replace(array(
			'd','m','Y','H','h','i','s',' ','/',':','a'
		), array(
			'([0-3][0-9])',
			'([0-1][0-9])',
			'([0-9]{4})',
			'([0-2][0-9])',
			'([0-1][0-9])',
			'([0-5][0-9])',
			'([0-5][0-9])',
			'\ ',
			'\/',
			'\:',
			'(am|pm)'
		), $format);
		//"#([0-3][0-9])\/([0-1][0-9])\/([0-9]{4})\ ([0-2][0-9])\:([0-5][0-9])\:([0-5][0-9])#"
		
		$ok=preg_match("/$exp/", $strDateTime,$out);
		
		if ($ok && is_array($out)){
			$dia="00";
			$mes="00";
			$anio="0000";
			$hora="00";
			$minuto="00";
			$segundo="00";
			$pm=false;
			
			foreach($exp_in as $pos=>$var){
				switch($var){
					case 'd':
						$dia=$out[$pos+1];
						break;
					case 'm':
						$mes=$out[$pos+1];
						break;
					case 'Y':
						$anio=$out[$pos+1];
						break;
					case 'h':case 'H':
						$hora=$out[$pos+1];
						break;
					case 'i':
						$minuto=$out[$pos+1];
						break;
					case 's':
						$segundo=$out[$pos+1];
						break;
					case 'a':
						if ($out[$pos+1]=='pm'){
							$pm=true;
						}
						break;
				}
			}
			
			if ($pm && $hora!=12){
				$hora+=12;
			}
			
			$this->setDate($anio, $mes, $dia);
			$this->setTime($hora, $minuto, $segundo);
		}else{
			throw new TeException('Fallo al cargar la fecha con la expresion regular '.$exp, __LINE__,__CLASS__);
		}
		
	}
	
	/**
	 * Devuelve un ddl con los timezones
	 * @param string $name
	 * @param string $selected
	 * @param string $class
	 * @param string $pais
	 * @return string
	 * 
	 */
	static function getDDLTimeZones($name,$selected='',$class='',$pais=''){
		
		$timezones_contry=array();
		$timezones=timezone_identifiers_list();
		
		if ($pais!=''){
			$BDBase=new BDBase();
			$tableTimezones=$BDBase->BD1->SelectTabla('timezones', array('timezone'), array('iso_a2'=>$pais));
			foreach($tableTimezones->Table as $row){
				$timezones_contry[]=$row[0];
				unset($timezones[array_search($row[0], $timezones)]);
			}
		}
		
		 
		$timezones=array_merge($timezones_contry,array('----'),$timezones);
		 
		$timestamp = time();
		$str='<select name="'.$name.'" class="'.$class.'">';
		if ($pais!='') $str.='<optgroup label="'.LANGBase::__('País actual').' ('.$pais.')">';
		foreach($timezones as $zone) {
			if ($zone=='----'){
				$str.='</optgroup><optgroup label="'.LANGBase::__('Resto').'" style="opacity:0.2;">';
				continue;
			}
			date_default_timezone_set($zone);
			$str.='<option value="'.$zone.'" '.($selected==$zone ? 'selected' : '').'>'.$zone.' (GMT ' . date('P', $timestamp).')</option>';
		}
		if ($pais!='') $str.='</optgroup>';
		$str.="</select>";
		return $str;
	}
}
?>