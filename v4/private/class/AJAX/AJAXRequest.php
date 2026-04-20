<?php


interface AJAXRequest{
	
	/**
	* Método que sera llamado desde el modulo de ajax
	* @param array $dataGET
	* @param array $dataPOST
	* @return string
	*/
	public function runAjax($dataGET,$dataPOST);
	
}


?>