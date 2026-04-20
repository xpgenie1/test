<?php
require_once 'v4/private/class/USU/USUPlanMDL.php';
require_once 'v4/private/class/USU/USUUsuarioMDL.php';
require_once 'v4/private/class/GBL/GBLTeenvio.php';
require_once 'v4/private/class/UTL/UTLUtilidades.php';


/**
 * Manejo de los permisos para un susario
 * @author Victor J Chamorro <victor@ipdea.com>
 * @package USU
 */
class USUPermisos{
    const SIN_PERMISO = 0;
    const VISUALIZACION = 1;
    const ADMINISTRACION = 2;

    /**
	 * @var array:USUPermisos
	 */
	private static $instance=array();
        
        /**
         * @var string
         */
        private $plan = '';
	
	/**
	 * Objeto Usuario
	 * @var USUUsuarioMDL
	 */
	public $objUsuario=false;
	
	/**
	 * @var boolean
	 */
	private $per_grupo=false;
	
	/**
	 * @var boolean
	 */
	private $per_piezas=false;
	
	/**
	 * @var boolean
	 */
	private $per_ficheros=false;
	
	/**
	 * @var boolean
	 */
	private $per_envios=false;
	
	/**
	 * @var boolean
	 */
	private $per_config=false;
        
        /**
         *
         * @var int
         */
        private $per_cuenta;
        private $per_remitentes;
	
	/**
	 * Instancia un modelo de Permisos
	 * @param string $username
	 * @param string $plan
	 * @throws TeException
	 */
		
	public function __construct($username="",$plan=""){
		
		//Si se le pasa una referencia a un objeto USUUsuarioMDL		
		if(!empty($username) && !empty($plan)){
                        $this->plan=$plan;
			$this->objUsuario=new USUUsuarioMDL($username,$plan);
		}else if(GBLSession::getPlan()){
                        $this->plan=GBLSession::getPlan();
			$this->objUsuario=new USUUsuarioMDL(GBLSession::getUsuario(),$this->plan);
		}
		
		if ($this->objUsuario==false) throw new TeException("El username pasado al USUPermisos parece no ser correcto. ¿Hay Sesión de usuario activa?", 2,__CLASS__);
		
		$BDBase=new BDBase($this->plan);
		
		//Saco los permisos del usuario
		$SQL = "SELECT per_config FROM usuarios WHERE borrado = 0 and id='".$this->objUsuario->getId()."'";
		$BDBase->BD2->silencio=true;
		$permisos = $BDBase->BD2->SelectTabla('usuarios', '','', $SQL);
			
		if ($permisos == false || $permisos->length==0) throw new TeException("Error al generar el objeto tabla en permisosGrupos ".$this->BD2->ultimo_error, __LINE__, __CLASS__);
		
		$aPermisos = str_split($permisos->ItemCol(0, 'per_config'));
        	$this->per_cuenta = $aPermisos[0];
                //$this->per_remitentes = $aPermisos[1];
                
	}
	
	/**
	 * Devuelve una instnacia en singleton de USUPermisos, si no se le pasa username, plan, usa el logueado
	 * @param string $username
	 * @param string $plan 
	 * @throws TeException
	 * @return USUPermisos
	 */
	public static function getInstance($username="", $plan=""){
		
		if (isset(self::$instance[$username.$plan]) && self::$instance[$username.$plan] instanceof USUPermisos){
			if(UTLUtilidades::isDebug()) UTLUtilidades::echoDebug("\r\tSe devuelve la instancia ya existente en memoria de USUPermisos", __CLASS__);
			
		//Solo lo cojo de sessión si me preguntan por el mismo usuario logueado
		}else if(GBLSession::getPlan() && GBLSession::getUSUPermisos() instanceof USUPermisos && (
				(
						GBLSession::getUSUPermisos()->objUsuario->getUser()==$username && GBLSession::getUSUPermisos()->objUsuario->getPlan()== $plan
				) || (
						$username == "" && GBLSession::getUSUPermisos()->objUsuario->getUser()==GBLSession::getUsuario() &&
						$plan=="" && GBLSession::getUSUPermisos()->objUsuario->getPlan()==GBLSession::getPlan()
					)
			)) {
			
			self::$instance[$username.$plan] = GBLSession::getUSUPermisos();
			if(UTLUtilidades::isDebug()) UTLUtilidades::echoDebug("\r\tSe devuelve la instancia ya existente en sesión de USUPermisos", __CLASS__);
			
		}else{

			self::$instance[$username.$plan] = new USUPermisos($username,$plan);
			if(UTLUtilidades::isDebug()) UTLUtilidades::echoDebug("\r\tSe crea la nueva instancia de USUPermisos", __CLASS__);			
		}
		
		return self::$instance[$username.$plan];
	}
	
	/**
	 * Devuelve un boleano en función de si tiene o no permiso
	 * @return boolean
	 */
	public function getPermisosGrupos(){		
		return $this->per_grupo;	
	}
	
	/**
	 * Devuelve un boleano en función de si tiene o no permiso
	 * @return boolean
	 */
	public function getPermisosPiezas(){
		return $this->per_piezas;
	}
	
	/**
	 * Devuelve un boleano en función de si tiene o no permiso
	 * @return boolean
	 */
	public function getPermisosFicheros(){
		return $this->per_ficheros;
	}
	
	/**
	 * Devuelve un boleano en función de si tiene o no permiso
	 * @return boolean
	 */
	public function getPermisosEnvios(){
		return $this->per_envios;
	}
	
	/**
	 * Devuelve un boleano en función de si tiene o no permiso
	 * @return boolean
	 */
	public function getPermisosConfig(){
		return $this->per_config;
	}
        
        /**
	 * Devuelve un int con el permiso para la sección de Cuenta
	 * @return boolean
	 */
	public function getPermisosCuenta(){
		return $this->per_cuenta;
	}
        
        /**
	 * Devuelve un int con el permiso para la sección de Cuenta
	 * @return boolean
	 */
	public function getPermisosRemitentes(){
		return $this->per_remitentes;
	}
        
        
	
	/**
	 * Devuelve el array de datos que necesita guardar al serializar
	 * @return multitype
	 */
	public function __sleep(){
		$vars=array_keys(get_object_vars($this));
		return $vars;
	}
	
	/**
	 * Al despertar de una serialización
	 */
	public function __wakeup(){
		//$this->__construct($_SESSION['username'],$_SESSION['database']);
	}
	
	/**
	 * Antes de morir, se guarda en sesión
	 */
	public function __destruct(){
		if (GBLSession::getUsuario()==$this->objUsuario->getUser() && GBLSession::getPlan()==$this->objUsuario->getPlan()){
			GBLSession::setUSUPermisos($this);
		}
	}
}
?>