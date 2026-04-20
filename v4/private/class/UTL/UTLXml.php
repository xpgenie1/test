<?php
/**
 * @author Victor J. Chamorro - victor@ipdea.com
 * @package UTL
 */
class UTLXml{
	/**
	 * Transformar las claves numericas de los arrays a strings (item_X)
	 * @var boolean
	 */
	static $array_keys_as_string=true;
	
	private static function recursiveEncode($xml,$data){
		
		foreach ($data as $clave=>$valor){
			
			if (self::$array_keys_as_string){
				$tag=(is_numeric($clave) ? 'item_' : ''). $clave;
			}else{
				$tag=(is_int($clave) ? 'item' : (is_numeric($clave) ? 'item_' : ''). $clave);
			}
			
			$xml->startElement($tag);

			if (is_array($valor)){
				self::recursiveEncode($xml,$valor);
			}else{
				$xml->text($valor);
			}				
			$xml->endElement();
		}
	}
	
	/**
	 * Devuelve un array asociativo multidimensinal en XML
	 * @param array $data
	 * @return string
	 */
	public static function encode($data,$encoding="iso-8859-1"){
		
		$xml= new XMLWriter();
		$xml->openMemory();
		$xml->startDocument("1.0",strtolower($encoding));
		$xml->startElement("data");			
		self::recursiveEncode($xml,$data);			
		$xml->endElement();		
		$xml->endDocument();
		return $xml->outputMemory();
		
	}
}

?>