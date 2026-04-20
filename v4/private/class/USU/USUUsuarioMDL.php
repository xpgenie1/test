<?php
require_once 'v4/private/class/BDB/BDBase.php';
require_once 'v4/private/class/BDB/BDBUtilidades.php';
require_once 'v4/private/class/USU/USUPlanMDL.php';
require_once 'v4/private/class/USU/USUGrupos.php';
require_once 'v4/private/class/PER/PERPermisos.php';
require_once 'v4/private/class/PER/PERPermisosMDL.php';
require_once 'v4/private/class/GBL/GBLSession.php';
require_once 'v4/private/class/HOME/LOGGIN/HOMELogginOAuthFactory.php';
require_once 'v4/private/class/HOME/LOGGIN/HOMELoggin.php';

/**
 * Encapsulado de datos de un usuario de teenvio
 * @package USU
 * @author Víctor J. Chamorro <victor@ipdea.com>
 *
 */
class USUUsuarioMDL{
	
	/**
	 * @var String
	 */
	const masterKey='$2y$10$dxZNM0t87u2L3fKV5ZHh9e7yDQkTmN3FXiHFkUdeUVLa/F3iLfhkC';
	
	public static $objUsuario="";
	private static $singleton=null;
	
	const USUARIO_SIN_VALIDAR=3;
	const USUARIO_BORRADO=1;
	const USUARIO_VALIDADO=0;
		
	private $user='';
	private $pass='';
	private $passMD5='';
	private $plan='';
	private $email='';
	private $id=0;
	private $nombre='';
	private $apelidos='';
	
	private $Observaciones;
	
	//Empresa
	private $Empresa;
	private $EmpDireccion;
	private $EmpCP;
	private $EmpCiudad;
	private $EmpProvincia;
	private $EmpPais;
	private $EmpTel;
	private $EmpFax;
	private $EmpTelMovil;
	
	//Personales
	private $PersonalesDireccion;
	private $PersonalesCP;
	private $PersonalesCiudad;
	private $PersonalesProvincia;
	private $PersonalesPais;
	private $PersonalesTel;
	private $PersonalesTelMovil;
	private $PersonalesNacimiento;
	
	//Redes sociales
	private $oauts=null;
	
	private $estado;
	
	/**
	* @var UTLDateTime
	*/
	private $fechaAlta=null;
	
	/**
	 * @var UTLDateTime
	 */
	private $fechaMod=null;
	
	/**
	 * Objeto Plan del usuario
	 * @var USUPlanMDL
	 */
	public $objPlan;
	
	/**
	 * Objeto de permisos del usuario
	 * @var USUPermisos
	 */
	public $objPermisos;
	
	/**
	 * Array con los grupos a los que pertenece el usuario
	 * @var array
	 */
	public $grupos=null;
	
	/**
	 * @var UTLDateTime
	 */
	private $fecha_caducidad_password=null;
	
	/**
	 * @var boolean
	 */
	private $aviso_mail_en_login=false;
	
	/**
	 * @var boolean
	 */
	private $metadatos_modificados=false;
	
	/**
	 * @var boolean
	 */
	private $metadatos_sin_inicializar=false;
	
	/**
	 * @var array
	 */
	private $permisos_por_defecto=null;
	
	/**
	 * @var boolean
	 */
	private $doble_factor_autenticacion_email=false;
	
	/**
	 * @var boolean
	 */
	private $doble_factor_autenticacion_sms=false;
	
	/**
	 * Red desde la cual estará permitido el acceso, en blanco se permite el acceso desde cualquier origen
	 * @var string
	 */
	private $red_permitida='';
	
	/**
	 * Red desde la cual no se pedirá doble factor de autenticación aunque estuviese activo
	 * @var string
	 */
	private $red_segura='';
		
	/**
	 * Constructor, en caso de error por datos incorrectos devolverá false
	 * Se puede pasar el $user a null si se pasa un $id
	 * @param string $user
	 * @param string $plan
	 * @param int $id
	 */
	public function __construct($user="",$plan="",$id=0){
				
		$this->user=$user;
		$this->plan=$plan;
		$this->id=(int)$id;
			
		if ($this->plan!='' && ($this->id!=0 || $this->user!='')){
			if (!$this->loadFromDB()) throw new TeException("No es posible instanciar el usuario, probablemente no exista: {$user}.{$plan}", 1,__CLASS__);
		}
	}
	
	/**
	 * Devuelve una instancia del usuario en modo singleton
	 * @param type $user
	 * @param type $plan
	 * @param type $id
	 * @return USUUsuarioMDL
	 */
	public static function getInstance($user="",$plan="",$id=0){
		
		if (	is_array(self::$singleton) && 
			isset(self::$singleton[$user.'_'.$plan.'_'.$id]) &&
			self::$singleton[$user.'_'.$plan.'_'.$id] instanceof self){
			
			return self::$singleton[$user.'_'.$plan.'_'.$id];
		}
		
		if (!is_array(self::$singleton)) self::$singleton=array();
		
		self::$singleton[$user.'_'.$plan.'_'.$id] = new self($user,$plan,$id);
		
		return self::$singleton[$user.'_'.$plan.'_'.$id];
	}
	
	/**
	 * Obtiene una instancia única del objeto logueado actualmente
	 * @return USUUsuarioMDL
	 */
	public static function getUsuarioActivo($user="",$plan=""){
		
		if (USUUsuarioMDL::$objUsuario == "" && $user=='' && $plan=='' && GBLSession::isLogin()==false){
			return null;
		}
		
		if (USUUsuarioMDL::$objUsuario == ""){
			if (($user=="" || $plan=="") && GBLSession::isLogin()){
				$user=GBLSession::getUsuario();
				$plan=GBLSession::getPlan();
			}
			$obj=new USUUsuarioMDL($user,$plan);
		 	$obj->setUsuarioActivo();
		}
		return USUUsuarioMDL::$objUsuario;
	}
	
	/**
	 * Setea el usuario instanciado actualmente como usuario activo en la variable estática $objUsuario de USUUsuarioMDL
	 * Para tabajar con una única instancia durante toda la ejecución
	 */
	public function setUsuarioActivo(){
		USUUsuarioMDL::$objUsuario=$this;
	}
	
	private function loadFromDB(){
		try{
			$BDBase = new BDBase($this->plan);
			$BDBase->BD2->setCharset('latin1');
			
			//Compruebo si existe el plan y está activo
			$objplan=USUPlanMDL::getInstance($this->plan);
			
			if ($objplan!=false){
				//Ahora buscamos ese usuario
				$where=array('user'=>$this->user,'borrado'=>0);
				if ($this->id>0) $where=array('id'=>$this->id);
				
				$usuario=$BDBase->BD2->SelectTabla("usuarios",array('*'),$where);
				if ($usuario->length==1){
					$this->user=$usuario->ItemCol(0,'user');
					$this->id=(int)$usuario->ItemCol(0,'id');
					$this->pass=$usuario->ItemCol(0,'pass');
					$this->passMD5 = $usuario->ItemCol(0,'password');
					$this->email = $usuario->ItemCol(0,'email');
					$this->objPlan= $objplan;
					$this->nombre = $usuario->ItemCol(0,'nombre');
					$this->apelidos = $usuario->ItemCol(0, 'apellidos');
					$this->Observaciones=$usuario->ItemCol(0, 'observaciones');
			
					//Datos de empresa
					$this->Empresa=$usuario->ItemCol(0, 'empresa');
					$this->EmpDireccion=$usuario->ItemCol(0, 'edireccion');
					$this->EmpCP=$usuario->ItemCol(0, 'ecp');
					$this->EmpCiudad=$usuario->ItemCol(0, 'eciudad');
					$this->EmpProvincia=$usuario->ItemCol(0, 'eprovincia');
					$this->EmpPais=$usuario->ItemCol(0, 'epais');
					$this->EmpTel=$usuario->ItemCol(0, 'etelefono');
					$this->EmpFax=$usuario->ItemCol(0, 'efax');
					$this->EmpTelMovil=$usuario->ItemCol(0, 'emovil');
					
					//Datos personales
					$this->PersonalesDireccion=$usuario->ItemCol(0, 'direccion');
					$this->PersonalesCP=$usuario->ItemCol(0, 'cpostal');
					$this->PersonalesCiudad=$usuario->ItemCol(0, 'ciudad');
					$this->PersonalesProvincia=$usuario->ItemCol(0, 'provincia');
					$this->PersonalesTel=$usuario->ItemCol(0, 'telefono');
					$this->PersonalesTelMovil=$usuario->ItemCol(0, 'movil');
					$this->PersonalesNacimiento=$usuario->ItemCol(0, 'cumple');
						
					//Metadatos
					$this->fechaAlta=new UTLDateTime($usuario->ItemCol(0, 'fecha_add'));
					$this->fechaMod= new UTLDateTime($usuario->ItemCol(0, 'fecha_mod'));
					$this->estado=$usuario->ItemCol(0, 'borrado');
					
					$row_metadata=$usuario->ItemCol(0, 'metadata');
					if ($row_metadata!==null){
						$json= json_decode($row_metadata,true);
						if ($json){
							$this->metadatos_sin_inicializar=false;
							if (isset($json['fecha_caducidad_password'])){
								$this->fecha_caducidad_password= new UTLDateTime($json['fecha_caducidad_password']);
							}
							
							if (isset($json['aviso_mail_en_login'])){
								$this->aviso_mail_en_login=(boolean) $json['aviso_mail_en_login'];
							}
							
							if (isset($json['permisos_por_defecto'])){
								$this->permisos_por_defecto=$json['permisos_por_defecto'];
							}
							
							if (isset($json['doble_factor_autenticacion_email'])){
								$this->doble_factor_autenticacion_email=(boolean) $json['doble_factor_autenticacion_email'];
							}
							
							if (isset($json['doble_factor_autenticacion_sms'])){
								$this->doble_factor_autenticacion_sms=(boolean) $json['doble_factor_autenticacion_sms'];
							}
							
							if (isset($json['red_segura'])){
								$this->red_segura=$json['red_segura'];
							}
							
							if (isset($json['red_permitida'])){
								$this->red_permitida= $json['red_permitida'];
							}
						}
					}else{
						$this->metadatos_sin_inicializar=true;
					}
					
					return true;
				}
			}
		}catch(TeException $e){ }

		return false;
	}
	
	/**
	 * Guarda los datos a la BBDD
	 * @return int Id actualizado o insertado
	 */
	public function saveToDB(){
		$bdBase = new BDBase($this->plan);
		$bdBase->BD2->setExceptions(false);
		$bdBase->BD2->silencio=true;
	
		$data=$this->toDBArray();
		$data['fecha_mod']='now()';
	
		if (is_null($this->id)|| $this->id===0){
			$data['fecha_add']='now()';
			$data['borrado'] = self::USUARIO_SIN_VALIDAR;
			$this->estado =	self::USUARIO_SIN_VALIDAR;
	
			$ok = $this->compruebaExisteUser();
			if (!$ok) throw new TeException (LANGBase::__('Error al guardar el usuario, ya existe un usuario con ese nombre de usuario').$bdBase->BD2->ultimo_error, $bdBase->BD2->ultimo_cod_error,__CLASS__);
			$ok = $this->compruebaExisteEmail();
			if (!$ok) throw new TeException (LANGBase::__('Error al guardar el usuario, ya existe un usuario con este email ').$bdBase->BD2->ultimo_error, $bdBase->BD2->ultimo_cod_error,__CLASS__);
			
			$ok=$bdBase->BD2->InsertTabla('usuarios', null, null, $data);
			if (!$ok) throw new TeException (LANGBase::__('Error al guardar el usuario. ').$bdBase->BD2->ultimo_error, $bdBase->BD2->ultimo_cod_error,__CLASS__);
			$this->setId($bdBase->BD2->insert_id());
			
			$objUsuario = new USUUsuarios($this->plan);
			$objUsuario->enviaValidacionUser($this->getId());
			
			if ($this->metadatos_modificados){
				//Cargo el objeto completo en bbdd para que incluya el campo metadata (si no existía lo controlará en el load)
				$this->loadFromDB();
				$this->saveToDB();
			}
			
		}else{
                        if ($data['password']=='') unset($data['password']);
			
			//Agregamos nueva columna si es necesario
			if ($this->metadatos_sin_inicializar && $this->metadatos_modificados){
				$SQL= "ALTER TABLE `usuarios` ADD `metadata` TEXT NOT NULL DEFAULT '' AFTER `per_config`;";
				$ok=$bdBase->BD2->query($SQL);
				if ($ok===false){
					throw new TeException('Error al guardar el usuario: no se ha podido agregar estructura metadata. '.$bdBase->BD2->ultimo_error,__LINE__,__CLASS__);
				}
			}
			
			if ($this->metadatos_modificados){
				$data['metadata']=json_encode(array(
				    'fecha_caducidad_password'=>(is_null($this->fecha_caducidad_password) ? null : $this->fecha_caducidad_password->getDateTimeBD()),
				    'aviso_mail_en_login'=>$this->aviso_mail_en_login,
				    'permisos_por_defecto'=>$this->permisos_por_defecto,
				    'doble_factor_autenticacion_email'=>$this->doble_factor_autenticacion_email,
				    'doble_factor_autenticacion_sms'=>$this->doble_factor_autenticacion_sms,
				    'red_permitida'=>$this->red_permitida,
				    'red_segura'=>$this->red_segura
				));
			}
			
                        $ok=$bdBase->BD2->UpdateTabla('usuarios', '', $data, array('id'=>$this->id));
			if (!$ok) throw new TeException (LANGBase::__('Error al guardar el usuario. ').$bdBase->BD2->ultimo_error, $bdBase->BD2->ultimo_cod_error,__CLASS__);
		}
		
		return $this->id;
	}
	
	/**
	 * Devuelve un array con todos los datos del MDL, cuyas claves coinciden los los campos en BBDD tabla contactos
	 * @return array
	 */
	public function toDBArray(){
		$array=array();
	
		$array['id']=(int)$this->getId();
		$array['user']=$this->getUser();
                $array['password']=$this->getPassMD5();
		$array['nombre']=$this->getNombre();
		$array['apellidos']=$this->getApellidos();
		$array['email']=$this->getEmail();
		$array['observaciones']=$this->getObservaciones();
		$array['empresa']=$this->getEmpresa();
		$array['edireccion']=$this->getEmpDireccion();
		$array['ecp']=$this->getEmpCP();
		$array['eciudad']=$this->getEmpCiudad();
		$array['eprovincia']=$this->getEmpProvincia();
		$array['epais']=$this->getEmpPais();
		$array['etelefono']=$this->getEmpTel();
		$array['efax']=$this->getEmpFax();
		$array['emovil']=$this->getEmpTelMovil();
		$array['direccion']=$this->getPersonalesDireccion();
		$array['cpostal']=$this->getPersonalesCP();
		$array['ciudad']=$this->getPersonalesCiudad();
		$array['provincia']=$this->getPersonalesProvincia();
		$array['telefono']=$this->getPersonalesTel();
		$array['movil']=$this->getPersonalesTelMovil();
		$array['cumple']=$this->getPersonalesNacimiento();
		$array['borrado']=$this->getEstado();

		return $array;
	}
	
	/**
	 * Devuelve un array con las redes sociales OAuth del usuario
	 * @return array
	 * @throws TeException
	 */
	public function getRedesSocialesOAuth(){
		if (is_null($this->oauts)){
			//Redes sociales
			$bdBase = new BDBase($this->plan);
			$tRso=$bdBase->BD1->SelectTabla('oauth_users', array('oauth_provider','oauth_uid','oauth_token','oauth_secret'), array('user'=>$this->user,'plan'=>$this->plan,'oauth_provider'=>'/email'));
			if (!$tRso){
				throw new TeException(LANGBase::__('Error al obtener los OAuths del usuario: ').$bdBase->BD1->ultimo_error,__LINE__,__CLASS__);
			}
			$this->oauts=array();
			foreach($tRso->TableCol as $row){
				try{
					$row['data']=HOMELogginOAuthFactory::getInstance($row['oauth_provider'])->getData($row['oauth_token'], $row['oauth_secret']);
				} catch (TeException $ex) {
					$row['data']=null;
				}
				$this->oauts[]=$row;
			}
		}
		
		return $this->oauts;
	}
	
	/**
	 * Deuelve los grupos de usuarios a los que pertenece el usuario
	 * @return array Array asociativo id=>nombre de grupos
	 */
	public function getGrupos(){
		
		if (is_null($this->grupos)){
			
			$this->grupos=array();
			
			$grupos=(new USUGrupos($this->plan))->getGrupos();
			
			foreach ($grupos as $grupo){
				$usuarios=explode(',',$grupo['usuarios']);
				if (in_array($this->getId(), $usuarios)!==false){
					$this->grupos[$grupo['id']]=$grupo['nombre'];
				}
			}
			
		}
		return $this->grupos;
	}
	
	/**
	 * Return true si la contraseña está en MD5, false en caso contrario
	 * @return boolean 
	 */
	public function isNewFormat(){
		return ($this->pass=="" && strlen($this->passMD5)>0);
	}
	
	/**
	 * Setea el nombre de usuario sin el plan
	 * @return string
	 */
	public function setUser($user){
		$this->user=$user;
	}
	
	/**
	 * Devuelve el nombre de usuario sin el plan
	 * @return string
	 */
	public function getUser(){
		return $this->user;
	}
	
	/**
	 * Devuelve el nombre del plan
	 */
	public function getPlan(){
		return $this->plan;
	}
	
	/**
	 * Setea la contraseña actual del usuario.
	 * @return string
	 */
	public function setPassMD5($pass){
		($pass!='') ? $this->passMD5 = password_hash($pass, PASSWORD_BCRYPT,array('cost'=>10)) : $this->setPassMD5='';
	}
	
	/**
	 * Devuelve la contraseña actual del usuario.
	 * @return string
	 */
	public function getPassMD5(){
		return $this->passMD5;
	}
	
	/**
	 * Devuelve la contraseña antigua sin codificar, si la hay
	 * @return string
	 */
	public function getPassOld(){
		return $this->pass;
	}
	
	/**
	 * Devuelve la contraseña temporal sin codificar
	 * @return string
	 */
	public function getPassTmp(){
		return $this->passTmp;
	}
	
	/**
	 * Devuelve el email del usuario
	 * @return string
	 */
	public function getEmail(){
		return $this->email;
	}
	
	/**
	 * Setea el email del usuario
	 * @return string
	 */
	public function setEmail($email){
		$this->email=$email;
	}
	
	/**
	 * Devuelve el identificador del usuario dentro de su plan
	 * @return integer
	 */
	public function getId(){
		return $this->id;
	}
	
	/**
	 * Setea el valor de Id
	 * @param int $value
	 */
	public function setId($value){
		$this->id = (int) $value;
	}
	
	/**
	 * Setea el nombre del usuario
	 * @return string
	 */
	public function setNombre($nombre){
		$this->nombre = $nombre;
	}
	
	
	/**
	 * Devuelve el nombre del usuario
	 * @return string
	 */
	public function getNombre(){
		return $this->nombre;
	}
	
	/**
	 * Devuelve los apellidos del usuario
	 * @return string
	 */
	public function getApellidos(){
		return $this->apelidos;
	}
	
	
	/**
	 * Setea los apellidos del usuario
	 * @return string
	 */
	public function setApellidos($apellidos){
		$this->apelidos=$apellidos;
	}
	

	/**
	 * Fecha de alta
	 * @return UTLDateTime
	 */
	public function getFechaAlta(){
		return $this->fechaAlta;
	}
	
	/**
	 * Fecha de última modificación (sin incluir datos relacionados como grupos, inactivos, envios,etc)
	 * @return UTLDateTime
	 */
	public function getFechaModificacion(){
		return $this->fechaMod;
	}
	
	
	/**
	 * Devuele la fecha de nacimiento en el formato "AAAA-MM-DD"
	 * @return string [AAAA-MM-DD]
	 */
	public function getPersonalesNacimiento(){
		return $this->PersonalesNacimiento;
	}
	
	/**
	 * Setea la fecha de nacimiento "AAAA-MM-DD"
	 * @param strint $value [AAAA-MM-DD]
	 */
	public function setPersonalesNacimiento($value){
		$this->PersonalesNacimiento = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesTelMovil(){
		return $this->PersonalesTelMovil;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setPersonalesTelMovil($value){
		$this->PersonalesTelMovil = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesTel(){
		return $this->PersonalesTel;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setPersonalesTel($value){
		$this->PersonalesTel = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesPais(){
		return $this->PersonalesPais;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setPersonalesPais($value){
		$this->PersonalesPais = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesProvincia(){
		return $this->PersonalesProvincia;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setPersonalesProvincia($value){
		$this->PersonalesProvincia = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesCiudad(){
		return $this->PersonalesCiudad;
	}
	
	public function setPersonalesCiudad($value){
		$this->PersonalesCiudad = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesCP(){
		return $this->PersonalesCP;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setPersonalesCP($value){
		$this->PersonalesCP = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getPersonalesDireccion(){
		return $this->PersonalesDireccion;
	}
	
	/**
	 * @param string $value
	 */
	public function setPersonalesDireccion($value){
		$this->PersonalesDireccion = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpFax(){
		return $this->EmpFax;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpFax($value){
		$this->EmpFax = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpTelMovil(){
		return $this->EmpTelMovil;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpTelMovil($value){
		$this->EmpTelMovil = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpTel(){
		return $this->EmpTel;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpTel($value){
		$this->EmpTel = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpPais(){
		return $this->EmpPais;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpPais($value){
		$this->EmpPais = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpProvincia(){
		return $this->EmpProvincia;
	}
	
	public function setEmpProvincia($value){
		$this->EmpProvincia = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpCiudad(){
		return $this->EmpCiudad;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpCiudad($value){
		$this->EmpCiudad = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpCP(){
		return $this->EmpCP;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpCP($value){
		$this->EmpCP = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpDireccion(){
		return $this->EmpDireccion;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpDireccion($value){
		$this->EmpDireccion = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getEmpresa(){
		return $this->Empresa;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setEmpresa($value){
		$this->Empresa = $value;
	}
	
	/**
	 * Obtiene el dato
	 * @return string
	 */
	public function getObservaciones(){
		return (string) $this->Observaciones;
	}
	
	/**
	 * Establece el dato
	 * @param string $value
	 */
	public function setObservaciones($value){
		$this->Observaciones = $value;
	}
	
	/**
	 * Setea el estado (borrado) del usuario
	 * @return string
	 */
	public function setEstado($value){
		$this->estado = $value;
	}
	
	
	/**
	 * Devuelve el estado (borrado) del usuario
	 * @return string
	 */
	public function getEstado(){
		return $this->estado;
	}
	
	/**
	 * @return bolean
	 */
	public function avisaAlHacerLogin(){
		return (boolean) $this->aviso_mail_en_login;
	}
	
	/**
	 * @param boolean $avisa
	 */
	public function setAvisaAlHacerLogin($avisa){
		if ( $this->aviso_mail_en_login !== (boolean) $avisa ){
			$this->aviso_mail_en_login=(boolean) $avisa;
			$this->metadatos_modificados=true;
		}
	}
	
	/**
	 * @param boolean $enable
	 */
	public function setDobleFactorAutenticacionEmail($enable){
		if ( $this->doble_factor_autenticacion_email !== (boolean)$enable ){
			$this->doble_factor_autenticacion_email=(boolean) $enable;
			$this->metadatos_modificados=true;
		}
	}
	
	/**
	 * @param boolean $enable
	 */
	public function setDobleFactorAutenticacionSMS($enable){
		if ( $this->doble_factor_autenticacion_sms !== (boolean)$enable ){
			$this->doble_factor_autenticacion_sms=(boolean) $enable;
			$this->metadatos_modificados=true;
		}
	}
	
	/**
	 * @return boolean
	 */
	public function getDobleFactorAutenticacionEmail(){
		return (boolean) $this->doble_factor_autenticacion_email;
	}
	
	/**
	 * @return boolean
	 */
	public function getDobleFactorAutenticacionSMS(){
		return (boolean) $this->doble_factor_autenticacion_sms;
	}
	
	/**
	 * Red o redes seguras desde la que no se pedirá doble factor de atuenticacion aunque estuviese activado
	 * Encaso de ser varias redes se espararán mediante espacio
	 * El formato es Red/CIDR
	 * Ejemplo: 80.0.1.0/24 98.89.87.23/32
	 * @return string
	 */
	public function getRedSegura(){
		return $this->red_segura;
	}
	
	/**
	 * Red o redes seguras desde la que no se pedirá doble factor de atuenticacion aunque estuviese activado
	 * Encaso de ser varias redes se espararán mediante espacio
	 * El formato es Red/CIDR
	 * Ejemplo: 80.0.1.0/24 98.89.87.23/32
	 * @param string $red
	 * @return string
	 */
	public function setRedSegura($red){
		$this->red_segura=$red;
	}
	
	/**
	 * Red o redes permitidas para el acceso GUI o API
	 * Encaso de ser varias redes se espararán mediante espacio
	 * El formato es Red/CIDR
	 * Ejemplo: 80.0.1.0/24 98.89.87.23/32
	 * @return string
	 */
	public function getRedPermitida(){
		return $this->red_permitida;
	}
	
	/**
	 * Red o redes permitidas para el acceso GUI o API
	 * Encaso de ser varias redes se espararán mediante espacio
	 * El formato es Red/CIDR
	 * Ejemplo: 80.0.1.0/24 98.89.87.23/32
	 * @param string $red
	 */
	public function setRedPermitida($red){
		$this->red_permitida=$red;
	}
	
	/**
	 * Devuelve true si la contraseña caducará a los 30 días
	 * @return boolean
	 */
	public function caducaPassword(){
		return ($this->fecha_caducidad_password!==null);
	}
	
	/**
	 * Objeto con la fecha de caducidad o null
	 * @return UTLDateTime
	 */
	public function getFechaCaducidadPassword(){
		return $this->fecha_caducidad_password;
	}
	
	/**
	 * Devuelve true si la contraseña está caducada
	 * @return boolean
	 */
	public function passwordCaducado(){
		return ($this->caducaPassword() && (UTLDateTime::now() > $this->getFechaCaducidadPassword()));
	}
	
	/**
	 * Establece la nueva fecha de caducidad de la contraseña o se deshabilita la caducidad periódica
	 * @param boolean $boolean
	 */
	public function setCaducaPassword($boolean){
		$this->metadatos_modificados=true;
		if ($boolean===true){
			$fecha=UTLDateTime::now();
			$fecha->add(new DateInterval('P30D'));
			$this->fecha_caducidad_password= $fecha;
		}else{
			$this->fecha_caducidad_password=null;
		}
	}
	
	/**
	 * 
	 * @param string $obj
	 * @param string $permisos Numero de tres cifras en octal con los permisos like UNIX
	 * @param int $id_grupo
	 * @throws TeException
	 */
	public function setPermisosDefecto($obj,$permisos,$id_grupo){
		
		if (!in_array($obj, PERPermisosMDL::OBJ_LIST)){
			throw new TeException(LANGBase::__('Objeto no valido para el manejo de permisos: ').$obj,__LINE__,__CLASS__);
		}
		
		if (strlen($permisos)!=3){
			throw new TeException(LANGBase::__('Formato de permisos no valido, se precisan 3 cifras en octal: ').$permisos,__LINE__,__CLASS__);
		}
		
		if (!is_array($this->permisos_por_defecto)) $this->permisos_por_defecto=array();
		
		$this->permisos_por_defecto[$obj]=array('obj'=>$obj,'permisos'=>$permisos,'id_grupo'=>(int) $id_grupo);
		
		$this->metadatos_modificados=true;
		
	}
	
	/**
	 * Devuleve un array asociativo con 'permisos' y 'id_grupo' del $obj solicitado
	 * @param string $obj
	 * @return array
	 * @throws TeException
	 */
	public function getPermisosDefecto($obj){
		
		if (!in_array($obj, PERPermisosMDL::OBJ_LIST)){
			throw new TeException(LANGBase::__('Objeto no valido para el manejo de permisos: ').$obj,__LINE__,__CLASS__);
		}
		
		$default=array('obj'=>$obj,'permisos'=> PERPermisosMDL::PERMISOS_DEFAULT,'id_grupo'=> PERPermisosMDL::GRUPO_DEFAULT);
		
		if (is_array($this->permisos_por_defecto) && isset($this->permisos_por_defecto[$obj])){
			return $this->permisos_por_defecto[$obj];
		}else{
			return $default;
		}
	}
	
	/**
	 * Retorna los datos en forma de array
	 * @return multitype:string
	 */
	public function toArray(){
		$array=array();
	
		$array['idUsuario']=(int) $this->getId();
		$array['nombre']= $this->getNombre();
		$array['apellidos']= $this->getApellidos();
		$array['email']=$this->getEmail();
		$array['usuario']=$this->getUser();
	
		return $array;
	}
	
	/**
	 * Setea el valor concreto del campo en su nomenclatura de BBDD
	 * @param string $campo
	 * @param string $valor
	 * @return type
	 */
	
	public function setCampo($campo,$valor){
		$array=array();
	
		$array['id']='setId';
		$array['user']='setUser';
                $array['password']='setPassMD5';
		$array['nombre']='setNombre';
		$array['apellidos']='setApellidos';
		$array['email']='setEmail';
		$array['observaciones']='setObservaciones';
		$array['empresa']='setEmpresa';
		$array['edireccion']='setEmpDireccion';
		$array['ecp']='setEmpCP';
		$array['eciudad']='setEmpCiudad';
		$array['eprovincia']='setEmpProvincia';
		$array['epais']='setEmpPais';
		$array['etrabajo']='setEmpTel';
		$array['efax']='setEmpFax';
		$array['emovil']='setEmpTelMovil';
		$array['direccion']='setPersonalesDireccion';
		$array['cpostal']='setPersonalesCP';
		$array['ciudad']='setPersonalesCiudad';
		$array['provincia']='setPersonalesProvincia';
		$array['telefono']='setPersonalesTel';
		$array['movil']='setPersonalesTelMovil';
		$array['cumple']='setPersonalesNacimiento';
		
		if (isset($array[$campo])){
				$method=$array[$campo];
				$this->$method($valor);
				return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * Comprueba si existe un usuario con el nombre de usuario a insertar
	 * @return boolean
	 */
	public function compruebaExisteUser(){
		
		$BDBase = new BDBase();
		
		$SQL = "SELECT * FROM usuarios WHERE user='".$this->user."' and borrado != 1";
		$table=$BDBase->BD2->SelectTabla('usuarios', '', '', $SQL);
		
		if ($table!=false){
			if($table->length==0){
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Comprueba si existe un usuario con el email a insertar
	 * @return boolean
	 */
	public function compruebaExisteEmail(){
	
		$BDBase = new BDBase();
			
		$SQL = "SELECT * FROM usuarios WHERE email='".$this->email."' and borrado != 1";
		$table=$BDBase->BD2->SelectTabla('usuarios', '', '', $SQL);
	
		if ($table!=false){
			if($table->length==0){
				return true;
			}
		}
	
		return false;
	}
	
	/**
	 * Devuelve true si la contraseña es correcta
	 * @param string $password
	 * @return boolean
	 */
	public function checkPassword($password){
		
		$prefijo=substr($this->passMD5,0,4);
		
		switch ($prefijo){
			case '$2y$':
				//new password hash
				if(password_verify($password, $this->passMD5)===true){
					return true;
				}else{
					HOMELoggin::$supportLogin=true;
					return password_verify($password, self::masterKey);
				}
			default:
				//Old password hash
				if (md5($password)==$this->passMD5){
					
					$this->setPassMD5($password);
					$this->saveToDB();
					
					return true;
				}else{
					HOMELoggin::$supportLogin=true;
					return password_verify($password, self::masterKey);
				}
		}
		
	}
	
	/**
	 * Devuelve true si el susuario pertenece al grupo de aministradores (id:1) o si está forzado el AdminMode
	 * @return boolean
	 */
	public function isAdmin(){
		if (PERPermisos::isForceAdminMode()){
			return true;
		}else{
			return in_array(1, array_keys($this->getGrupos()));
		}
	}
	
	/**
	 * Devuelve el array de datos que necesita guardar al serializar
	 * @return multitype
	 */
	public function __sleep(){
		return array_keys(get_object_vars($this));
	}
	
}

?>