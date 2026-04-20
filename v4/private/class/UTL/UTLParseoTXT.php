<?php

/**
 * Clase para el manejo de ficheros de texto en formato CSV o de ancho fijo
 * @author Victor J. Chamorro 
 *
 */
class UTLParseTXT{
	
	const FIN_LINEA_WINDOWS="\r\n";
	const FIN_LINEA_MAC="\r";
	const FIN_LINEA_UNIX="\n";
	
	/**
	 * @var string
	 */
	private $separador=",";
	
	/**
	 * @var string
	 */
	private $finLinea="\n";
	
	/**
	 * @var string
	 */
	private $entrecomillado='"';
	
	/**
	 * Configuracion de anchos:
	 * Array de
	 * 
	 * [nombreCampo]
	 * format=CHAR|NUM
	 * len=25
	 * dec=0
	 * desc=Descripcion
	 * 
	 * @var array
	 */
	private $anchosFijos=array();
	
	/**
	 * Datos del fichero en array de dos dimensiones, filas->columnas
	 * @var array
	 */
	private $data=array();
		
	/**
	 * Manejador de ficheros de datos en texto plano: CSV o de ancho fijo
	 * @param string $path
	 */
	public function __construct($path=""){
		if ($path!="") $this->loadFile($path);
	}
	
	/**
	 * Carga un fichero
	 * @param string $path
	 */
	public function loadFile($path){
		$real_path=UTLUtilidades::getFullPath($path);
		if (is_file($real_path)){
			
		}else{
			throw new TeException('No se ha podido cargar el fichero', __LINE__,__CLASS__);
		}
		
	}
	
	private function detectFormat(){
		
	}
	
	/**
	 * Separador de campos
	 * @param string $separador
	 */
	public function setSeparador($separador=","){
		$this->separador=$separador;
	}
	
	/**
	 * Carácter fin de línea
	 * @param string $finLinea
	 */
	public function setFinLinea($finLinea="\n"){
		$this->finLinea=$finLinea;
	}
	
	/**
	 * Campos encerrados entre el carácter pasado
	 * @param string $entrecomillado
	 */
	public function setEntrecomillado($entrecomillado='"'){
		$this->entrecomillado=$entrecomillado;
	}
	
	/**
	 * Anchos para los ficheros de campos con ancho fijo, pide un array simple con un int por campo
	 * @param array $anchos
	 */
	public function setAnchos($anchos){
		
		$this->anchosFijos=array_values($anchos);
	}
	
	/**
	 * Array de datos de 2 dimensiones, array de filas que contiene array de columnas
	 * @param array $data
	 */
	public function setData($data){
		if (!is_array($data)) throw new TeException("Se esperaba un array en ".__FUNCTION__, __LINE__,__CLASS__);
		
		$this->data=$data;
	}
	
	/**
	 * Escribe los datos en disco
	 * @param string $path
	 */
	public function writeFile($path){
		if(count($this->anchosFijos)>0){
			
			//Fichero por anchos fijos, se trabaja como fichero de texto normal
			$rs=fopen($path,'w');
			if ($rs==false) throw new TeException('No se ha podido abrir el fichero para escritura', __LINE__,__CLASS__);
			foreach($this->data as $fila=>$columnas){
				
				$fila="";
				foreach($columnas as $col=>$data){
						
						if (!isset(
									$this->anchosFijos[$col],
									$this->anchosFijos[$col]['len'],
									$this->anchosFijos[$col]['format'],
									$this->anchosFijos[$col]['dec']
						)){
							throw new TeException('Datos de anchos fijos incompletos, no puede generarse el fichero - '.$col, __LINE__,__CLASS__);
						}
						
						$len=$this->anchosFijos[$col]['len'];
						$tipo=$this->anchosFijos[$col]['format'];
						$decimales=$this->anchosFijos[$col]['dec'];
						
						if ($tipo=='NUM'|$tipo=='INT') $data=number_format((double)$data,$decimales,'.','');
						
						$fila.=	str_pad($data,$len,' ',($tipo=='NUM'|$tipo=='INT' ? STR_PAD_LEFT : STR_PAD_RIGHT));
					
				}
				
				fwrite($rs,	$fila.$this->finLinea);
			
			}
			fclose($rs);
			
		}else{
			
			//Fichero CSV
			$rs=fopen($path,'w');
			if ($rs==false) throw new TeException('No se ha podido abrir el fichero para escritura', __LINE__,__CLASS__);
			
			foreach($this->data as $fila=>$columnas){
				
				fwrite($rs,	$this->entrecomillado.
							implode($this->entrecomillado.$this->separador.$this->entrecomillado,$columnas).
							$this->entrecomillado.
							$this->finLinea);	
				
			}
			
			fclose($rs);
		}
	}
	
}

?>