<?php

require_once 'v4/private/class/AVI/AVIAvisoNuevoUsuario.php';
require_once 'v4/private/class/USU/USUUsuarioMDL.php';
require_once 'v4/private/class/USU/USUPlanMDL.php';

class USUUsuarios{
	
	const MAX_USUARIOS=500;
	const KEY_NUEVO_USER = '$_9614_';
	
	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * @param string $plan
	 * @throws TeException
	 */
	public function __construct($plan) {
		if (empty($plan)){
			throw new TeException('No se ha pasado el plan al constructor',__LINE__,__CLASS__);
		}
		$this->plan=$plan;
	}
	
	/**
	 * Elimina los usuarios pasados (borrado=1)
	 * @param array $ids
	 * @throws TeException
	 */
	public function eliminaUsuarios($ids){
	
		if (!is_array($ids)) throw new TeException('No ha llegado un array de ids a eliminaUsuarios', __LINE__,__CLASS__);
	
		$BDBase= new BDBase($this->plan);
		$objGrupos=new USUGrupos($this->plan);
		
		foreach($ids as $id){
			
			$objUsuario=new USUUsuarioMDL(null, $this->plan, $id);
			if ($objUsuario->isAdmin()){
				//Revisar que exista al menos otro administrador
				$administradores=new USUGrupoMDL($this->plan,1);
				if (count($administradores->getUsuarios()) < 2){
					throw new TeException('No puede eliminarse el unico usuario administrador que existe - '.$id,403,'PERPermisos');
				}
			}
			
			//Eliminamos el usuario de los grupos
			$grupos=array_keys($objUsuario->getGrupos());
			
			foreach($grupos as $id_grupo){
				$objGrupos->delUser($id, $id_grupo);
			}
				
			//Eliminamos el usuario
			
			$SQL="UPDATE usuarios SET borrado=1, fecha_mod=date(now()) WHERE id = '$id'";
			$rs=$BDBase->BD2->query($SQL);

			if ($rs==false) throw new TeException('Error al eliminar el usuarios '.$id.' - '.$BDBase->BD2->ultimo_error, __LINE__,__CLASS__);

			UTLLog::guardaLog('USUUsuarios_'.$this->plan, 'ELIMINA', GBLSession::getUsuario().' - Eliminados el usuario '.$id.' '.$objUsuario->getUser());
		}
	}
        
        /**
	 * Comprueba si ya existe un usuario con el nombre pasado
	 * @param int id
         * @param string $name
	 */
	public function checkUserName($name){
	
		if (empty($name)) throw new TeException('No ha llegado el nombre de usuario a checkUserName', __LINE__,__CLASS__);
	
		$BDBase= new BDBase($this->plan);
		
                $SQL='SELECT * FROM usuarios WHERE user="'.$name.'" and borrado!=1';
                $rs = $BDBase->BD2->query($SQL);
                if ($rs!=false && $rs->num_rows==0){
                        return true;
                }else{
                        return false;
                }
	}
	
	
	/**
	 * Devuelve un array asociativo NombreCampo=>Alias con los alias globales y del plan (auxiliares)
	 * @return array
	 */
	public function getCampos(){
		
		$BDBase= new BDBase($this->plan);
		//Los datos que nos ocupan están en Latin1 en mysql, hay que jugar con los charsets
		$charsetBD1=strtolower($BDBase->BD1->getCharset());
		$charsetBD2=strtolower($BDBase->BD2->getCharset());
		
		if ($charsetBD1!='utf8'){
			$BDBase->BD1->setCharset('utf8');
		}
		if ($charsetBD2!='utf8'){
			$BDBase->BD2->setCharset('utf8');
		}
	
		$tGlobalAlias = $BDBase->BD1->SelectTabla('alias_campos', array('campo','alias'), array('borrado'=>0),'','ncampo ASC');
		
		$alias=array();
		foreach($tGlobalAlias->Table as $n=>$fila){
			if (isset($pAlias[$fila[0]])){
				$alias[$fila[0]]=$pAlias[$fila[0]];
			}else{
				$alias[$fila[0]]=$fila[1];
			}
		}
	
		//Los datos que nos ocupan están en Latin1 en mysql, hay que jugar con los charsets, los dejamos como antes
		if ($charsetBD1!='utf8'){
			$BDBase->BD1->setCharset($charsetBD1);
		}
		if ($charsetBD2!='utf8'){
			$BDBase->BD2->setCharset($charsetBD1);
		}
	
		$alias['telefono']=$alias['telperso'];
		$alias['movil']=$alias['movilperso'];
		unset($alias['telperso'],$alias['movilperso']);
		
		return $alias;
	}
	
	
	/**
	 * Envía email de validación a un usuario recien creado o si se quiere reenviar el correo de validación
	 * @param int $id_usuario
	 */
	public function enviaValidacionUser($id_usuario){
	
		if (empty($id_usuario)) throw new TeException('No se ha pasado el id usuario', __LINE__, __CLASS__);
		
		$objAviso = new AVIAvisoNuevoUsuario($this->plan,$id_usuario);
		$objAviso->send();
	
	}
	
	/**
	 * activa un usuario creado
	 * @param int $id 
	 */
	public function activacionUsuario($id){

		if (empty($id)) throw new TeException('No se ha pasado el id usuario', __LINE__, __CLASS__);
		
		$objUsuarioMdl = new USUUsuarioMDL(null, $this->plan,$id);
		$objUsuarioMdl->setEstado($objUsuarioMdl::USUARIO_VALIDADO);
		$objUsuarioMdl->saveToDB();
	
	}
	
	
	/**
	 *  Sube una imagen para la cabecera
	 * @param string $campo
	 * @param int $id
	 * @throws TeException
	 */
	
	public function uploadImg($campo){
	
		if (!isset($_FILES[$campo]['tmp_name'])) return '';
	
		if(!is_uploaded_file($_FILES[$campo]['tmp_name'])){
			return '';
			//throw new TeException('El fichero no parece un fichero subido autorizado o ha excedido el tamaño máximo.', __LINE__,__CLASS__);
		}
	
		$path= USUPlanMDL::getInstance($this->plan,true)->getFilesPath();
		
		if (!is_dir($path.'/cabeceras')) mkdir($path.'/cabeceras');
		
		if (!is_dir($path.'/cabeceras')){
			throw new TeException('No se ha podido generar la carpeta para la imagen de cabecera', __LINE__,__CLASS__);
		}
	
		//Máximo 500Kb
		if (filesize($_FILES[$campo]['tmp_name'])>512*1024){
			throw new TeException('El fichero subido excede de los 500Kb', __LINE__,__CLASS__);
		}
		
		//Solo imgs
		
		if (substr($_FILES[$campo]['type'], 0, 6)!='image/'){
			throw new TeException('El fichero subido no es una imagen', __LINE__,__CLASS__);
		}
	
		$name = time()."_".$_FILES[$campo]['name'];
		if (!copy($_FILES[$campo]['tmp_name'], $path.'/cabeceras/'.$name)){
			throw new TeException('No se ha podido copiar el fichero subido', __LINE__,__CLASS__);
		}else{
			return $name;
		}
	
	}
	
	/**
	 * Inicializa la tabla usuarios_grupos en caso de no existir, creando la tabla y cargando los grupos del sistema
	 * @param boolean $demo
	 */
	public function checkUsersGroupsTable($demo=false){
		
		$BDBase=new BDBase($this->plan);
		
		$rs=$BDBase->BD2->query("show tables like 'usuarios_grupos'");
		if ($rs && $rs->num_rows==0){
			
			//1- Se crea la tabla
			$rs=$BDBase->BD1->query('SHOW CREATE TABLE `usuarios_grupos`');
			if ($rs && $rs->num_rows==1){
				$SQL_CREATE=$rs->fetch_row()[1];
				if ($demo){
					echo $SQL_CREATE."\n";
				}else{
					if ($BDBase->BD2->query($SQL_CREATE)==false){
						throw new TeException('Error al inicializar la BBDD para grupos de contactos',__LINE__,__CLASS__);
					}
				}
			}else{
				throw new TeException('Error al inicializar la BBDD para grupos de contactos, no se encuentra la tabla origen para su copiado.',__LINE__,__CLASS__);
			}
			
			//2- Se inicializa con los grupos de usuarios del sistema
			$rs=$BDBase->BD2->query('SELECT count(*) FROM usuarios_grupos');
			if ($rs && $rs->num_rows==1){
				$num=$rs->fetch_row()[0];
				if ($num==0){
					//no hay grupos, me los traigo de teenvio_system
					$SQL='INSERT INTO `usuarios_grupos` SELECT * from teenvio_system.`usuarios_grupos` WHERE id < 100';
					if ($demo){
						echo $SQL.";\n";
					}else{
						$BDBase->BD2->query($SQL);
					}
				}
			}
			
			//3- Se agrega el primer usuario como administrador y el resto como usuarios simples
			
			$objGrupos=new USUGrupos($this->plan);
			
			$tUsuarios=$BDBase->BD2->SelectTabla('usuarios', array('id'), array('borrado'=>0),'','id ASC');
			
			for($i=0;$i<count($tUsuarios->Table);$i++){
				
				$id_usuario=$tUsuarios->Table[$i][0];
				
				if ($i==0){
					//Administrador
					if ($demo){
						echo "/* ADD USER $id_usuario TO GROUP 1 */\n";
					}else{
						$objGrupos->addUser($id_usuario, 1);
					}
				}else{
					//Usuario simple
					
					if ($demo){
						echo "/* ADD USER $id_usuario TO DEFAULTS GROUPS */\n";
					}else{
						$grupos=$objGrupos->getGrupos();
						foreach($grupos as $rowGrupo){
							//Asociamos a todos los grupos del sistema menos el 1 y el 3
							if ($rowGrupo['id']<100 && in_array($rowGrupo['id'],array(1,3))===false){
								$objGrupos->addUser($id_usuario, $rowGrupo['id']);
							}
						}
					}
				}
			}
		}
		
		
		
	}
}
?>