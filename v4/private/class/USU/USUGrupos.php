<?php
require_once 'v4/private/class/BDB/BDBase.php';
require_once 'v4/private/class/LANG/LANGBase.php';
require_once 'v4/private/class/UTL/UTLLog.php';

class USUGrupos{
	
	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * @param sting $plan
	 */
	public function __construct($plan) {
		$this->plan=$plan;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getGrupos(){
		
		$bdBase=new BDBase($this->plan);
		
		$tGrupos=$bdBase->BD2->SelectTabla('usuarios_grupos', array('id','nombre','descripcion','usuarios'), array('borrado'=>0),'','id asc');
		
		if ($tGrupos===false){
			throw new TeException('Fallo al obtener los grupos de contactos: '.$bdBase->BD2->error(),__LINE__,__CLASS__);
		}
		
		return $tGrupos->TableCol;
		
	}
	
	/**
	 * Devuelve un select html con los grupos de usuarios
	 * @param string $name
	 * @param string $selected
	 * @param int $user
	 * @param array $options
	 * @param bollean $seleccione
	 * @return type
	 */
	public function getDDLGrupos($name,$selected=2,$user=null,$options=null,$seleccione=true){
		$bdBase=new BDBase($this->plan);
		
		$WHERE='';
		if ($user!=null){
			$WHERE = "AND find_in_set('$user',usuarios)<>0";
		}
		
		return $bdBase->BD2->CreaDDL($name, 'SELECT id,nombre FROM usuarios_grupos WHERE borrado=0 '.$WHERE.' ORDER By ID ',true,$selected,'','',$options,$seleccione);
	}
	
	/**
	 * Agrega un usuario a un grupo de usuarios
	 * @param int $id_usuario
	 * @param int $id_grupo
	 * @return boolean
	 */
	public function addUser($id_usuario,$id_grupo){
		$bdBase=new BDBase($this->plan);
		
		$SQL="	UPDATE usuarios_grupos SET usuarios = TRIM(BOTH ',' FROM CONCAT(usuarios,',$id_usuario'))
			WHERE id='$id_grupo' and find_in_set('$id_usuario',usuarios)=0";
		
		if ($bdBase->BD2->query($SQL)==true){
			UTLLog::guardaLog('USUGrupos_'.$this->plan, 'AddUser', GBLSession::getUsuario().' Agregado usuario '.$id_usuario.' al grupo '.$id_grupo);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Elimina un usuario de un grupo de usuarios
	 * @param int $id_usuario
	 * @param int $id_grupo
	 * @return boolean
	 */
	public function delUser($id_usuario,$id_grupo){
		$bdBase=new BDBase($this->plan);
		
		$SQL="	UPDATE usuarios_grupos SET usuarios = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', usuarios, ','), ',$id_usuario,', ','))
			WHERE id='$id_grupo' and find_in_set('$id_usuario',usuarios)>0";
		
		if ($bdBase->BD2->query($SQL)==true){
			UTLLog::guardaLog('USUGrupos_'.$this->plan, 'DelUser', GBLSession::getUsuario().' Eliminado usuario '.$id_usuario.' del grupo '.$id_grupo);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Devuelve el texto correspondiente al locale actual, el origen debe tener un string por linea:
	 * 
	 * @param string $data
	 * @return type
	 */
	public static function getDescripcionInLocale($data){
		return LANGBase::getDescripcionInLocale($data);
	}
}

?>