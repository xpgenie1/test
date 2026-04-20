<?php

/**
 * Case para utilidades de ajax con métodos estáticos
 * @package AJAX
 * @class AJAXUtils
 */

class AJAXUtils{

	/**
	 * Envia una cabecera text/plain UTF8 y retorna un string codificado en JSON
	 * @param mixed $obj
	 * @param bool $reencoding Indica si ha de transformarse previamente a UTF-8
	 * @return string JSON
	 */
	static public function print_json_encode($obj,$reencoding=true){
		
		if (!headers_sent()) header('Content-Type:text/plain; charset=UTF-8');
		if ($reencoding && is_array($obj)){
			echo json_encode(AJAXUtils::utf8_encode_r($obj));
		}else{
			echo json_encode($obj);
		}

	}


	/**
	 * Retorna un array con todos sus elementos codificados en UTF-8
	 * @param array $array
	 * @return array
	 */
	static function utf8_encode_r($array){
		
		if (is_array($array)){
			$result_array = array();

			foreach($array as $key => $value){
				if (is_array($value)){
					// recursión
					$result_array[utf8_encode($key)] = AJAXUtils::utf8_encode_r($value);
				}else{
					// Codificamos
					$result_array[utf8_encode($key)] = utf8_encode($value);
				}
			}
			return $result_array;
		
		}

		return false;     //no es un array
	}
}

?>