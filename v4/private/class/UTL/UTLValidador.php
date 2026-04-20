<?php
/**
 * Clase estática para validaciones de datos
 * @package UTL
 * @autor Victor J Chamorro - victor@ipdea.com
 */
class UTLValidador{
	
	
	/**
	 * Valida si un NIF/CIF es un número de IVA válido a nivel europeo (VAT-ID).
	 * Utiliza una llamada SOAP a servidores europeos, sistema VIES
	 * @param string $vat El vat/nif/cif
	 * @param string $country El código internacional de pais de 2 letras (ES,PT,FR,etc)
	 * @return boolean
	 * @throws TeException (10=conexion fallida, 20=solicitud fallida)
	 * @link http://ec.europa.eu/taxation_customs/vies/vatRequest.html 
	 * @link http://www.webmastersdiary.com/2011/12/php-vies-vat-number-validation-european.html
	 */
	public static function validateVATEurope($vat,$country){
		
		$vatid = str_replace(array(' ', '.', '-', ',', ', '), '', trim($vat));
		
		$client=null;
		try{
			$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
		}catch(SoapFault $e){
			throw new TeException('Error en la conexión SOAP a europa: '.$e->getMessage(), 10,__CLASS__);
		}
		
		$params = array('countryCode' => $country, 'vatNumber' => $vatid);
		try{
			$r = $client->checkVat($params);
			return ($r->valid == true);
			
		} catch(SoapFault $e) {
			throw new TeException('Error en la solicitud SOAP a europa: '.$e->getMessage()." - ".print_r($params,true), 20,__CLASS__);
		}
		
		return false;
	}
	
	/**
	 * Valida un NIF según su letra
	 * @param string $nif
	 * @param string $country Código de país 2 letras (ES,PT,FR,...)
	 * @return boolean
	 */
	public static function validateNIF($nif,$country='ES'){
		
		$nif = strtoupper(str_replace(array(' ', '.', '-', ',', ', '), '', trim($nif)));
		
		switch (strtoupper($country)){
			case 'ES':
				
				if (self::_validateNif($nif)){
					return true;
				}
				
				if (self::_validateCif($nif)){
					return true;
				}
				
				break;
			default:
				//Si no conozco la validación del país concreto lo doy por bueno...
				return true;
		}
		
		return false;
	}
	
	/**
	 * Función auxiliar usada para CIFs y NIFs especiales
	 * @param string $cif
	 * @return int
	 * @link http://labs.viricmind.org/2011/07/30/validacion-de-nifs-nies-dnis-y-cifs-en-php/
	 */
	private static function _getCifSum($cif) {
		$sum = $cif[2] + $cif[4] + $cif[6];

		for ($i = 1; $i<8; $i += 2) {
			$tmp = (string) (2 * $cif[$i]);
			$tmp = $tmp[0] + ((strlen ($tmp) == 2) ?  $tmp[1] : 0);
			$sum += $tmp;
		}

		return $sum;
	}
	
	/**
	 * Valida CIFs
	 * @param string $cif
	 * @return boolean
	 * @link http://labs.viricmind.org/2011/07/30/validacion-de-nifs-nies-dnis-y-cifs-en-php/
	 */
	private static function _validateCif ($cif) {
		
		if (strlen(trim($cif))!=9) return false;
		
		$cif_codes = 'JABCDEFGHI';

		$sum = (string) self::_getCifSum($cif);
		$n = (10 - substr ($sum, -1)) % 10;

		if (preg_match ('/^[ABCDEFGHJNPQRSUVW]{1}/', $cif)) {
			if (in_array ($cif[0], array ('A', 'B', 'E', 'H'))) {
				// Numerico
				return ($cif[8] == $n);
			} elseif (in_array ($cif[0], array ('K', 'P', 'Q', 'S'))) {
				// Letras
				return ($cif[8] == $cif_codes[$n]);
			} else {
				// Alfanumérico
				if (is_numeric ($cif[8])) {
					return ($cif[8] == $n);
				} else {
					return ($cif[8] == $cif_codes[$n]);
				}
			}
		}

		return false;
	}
	
	/**
	 * Valida NIFs (DNIs y NIFs especiales)
	 * @param string $nif
	 * @return boolean
	 * @link http://labs.viricmind.org/2011/07/30/validacion-de-nifs-nies-dnis-y-cifs-en-php/
	 */
	private static function _validateNif ($nif) {
		$nif_codes = 'TRWAGMYFPDXBNJZSQVHLCKE';

		$sum = (string) self::_getCifSum($nif);
		$n = 10 - substr($sum, -1);

		if (preg_match ('/^[0-9]{8}[A-Z]{1}$/', $nif)) {
			// DNIs
			$num = substr($nif, 0, 8);
			return ($nif[8] == $nif_codes[$num % 23]);
		} elseif (preg_match ('/^[XYZ][0-9]{7}[A-Z]{1}$/', $nif)) {
			// NIEs normales
			$tmp = substr ($nif, 1, 7);
			$tmp = strtr(substr ($nif, 0, 1), 'XYZ', '012') . $tmp;

			return ($nif[8] == $nif_codes[$tmp % 23]);
		} elseif (preg_match ('/^[KLM]{1}/', $nif)) {
			// NIFs especiales
			return ($nif[8] == chr($n + 64));
		} elseif (preg_match ('/^[T]{1}[A-Z0-9]{8}$/', $nif)) {
			// NIE extraño
			return true;
		}

		return false;
	}
}

?>