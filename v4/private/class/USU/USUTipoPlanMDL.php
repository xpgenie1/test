<?php

/**
 * Objeto TipoPlan, encapsula la información de un tipo de plan, por ejemplo el plan gratuito
 * @package USU
 * @author Victor J Chamorro - victor@ipdea.com
 */
class USUTipoPlanMDL{
	
	const CATEGORIA_GRATUITO='gratuito';
	const CATEGORIA_LIMITADO='limitado';
	const CATEGORIA_ILIMITADO='ilimitado';
	const CATEGORIA_IDEM='idem';
	const CATEGORIA_BONO='bono';
	const CATEGORIA_ORIGINAL='original';
	const CATEGORIA_CREDITOS_SMS='creditos_sms';
	const CATEGORIA_DESARROLLO='desarrollo';
	const CATEGORIA_SOPORTE='soporte';
	const CATEGORIA_DISENO='disign';
	const CATEGORIA_DISIGN='disign';
	const CATEGORIA_OTROS='varios';
        
        const TIPOPLAN_GRATUITO_TEMPORAL=20; 
	
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $nombre;
	
	/**
	 * @var double
	 */
	private $precio;
	
	/**
	 * Numero de contactos distintos que puede enviar durante su ciclo. -1 indica ilimitado
	 * @var int
	 */
	private $contactos;
	
	/**
	 * Numero de envíos totales que puede realizar durante su ciclo. -1 indica ilimitado
	 * @var int
	 */
	private $envios;
	
	/**
	 * [gratuito|limitado|ilimitado|idem|bono|original|creditos_sms]
	 * @var type string
	 */
	private $tipo_categoria;
	
	/**
	 * Número de remitentes permitidos
	 * @var int
	 */
	private $remitentes;
	
	/**
	 * @var int
	 */
	private $borrado;
	
	/**
	 * Construye un objeto con los datos del tipo de plan pasándole el id_tipo_plan
	 * @param int $id_tipo_plan 
	 */
	public function __construct($id_tipo_plan=0) {
		if ($id_tipo_plan!=0){
			$this->loadFromDB($id_tipo_plan);
		}
	}
	
	/**
	 * Reyena el objeto con los datos del tipo de plan pasándole el id_tipo_plan
	 * @param int $id_tipo_plan 
	 * @throws TeExcepcion
	 */
	public function loadFromDB($id_tipo_plan){
		$BDBase=new BDBase();
		$codificacion=$BDBase->BD1->getCharset();
		if ($codificacion!='utf8'){
			$BDBase->BD1->setCharset('utf8');
		}
		$objTable=$BDBase->BD1->SelectTabla('planes', array('id', 'nombre', 'precio', 'contactos', 'envios','borrado','categoria','remitentes'), array('id'=>$id_tipo_plan));
		
		if ($objTable==false || $objTable->length==0) throw new TeException ('Error al sacar los datos del tipo de plan '.$id_tipo_plan, __LINE__,__CLASS__);
		
		$this->id=   (int) $id_tipo_plan;
		$this->contactos = $objTable->ItemCol(0, 'contactos');
		$this->envios    = $objTable->ItemCol(0, 'envios');
		$this->nombre    = $objTable->ItemCol(0, 'nombre');
		$this->precio    = $objTable->ItemCol(0, 'precio');
		$this->borrado   = $objTable->ItemCol(0, 'borrado');
		$this->remitentes= $objTable->ItemCol(0, 'remitentes');
		$this->tipo_categoria= strtolower($objTable->ItemCol(0, 'categoria'));
		if ($codificacion!='utf8'){
			$BDBase->BD1->setCharset($codificacion);
		}
		
	}
	
	/**
	 * Id del tipo de plan
	 * @return int
	 */
	public function getId(){
		return (int) $this->id;
	}
	
	/**
	 * Nombre del tipo de plan
	 * @return string
	 */
	public function getNombre(){
		return $this->nombre;
	}
	
	/**
	 * Precio sin impuestos del tipo de plan
	 * @return double
	 */
	public function getPrecio(){
		return $this->precio;
	}
	
	/**
	 * Número de contactos distintos que puede enviar durante su ciclo. -1 indica ilimitado
	 * @return int
	 */
	public function getContactos(){
		return $this->contactos;
	}
	
	/**
	 * Número de envíos totales que puede realizar durante su ciclo. -1 indica ilimitado
	 * @return int
	 */
	public function getEnvios(){
		return $this->envios;
	}
	
	/**
	 * [gratuito|limitado|ilimitado|idem|bono|original|creditos_sms|desarrollo|soporte|disign]
	 * @return string 
	 */
	public function getTipoCategoria(){
		return $this->tipo_categoria;
	}
	
	/**
	 * Meses de duración de los ciclos por defecto
	 * 1 mes por defecto excepto 12 meses en bonos
	 * @return int
	 */
	public function getPlazo(){
		if ($this->getTipoCategoria()=='bono'){
			return 12;
		}else{
			return 1;
		}
	}
	
	/**
	 * Número de remitentes permitidos
	 * @return int
	 */
	public function getRemitentes(){
		return (int) $this->remitentes;
	}
	
	/**
	 * @return int
	 */
	public function getBorrado(){
		return $this->borrado;
	}
}

?>