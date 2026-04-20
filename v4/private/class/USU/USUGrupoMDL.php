<?php
require_once 'v4/private/class/BDB/BDBase.php';
require_once 'v4/private/class/USU/USUGrupos.php';

class USUGrupoMDL{
	
	/**
	 * @var string
	 */
	const TYPE_SYSTEM="system";
	
	/**
	 * @var string
	 */
	const TYPE_CUSTOM="custom";
	
	/**
	 * @var string
	 */
	private $plan="";
	
	/**
	 * @var int
	 */
	private $id=0;
	
	/**
	 * @var int
	 */
	private $borrado=0;
	
	/**
	 * @var string
	 */
	private $nombre="";
	
	/**
	 * @var string
	 */
	private $descripcion="";
	
	/**
	 * @var string
	 */
	private $tipo="system";
	
	/**
	 * Ids de usuarios asociados al grupo
	 * @var array
	 */
	private $usuarios=array();
	
	/**
	 * @var BDBase
	 */
	private $BDBase=null;
	/**
	 * Objeto Grupo de Usuarios
	 * @param string $plan
	 * @param int $id
	 */
	public function __construct($plan,$id=0) {
		
		$this->plan=$plan;
		$this->id=(int) $id;
		
		$this->BDBase= new BDBase($this->plan);
		
		if ($this->id!==0){
			$this->loadFromDB();
		}
	}
	
	private function loadFromDB(){
		
		$gTable=$this->BDBase->BD2->SelectTabla('usuarios_grupos', array('id','nombre','descripcion',"IF(id<100,'system','custom') as tipo,borrado,usuarios"), array('id'=>$this->id,'borrado'=>0));
		
		if ($gTable!==false && $gTable->length==1){
			
			$this->nombre =    $gTable->ItemCol(0, 'nombre');
			$this->tipo =      $gTable->ItemCol(0, 'tipo');
			$this->borrado=    $gTable->ItemCol(0, 'borrado');
			$this->descripcion=$gTable->ItemCol(0, 'descripcion');
			$this->usuarios=   trim($gTable->ItemCol(0, 'usuarios'))==="" ? array() : explode(',',$gTable->ItemCol(0, 'usuarios'));
			array_walk($this->usuarios, function(&$elemento){
				$elemento=(int) $elemento;
			});
			
		}else{
			throw new TeException('No se puede cargar el grupo de usuarios '.$this->plan.'-'.$this->id,__LINE__,__CLASS__);
		}
		
	}
	
	/**
	 * Guarda en BBDD y devuelve el id insertado o modificado
	 * @return int
	 * @throws TeException
	 */
	public function saveToDB(){
		
		if ( ($this->id!==0 && $this->id < 100)  || $this->tipo==self::TYPE_SYSTEM){
			throw new TeException('Se ha intentado modificar un grupo de usuarios del sistema: '.$this->plan.'-'.$this->id,__LINE__,__CLASS__);
		}
		
		$data=array(
		    'nombre'=>$this->nombre,
		    'descripcion'=>$this->descripcion,
		    'borrado'=>$this->borrado
		);
		
		if ($this->id===0){
			$ok=$this->BDBase->BD2->InsertTabla('usuarios_grupos', null,null, $data);
			if ($ok==false){
				throw new TeException('Ha fallado al intentar insertar el grupo de contactos',__LINE__,__CLASS__);
			}
			$this->id=$this->BDBase->BD2->insert_id();
		}else{
			$ok=$this->BDBase->BD2->UpdateTabla('usuarios_grupos','', $data, array('id'=>$this->id));
			if ($ok==false){
				throw new TeException('Ha fallado al intentar actualizar el grupo de contactos',__LINE__,__CLASS__);
			}
		}
		
		return $this->id;
	}
	
	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getNombre(){
		return $this->nombre;
	}
	
	/**
	 * @return string
	 */
	public function getTipo(){
		return $this->tipo;
	}
	
	/**
	 * @return string
	 */	
	public function getDescripcion(){
		return USUGrupos::getDescripcionInLocale($this->descripcion);
	}
	
	/**
	 * @return int
	 */
	public function getBorrado(){
		return $this->borrado;
	}
	
	/**
	 * @param int $id
	 */
	public function setId($id){
		$this->id=(int)$id;
	}
	
	/**
	 * @param string $nombre
	 */
	public function setNombre($nombre){
		$this->nombre=$nombre;
	}
	
	/**
	 * @param string $descripcion
	 */
	public function setDescripcion($descripcion){
		$this->descripcion=$descripcion;
	}
	
	/**
	 * @param string $tipo
	 */
	public function setTipo($tipo){
		switch ($tipo){
			case self::TYPE_CUSTOM:
			case self::TYPE_SYSTEM:
				$this->tipo=$tipo;
				break;
			default:
				throw new TeException('El tipo \''.$tipo.'\'pasado no es valido',__LINE__,__CLASS__);
		}
	}
	
	/**
	 * @param int $borrado
	 */
	public function setBorrado($borrado){
		if ($borrado!==0 && count($this->usuarios)>0){
			throw new TeException('Error al eliminar el grupo de usuarios: Tiene asignados usuarios',101,__CLASS__);
		}
		
		$this->borrado=$borrado;
	}
	
	/**
	 * Devuelve los ids de los usuarios que pertenecen al grupo
	 * @return array
	 */
	public function getUsuarios(){
		return $this->usuarios;
	}
	
}
?>