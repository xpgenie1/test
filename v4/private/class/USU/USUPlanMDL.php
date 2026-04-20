<?php
require_once 'v4/private/class/BDB/BDBase.php';
require_once 'v4/private/class/UTL/UTLUtilidades.php';
require_once 'v4/private/class/GBL/GBLSession.php';
require_once 'v4/private/class/UTL/UTLDateTime.php';
require_once 'v4/private/class/UTL/UTLIni.php';
require_once 'v4/private/class/USU/USUTipoPlanMDL.php';
require_once 'v4/private/class/CUOT/CUOTCiclo.php';

/**
 * Encapsulado de datos de un plan de teenvio
 * @package USU
 * @author Victor J. Chamorro <victor@ipdea.com>
 *
 */

class USUPlanMDL{
	
	/**
	 * Valor de la propiedad publicidad para que un plan gratuito no muestre publi en los envíos de email
	 * @var int
	 */
	const PUBLICIDAD_GRATUITO_DISABLED=2;
	
	/**
	 * Ruta relativa a include_path
	 * @var string
	 */
	const FILES_PATH_BASE='v3/uploads/';
	
	/**
	 * Array de Objetos USUPlanMDL en Singletone
	 * @var USUPlanMDL
	 */
	static $singletone=array();
	
	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * @var boolean
	 */
	private $activo;
	
	/**
	 * @var int
	 */
	private $id=0;
	
	/**
	 * Host del mysql del plan
	 * @var string
	 */
	private $mysql_host="";
	
	/**
	 * Envios en la ficha, debe coincidir con su ciclo
	 * @var int
	 */
	private $envios=0;
	
	private $cuota=0;
	
	/**
	 * Número de contactos en su ficha, debe coincidir con su ciclo
	 * @var int
	 */
	private $contactos=0;
	
	private $fecha_alta;
	private $user_sms;
	private $pass_sms;
	private $telf_sms;
	private $ip_alta='';
	private $pais_alta='';
	
	/**
	 * @deprecated
	 * @var int
	 */
	private $ip1;
	
	/**
	 * @deprecated
	 * @var int
	 */
	private $ip2;
	
	/**
	 * @deprecated
	 * @var int
	 */
	private $ip3;
	
	/**
	 * ID del segmento que gobierna el balanceo del plan
	 * @var int 
	 */
	private $id_segmento;
	
	private $publicidad;
	
	/**
	 * Id de idioma antiguo
	 * @var int
	 */
	private $idIdioma=3;
	
	/**
	 * @var string
	 */
	private $imagen_gui="";
	
	/**
	 * @var string
	 */
	private $cabecera_envios="";
	
	/**
	 * @var string
	 */
	private $pie_personalizado_envios="";
	
	/**
	 * @var string
	 */
	private $url_baja="";
	
	/**
	 * @var boolean
	 */
	private $visible_clave_propia=null;
	
	/**
	 * @var string
	 */
	private $timezone="Europe/Madrid";
	
	/**
	 * @var array
	 */
	private $parametros;
	
	/**
	 * Tipo de plan que consta en su ficha de plan, debe coincidir con el tipo de plan del último ciclo válido
	 * @var int
	 */
	private $tipo_plan_ficha=0;
	
	/**
	 * @var boolean
	 */
	private $acepto_condiciones_v3=false;
	
	/**
	 * Código descuento
	 * @var string
	 */
	private $codigo_descuento='';
	
	/**
	 * Código de suscripcion. 0=sin suscripcion
	 * @var string
	 */
	private $codigo_suscripcion=null;
	
	/**
	 * Array de ids de usuarios
	 * @var array:int
	 */
	private $usuarios;
	
	/**
	 * Urls de las piezas parseadas (link.php, baja.php, etc)
	 * @var string
	 */
	private $raiz_piezas;
	
	/**
	 * Urls de las piezas parseadas (pixel)
	 * @var string
	 */
	private $raiz_piezas_secure;
	
	/**
	 * Ruta para las imágenes y html de piezas
	 * @var string
	 */
	private $raiz_img;
	
	/**
	 * Host para las peticiones api
	 * @var string
	 */
	private $host_api;
	
	/**
	 * Ruta en disco para los ficheros del plan
	 * @var string
	 */
	private $files_path;
	
	/**
	 * Borrado del plan
	 * @var int
	 */
	private $borrado=null;
	
	/**
	 * Boleano que indica que hay datos sin guardar del plan (tabla clientes)
	 * @var boolean
	 */
	private $mod_data_plan=false;
	
	/**
	 * Boleano que indica que hay datos sin guardar (tabla personalizacion)
	 * @var boolean
	 */
	private $mod_data_personalizados=false;
	
	/**
	 *
	 * @var USUTipoPlanMDL
	 */
	private $objTipoPlan=null;
	
	/**
	 * Solo saca datos de BD1 (teenvio_system)
	 * @var boolean
	 */
	private $onlybd1=false;
	
	/**
	 * True si el sistema de bajas es el nuevo basado en resopnsables y finalidades (RGPD)
	 * @var boolean
	 */
	private $newUnsuscribeSystem=null;
	
	/**
	 * Construye un objeto MDL del plan pasado (el nombreplan o el id)
	 * @param string $nombreplan
	 * @param int $id
	 * @param boolean $onlybd1 [si se pasa a true, no se cargan los datos correspondientes a la bd concreta del plan (c_nombreplan) solo de teenvio_system
	 * @throws TeException
	 */
	public function __construct($nombreplan='',$id=0,$onlybd1=false){
		
		$this->onlybd1=(boolean) $onlybd1;
                
		if ($nombreplan=='' && $id==0) throw new TeException('No se ha especificado un nombre de plan o id en el constructor', __LINE__,__CLASS__);
		
		$BDBase=new BDBase();
		$BDBase->BD1->silencio=true;

	
		if ($nombreplan!=''){
			$where=array('nombre'=>$nombreplan,'borrado'=>0);
		}else{
			$where=array('id'=>$id);
		}
		
		$plan=$BDBase->BD1->SelectTabla("clientes",array('*'),$where);
		
		if ($plan!=false && $plan->length==1){
			$this->id=$plan->ItemCol(0,'id');
			
			/**
			 * Manda el ciclo
			 * @deprecated
			 * $this->tipo=$plan->ItemCol(0,'id_plan');
			 */
			
			$this->tipo_plan_ficha=$plan->ItemCol(0,'id_plan');
			
			$this->activo=$plan->ItemCol(0,'acepto_condiciones');
			
			$this->mysql_host=$plan->ItemCol(0,'mysql_host');
			
			$this->envios=$plan->ItemCol(0,'envios');
			$this->contactos=$plan->ItemCol(0, 'contactos');
			
			$this->cuota=$plan->ItemCol(0,'cuota');
			
			$this->fecha_alta=new UTLDateTime($plan->ItemCol(0,'fecha_alta'));
			$this->user_sms=$plan->ItemCol(0,'user_sms');
			$this->pass_sms=$plan->ItemCol(0,'pass_sms');
			$this->telf_sms=$plan->ItemCol(0,'telf_sms');
			$this->ip_alta=$plan->ItemCol(0,'ip_alta');
			$this->pais_alta=$plan->ItemCol(0,'pais_alta');
			
			/**
			 * Nuevo sistema de balanceo basado en segmentos
			 */
			$this->id_segmento=$plan->ItemCol(0,'id_segmento');
			
			$this->publicidad=$plan->ItemCol(0,'publicidad');
			
			$this->plan=$plan->ItemCol(0,'nombre');
			
			$this->codigo_descuento=$plan->ItemCol(0,'codigo_descuento');
			
			$this->codigo_suscripcion=$plan->ItemCol(0,'codigo_suscripcion');
			
			$this->borrado=$plan->ItemCol(0,'borrado');
			
			$this->files_path=UTLUtilidades::getFullPath(self::FILES_PATH_BASE).$plan->ItemCol(0, 'files_path');
			$this->files_path=str_replace('/./', '/', $this->files_path);
			
			$this->acepto_condiciones_v3=($plan->ItemCol(0,'acepto_condiciones')=='1');

			$this->getConfig($BDBase);
			
			
		}else{
			throw new TeException('No se ha podido construir un objeto USUPlanMDL '.$nombreplan.', error al sacar los datos de la BBDD: '.$BDBase->BD1->ultimo_error, 101,__CLASS__);
		}
	}
	
	/**
	 * Saca la configuracion del plan, idiomas, cabecera/pie y nombre para los campos auxiliares
	 * @param BDBase $BDBase
	 * @throws TeException
	 */
	function getConfig($BDBase){
                
		if ($this->onlybd1==false){
		
			$BDBase->creaConexionCliente($this->plan,$this->mysql_host);

			//Se necesita que no se escape para el campo parametros ya que tiene json
			$escapado=$BDBase->BD2->escapado;
			$BDBase->BD2->escapado=false;

			//Sacamos la configuración del plan de su tabla config
			$objPersonalizacion=$BDBase->BD2->SelectTabla("personalizacion", array('*'), array('borrado'=>0));

			$BDBase->BD2->escapado=$escapado;

			if ($objPersonalizacion==false) throw new TeException("Error al obtener los datos de configuracion del plan de la tabla ".$this->getDBName(true).".personalizacion: ".$BDBase->BD2->ultimo_error, __LINE__, __CLASS__);

			if ($objPersonalizacion->length==1){ 
				$row=$objPersonalizacion->RowCol(0);
				if (isset($row['id_idioma']))	$this->idIdioma=$row['id_idioma'];
				if (isset($row['cabecera']))	$this->cabecera_envios=$row['cabecera'];
				if (isset($row['pie']))		$this->pie_personalizado_envios= mb_convert_encoding($row['pie'], 'UTF-8', 'UTF-8, ISO-8859-15');
				if (isset($row['url_baja']))	$this->url_baja=$row['url_baja'];
				if (isset($row['imagen']))	$this->imagen_gui=$row['imagen'];
				if (isset($row['timezone']))	$this->timezone=$row['timezone'];
				if (isset($row['parametros']))  $this->parametros=json_decode($row['parametros'],true);
			}
			
		}
		
		//Sacamos las rutas
		if (!isset(UTLIni::$conf['LOGIN'])){
			try{
				UTLIni::addIniFile('v4/private/conf/home_planes.ini','LOGIN');
			}catch (TeException $e){
				throw new TeException('No se ha podido obtener la configuracion para el login',201,__CLASS__);
			}
		}
		
		$this->raiz_img='http://img1.teenvio.com/';
		$this->raiz_piezas='http://www.teenvio.com/';
		$this->raiz_piezas_secure='https://secure.teenvio.com/';
		$this->host_api='api.teenvio.com';
		
		$host='default';
		if (isset(UTLIni::$conf['LOGIN']['planes'][$this->plan])){
			$host=UTLIni::$conf['LOGIN']['planes'][$this->plan];
		}else{
			$bdhost=$this->getMySQLHost();
			if (isset(UTLIni::$conf['LOGIN'][$bdhost])){
				$host=$bdhost;
			}
		}
		
		if (isset(UTLIni::$conf['LOGIN'][$host]['url_static'])){
			$this->raiz_img=str_replace('###plan###',$this->plan,UTLIni::$conf['LOGIN'][$host]['url_static']);
		}
		
		if (isset(UTLIni::$conf['LOGIN'][$host]['url_base'])){
			$this->raiz_piezas=str_replace('###plan###',$this->plan,UTLIni::$conf['LOGIN'][$host]['url_base']);
		}
		
		if (isset(UTLIni::$conf['LOGIN'][$host]['url_secure'])){
			$this->raiz_piezas_secure=str_replace('###plan###',$this->plan,UTLIni::$conf['LOGIN'][$host]['url_secure']);
		}
		
		if (isset(UTLIni::$conf['LOGIN'][$host]['host_api'])){
			$this->host_api=UTLIni::$conf['LOGIN'][$host]['host_api'];
		}
		
	}
		
	/**
	 * Método para obtener una instancia en modo Singletone
	 * @param string $plan
	 * @param boolean $nosesion Si se establece a true no se intenta buscar la instancia en sesión, solo en memoria
	 * @return USUPlanMDL
	 * @see USUPlanMDL
	 */
	static public function getInstance($plan,$nosesion=false){
		if (isset(USUPlanMDL::$singletone[$plan]) && USUPlanMDL::$singletone[$plan] instanceof USUPlanMDL &&
			USUPlanMDL::$singletone[$plan]->getPlanName()==$plan){
			if (UTLUtilidades::isDebug() && UTLUtilidades::getDebugLevel()>1) UTLUtilidades::echoDebug("\n\tDevolviendo instancia ya existente en singleton del plan $plan", "USUPlanMDL");
		}elseif($nosesion==false && GBLSession::getPlan()==$plan && GBLSession::getMDLPlan() instanceof USUPlanMDL && GBLSession::getMDLPlan()->getPlanName()==$plan){
			USUPlanMDL::$singletone[$plan]=GBLSession::getMDLPlan();
			if (UTLUtilidades::isDebug() && UTLUtilidades::getDebugLevel()>1) UTLUtilidades::echoDebug("\n\tDevolviendo instancia ya existente en sesión del plan $plan", "USUPlanMDL");
		}else{
			if (UTLUtilidades::isDebug()) UTLUtilidades::echoDebug("\n\tCreando nueva instancia en singleton del plan $plan", "USUPlanMDL");
			USUPlanMDL::$singletone[$plan]=new USUPlanMDL($plan);
			if (GBLSession::getPlan()==$plan) GBLSession::setMDLPlan(USUPlanMDL::$singletone[$plan]);
		}
		
		return USUPlanMDL::$singletone[$plan];
	}
	
	public function __destruct(){
		if (isset(USUPlanMDL::$singletone[$this->plan]) && USUPlanMDL::$singletone[$this->plan] instanceof self)	GBLSession::setMDLPlan(USUPlanMDL::$singletone[$this->plan]);
	}
	
	/**
	 * Ip desde la que se ejecutó el alta
	 * @return string
	 */
	public function getIpAlta(){
		return $this->ip_alta;
	}
	
	/**
	 * Código ISO del país de alta (2 letras)
	 * @return string 
	 */
	public function getPaisAlta(){
		return $this->pais_alta;
	}
	
	/**
	 * Devuelve el código de suscripción, si devuelve cero no hay suscripcion.
	 * Puede devolver cualquier valor distinto de 0 o un código 14- + código suscripcion de paypal
	 * @return string
	 */
	public function getCodigoSuscripcion(){
		return empty($this->codigo_suscripcion) ? 0 : $this->codigo_suscripcion;
	}
	
	/**
	 * Setea el código de suscripción, el formato es NN-AUXDATA, para suscripciones paypal 14-PROFILEID
	 * @param string $code
	 */
	public function setCodigoSuscripcion($code){
		$this->mod_data_plan=true;
		$this->codigo_suscripcion=$code;
	}
	
	/**
	 * @return string
	 */
	public function getCodigoDescuento(){
		return $this->codigo_descuento;
	}
	
	/**
	 * Devuelve true si se han aceptado las condiciones generales de Teenvio
	 * @return boolean
	 */
	public function isActivo(){
		return ($this->activo==1);
	}
	
	/**
	 * @return boolean
	 */
	public function isGratuito(){
		return ($this->getTipoPlanCategoria()==USUTipoPlanMDL::CATEGORIA_GRATUITO);
	}
	
	/**
	 * Devuelve el nombre del plan con el que se ha construdio el objeto
	 * @return string
	 */
	public function getPlanName(){
		return $this->plan;
	}
	
	/**
	 * Devolverá el estado de la publicidad
	 * @return int
	 */
	public function getPublicidad(){
		return $this->publicidad;
	}
	
	/**
	 * Devuelve el ID del plan asignado 
	 * 1: grautio, 2: 5.000, 3: 10.000, ...
	 */
	public function getTipoPlan(){
		return CUOTCiclo::getInstance($this->plan)->getTipoPlan();
	}
	
	/**
	 * Id del tipo de plan guardado en su ficha, debe coincidir con el ciclo
	 * @param type $id_tipo_plan
	 */
	public function setTipoPlanFicha($id_tipo_plan){
		$this->tipo_plan_ficha=(int) $id_tipo_plan;
		$this->mod_data_plan=true;
	}
	
	/**
	 * Devuelve el tipo de plan asignado al cliente en su ficha, aunque no tenga ciclo activo.
	 * Este valor debería corresponder con el tipo de plan del último ciclo activo
	 */
	public function getTipoPlanFicha(){
		return $this->tipo_plan_ficha;
	}
	
	/**
	 * Devuelve la categoria del tipo de plan
	 * @return string gratuito|limitado|ilimitado|idem|bono|original
	 */
	public function getTipoPlanCategoria(){
		if (!$this->objTipoPlan instanceof USUTipoPlanMDL || $this->objTipoPlan->getId()!= $this->getTipoPlan()){
			$this->objTipoPlan=new USUTipoPlanMDL($this->getTipoPlan());
		}
		return $this->objTipoPlan->getTipoCategoria();
	}
	
	/**
	 * Devuelve el nombre del tipo de plan
	 * @return string
	 */
	public function getTipoPlanNombre(){
		if (!$this->objTipoPlan instanceof USUTipoPlanMDL || $this->objTipoPlan->getId()!= $this->getTipoPlan()){
			$this->objTipoPlan=new USUTipoPlanMDL($this->getTipoPlan());
		}
		return $this->objTipoPlan->getNombre();
	}
	
	/**
	 * Retorna el número de envios disponibles por ciclo, sin contar ampliaciones
	 * @return int
	 */
	public function getEnvios(){
		if (!$this->objTipoPlan instanceof USUTipoPlanMDL || $this->objTipoPlan->getId()!= $this->getTipoPlan()){
			$this->objTipoPlan=new USUTipoPlanMDL($this->getTipoPlan());
		}
		return (int) $this->objTipoPlan->getEnvios();
	}
	
	/**
	 * Número de envíos del plan, debe coincidir con su ciclo
	 * @param int $num
	 */
	public function setEnviosFicha($num){
		$this->envios=(int) $num;
		$this->mod_data_plan=true;
	}
	
	/**
	 * Retorna el número de MB asignados, sin contar ampliaciones
	 * @return int
	 */
	public function getCuota(){
		return $this->cuota;
	}
	
	/**
	 * Retorna el número de contactos disponibles por ciclo
	 * @return int
	 */
	public function getContactos(){
		if (!$this->objTipoPlan instanceof USUTipoPlanMDL || $this->objTipoPlan->getId()!= $this->getTipoPlan()){
			$this->objTipoPlan=new USUTipoPlanMDL($this->getTipoPlan());
		}
		return (int) $this->objTipoPlan->getContactos();
	}
	
	/**
	 * Número de contactos del plan, debe coincidir con su ciclo
	 * @param int $num
	 */
	public function setContactosFicha($num){
		$this->contactos=(int) $num;
		$this->mod_data_plan=true;
	}
	
	/**
	 * Retorna la fecha de alta en un objeto UTLDateTime
	 * @return UTLDateTime
	 */
	public function getFechaAlata(){
		return $this->fecha_alta;
	}

	/**
	 * (DEPRECATED)
	 * Retorna un array asociativo con los valores de valanceo de las 3 ips
	 * siendo 0 nada y 20 el maximo por cada una de ellas
	 * la suma de las 3 siempre debe dar 20
	 * @return array
	 * @deprecated
	 */
	public function getBalanceo(){
		return array(	'ip1'=>$this->ip1,
						'ip2'=>$this->ip2,
						'ip3'=>$this->ip3
				);
	}
	
	/**
	 * Devuelve un array asociativo con los datos del api SMS para ese plan:
	 * user,pass y telf
	 * @return array
	 */
	public function getSMSData(){
		return array(	'user'=>$this->user_sms,
						'pass'=>$this->pass_sms,
						'telf'=>$this->telf_sms
					);
	}
	
	/**
	 * Setea los datos de acceso a la api sms
	 * @param type $user
	 * @param type $pass
	 */
	public function setSMSData($user,$pass){
		$this->mod_data_plan=true;
		$this->user_sms=$user;
		$this->pass_sms=$pass;
	}
	
	/**
	 * Devuelve el nombre de la base de datos correspondiente a este plan
	 * Si se pasa un true, el valo se retorna entre comillas de mysql (`)
	 * @param boolean $comillas
	 * @return string
	 */
	public function getDBName($comillas=false){
		$BDBase=new BDBase();
		
		
		if ($comillas)
			return '`'.$BDBase->CONFIG['prefijo'].$this->plan.'`';
		else
			return $BDBase->CONFIG['prefijo'].$this->plan;
	}
	
	/**
	 * Devuelve el id del segmento asociado al plan, encargado de establcer 
	 * las reglas del balanceo
	 * @return int
	 */
	public function getSegmento(){
		return (int) $this->id_segmento;
	}
	
	/**
	 * Modifica el segmento asociado
	 * @param int $id_segmento
	 */
	public function setSegmento($id_segmento){
		if ($this->id_segmento!=(int) $id_segmento){
			$this->id_segmento=(int) $id_segmento;
			$this->mod_data_plan=true;
		}
	}
	
	/**
	 * Devuelve el id del idioma del plan para los envíos de email (piezas)
	 * @return string
	 */
	public function getIdIioma(){
		return $this->idIdioma;
	}
	
	/**
	 * setea el id del idioma de los envíos de email (piezas)
	 * @param int $id_idioma
	 */
	public function setIdIdioma($id_idioma){
		$this->idIdioma=$id_idioma;
		$this->mod_data_personalizados=true;
	}
	
	/**
	 * Devuelve si está o no activa la cabecera de <si no pude ver pinche aqui> en los envios
	 * @return boolean
	 */
	public function getCabeceraEnvios(){
		return (bool) $this->cabecera_envios;
	}
	
	/**
	 * Devuelve el contenido del pie personalizado
	 * @return string
	 */
	public function getPieEnvios(){
		$fichero_pie=UTLUtilidades::getFullPath('v4/private/conf/piez_pie_personalizado/'.$this->plan.'.html');
		if (is_file($fichero_pie)){
			return file_get_contents($fichero_pie);
		}else{
			return $this->pie_personalizado_envios;
		}
	}
	
	/**
	 * Setea el texto al pié personalizado de los envios de email
	 * @param string $pie_envios
	 */
	public function setPieEnvios($pie_envios){
		$this->pie_personalizado_envios=$pie_envios;
		$this->mod_data_personalizados=true;
	}
	
	/**
	 * Devuelve la imagen de personalización de la herramienta en la cabecera
	 * @return string | false
	 */
	public function getImagenGUI(){
		return (strlen(trim($this->imagen_gui))>0 ? $this->imagen_gui : false);
	}
	
	/**
	 * Devuelve la imagen de personalización de la herramienta en la cabecera
	 * @return string | false
	 */
	public function setImagenGUI($imagen){
		$this->imagen_gui=$imagen;
		$this->mod_data_personalizados=true;
	}
	
	/**
	 * Devuelve la url configurada que será llamada después de procesar una baja
	 * @return string | false
	 */
	public function getURLBaja(){
		return (strlen(trim($this->url_baja))>0 ? $this->url_baja : false);
	}
	
	/**
	 * Devuelve true si se debe mostar la clave propia
	 * @return boolean
	 */
	public function isVisibleClavePropia(){
		if ($this->visible_clave_propia!==null){
			return $this->visible_clave_propia;
		}else{
			$this->visible_clave_propia= (boolean) $this->getParametro('visible_clave_propia');
			return $this->visible_clave_propia;
		}
	}
	
	/**
	 * Setea la url que será llamada después de procesar una baja
	 * @param $url
	 */
	public function setURLBaja($url){
		$this->url_baja=$url;
		$this->mod_data_personalizados=true;
	}
	
	/**
	 * Setea la visibilidad de Clave Propia
	 * @param $boolean
	 */
	public function setVisibleClavePropia($boolean){
		$this->setParametro('visible_clave_propia', (boolean) $boolean);
		$this->visible_clave_propia= (boolean) $boolean;
	}


	/**
	 * Devuelve el id del plan en teenvio_system
	 * @return int
	 */
	public function getId(){
		return (int) $this->id;
	}
	
	/**
	 * Devuelve la Zona Horaria que tiene configurada el plan
	 * @return string Europe/Madrid
	 */
	public function getTimeZone(){
		return $this->timezone;
	}
	
	/**
	 * Zona horaria del plan (Europe/Madrid)
	 * @param string $timezone
	 */
	public function setTimeZone($timezone){
		$this->timezone=$timezone;
		$this->mod_data_personalizados=true;
	}
	
	/**
	 * Devuelve el array de datos que necesita guardar al serializar
	 * @return multitype
	 */
	public function __sleep(){
		return array_keys(get_object_vars($this));
	}
	
	/**
	 * Al despertar de una serialización
	 */
	public function __wakeup(){
		//parent::__construct();
	}

	/**
	 * Devuelve el parámetro si existe
	 * @param string $parametro
	 */
	public function getParametro($parametro){
		if (isset($this->parametros[$parametro])){
			return $this->parametros[$parametro];
		}
		return null;
	}
	/**
	 * Guarda el parámetro con el valor dado
	 * @param string $parametro
	 * @param string $contenido
	 * @throws TeException
	 */
	public function setParametro($parametro,$contenido){
		$this->parametros[$parametro]=$contenido;
		$datos=json_encode($this->parametros);
		if ($datos==null) throw new TeException('No se ha podido codificar en json los parámetros para ser guardados. ¿Datos no codificados en UTF-8?', __LINE__,__CLASS__);
		
		$BDBase = new BDBase($this->plan);		
		$ok=$BDBase->BD2->UpdateTabla('personalizacion','',array('parametros'=>$datos),array('borrado'=>'0'));
		if (!$ok) throw new TeException('Error al guardar los parámetros: - '.$BDBase->BD2->ultimo_error, __LINE__,__CLASS__);
	}
	
	/**
	 * Devuelve la version de la estructura de bbdd
	 * @return int
	 */
	public function getDatabaseVersion(){
		$DBVersion=$this->getParametro('db_version');
		if (is_null($DBVersion)){
			$this->setParametro('db_version', 4336);
			return 4336;
		}else{
			return (int) $DBVersion;
		}
	}
	
	/**
	 * Setea la version de la estructura de bbdd
	 * @param int $version
	 */
	public function setDatabaseVersion($version){
		$this->setParametro('db_version', (int) $version);
	}
	
	/**
	 * Devuelve true si el sistema de bajas es el nuevo basado en finalidad y responsable
	 * @return boolean
	 */
	public function newUnsuscribeSystem(){
		
		if (is_null($this->newUnsuscribeSystem)){
		
			$SQL="DESCRIBE inactivos";
			$BDBase = new BDBase($this->plan);
			$describe=$BDBase->BD2->SelectTabla('describe_inactivos', null, null, $SQL);
			$fields=array();
			foreach($describe->Table as $row){
				$fields[$row[0]]=$row;
			}
			$this->newUnsuscribeSystem=(isset($fields['id_responsable'],$fields['id_finalidad'],$fields['id_envio']));
		}
		
		return $this->newUnsuscribeSystem;
	}
	
	/**
	 * Devuelve el idioma preferente cuando no hay un idioma de navegador/cookie
	 * @return string
	 */
	public function getIdiomaPreferente() {
		$idioma=$this->getParametro('idioma_preferente');
		if (is_null($idioma)){
			$idioma='es_ES';
		}
		return $idioma;
	}

	/**
	 * Setea un idioma preferente para cuando no hay un idioma de navegador/cookie
	 * @param string $idioma_preferente Locale, por ejemplo en_US
	 * @throws TeException
	 */
	public function setIdiomaPreferente($idioma_preferente) {
		$idiomas_disponibles=LANGBase::getInstance()->getIdiomasDisponibles();		
		if (!isset($idiomas_disponibles[$idioma_preferente])) throw new TeException ('El locale '.$idioma_preferente.' no está disponible', __LINE__,__CLASS__);
		
		$this->setParametro('idioma_preferente', $idioma_preferente);
	}

	/**
	 * Devuelve el host de mysql del plan
	 * @return string
	 */
	public function getMySQLHost(){
		return $this->mysql_host;
	}

	/**
	 * Devuelve los ids de los usuarios del plan
	 * @throws TeException
	 * @return array:int
	 */
	public function getUsuarios(){
		
		if (empty($this->usuarios)){
			
			$BDBase = new BDBase($this->plan);
			$t_users= $BDBase->BD2->SelectTabla('usuarios', array('id'), array('borrado'=>0));
			if ($t_users==false) throw new TeException("Error al obtener los usuarios del plan: ".$BDBase->BD2->ultimo_error, __LINE__, __CLASS__);
			$this->usuarios=array();
			foreach($t_users->Table as $row){
				$this->usuarios[]=$row[0];
			}
		}
		
		return $this->usuarios;
	}
	
	/**
	 * Ruta para las imágenes y html de piezas, es decir, recursos estáticos
	 * @return string
	 * @see USUPlanMDL::getRaizStatic()
	 */
	public function getRaizImgs(){
		return $this->raiz_img;
	}
	
	/**
	 * Ruta para las imágenes y html de piezas, es decir, recursos estáticos
	 * @return string
	 * @see USUPlanMDL::getRaizImgs
	 */
	public function getRaizStatic(){
		return $this->raiz_img;
	}

	/**
	 * Ruta para el parseo de piezas para los links, bajas, pinque aquí, etc.
	 * @return string
	 * @see USUPlanMDL::getRaizBase()
	 */
	public function getRaizPiezas(){
		return $this->raiz_piezas;
	}
	
	/**
	 * Ruta para el parseo de piezas para los links, bajas, pinque aquí, etc.
	 * @return string
	 * @see USUPlanMDL::getRaizPiezas()
	 */
	public function getRaizBase(){
		return $this->raiz_piezas;
	}
	
	/**
	 * Ruta para el parseo de piezas, recurso pixel para lecturas, bajo protocolo seguro.
	 * @return string 
	 * @see USUPlanMDL::getRaizSecure()
	 */
	public function getRaizPiezasSecure(){
		return $this->raiz_piezas_secure;
	}
	
	/**
	 * Ruta para el parseo de piezas, recurso pixel para lecturas, bajo protocolo seguro.
	 * @return string 
	 * @see USUPlanMDL::getRaizPiezasSecure()
	 */
	public function getRaizSecure(){
		return $this->raiz_piezas_secure;
	}
	
	/**
	 * Host a utilizar para las peticiones api
	 * @return string
	 */
	public function getHostApi(){
		return $this->host_api;
	}
	
	/**
	 * Obtiene el valor del borrado del plan
	 * @return string 
	 */
	public function getBorrado(){
		return $this->borrado;
	}
	
	/**
	 * Setea el Borrado delp lan
	 * @return string
	 */
	public function setBorrado($borrado){
		$this->mod_data_plan=true;
		$this->borrado=$borrado;
	}
	
	/**
	 * Devuelve la ruta absoluta de su carpeta uploads
	 * @return string
	 */
	public function getFilesPath(){
		if (!is_dir($this->files_path)){
			mkdir($this->files_path);
		}
		return $this->files_path;
	}
	
	/**
	 * Devuelve true si el plan acepto condiciones en v3
	 * @deprecated v3
	 * @return boolean
	 */
	public function getAceptoCondicionesV3(){
		return $this->acepto_condiciones_v3;
	}
	
	/**
	 * Agrega el plan a la beta de Teenvio v4.0
	 * @deprecated
	 */
	public function joinBeta4(){
		
		if (!isset(UTLIni::$conf['LOGIN'],UTLIni::$conf['LOGIN2'])){
			try{
				UTLIni::addIniFile('v4/private/conf/home_planes.ini','LOGIN');
				UTLIni::addIniFile('/var/www/teenvio/v4/private/conf/home_planes.ini','LOGIN2');
			}catch (TeException $e){
				throw new TeException('No se ha podido obtener la configuracion para el login',__LINE__,__CLASS__);
			}
		}
		
		$host='default';
		if (isset(UTLIni::$conf['LOGIN2']['planes'][$this->plan])){
			$host=UTLIni::$conf['LOGIN2']['planes'][$this->plan];
		}
		
		if ($host=='default'){
			UTLIni::$conf['LOGIN']['planes'][$this->plan]='master2.teenvio.com';
			UTLIni::$conf['LOGIN2']['planes'][$this->plan]='master2.teenvio.com';
			try{
				UTLIni::writeINI('LOGIN', true);
				UTLIni::writeINI('LOGIN2', true);
				return true;
			}catch(TeException $e){
				throw new TeException('No se ha podido guardar la configuracion para el login tras el cambio a Beta v4 de '.$this->plan,__LINE__,__CLASS__);
			}
		}
		return false;	
	}
	
	/**
	 * Guarda losparámetros modificados
	 * @throws TeException
	 */
	public function saveToDB(){
		
		$BDBase = new BDBase($this->plan);
		
		if ($this->mod_data_personalizados){
			$data=array();
			$data['id_idioma']=$this->idIdioma;
			$data['imagen']=$this->imagen_gui;
			$data['timezone']=$this->timezone;
			$data['url_baja']=(string) $this->url_baja;
			$data['pie']=$this->pie_personalizado_envios;
			GBLSession::setTimezone($this->timezone);

			$ok=$BDBase->BD2->UpdateTabla('personalizacion',null,$data,array('borrado'=>0));
			if(!$ok){
				throw new TeException('Error al guardar los parámetros del plan: '.$BDBase->BD2->ultimo_error, __LINE__,__CLASS__);
			}
		}
		
                //Hay datos modificados
		if ($this->mod_data_plan){
			$data_plan=array();
        		if (!is_null($this->borrado)) $data_plan['borrado'] = $this->borrado;
			$data_plan['user_sms']= $this->user_sms;
			$data_plan['pass_sms']= $this->pass_sms;
			$data_plan['id_plan'] = $this->tipo_plan_ficha;
			$data_plan['envios']  = $this->envios;
			$data_plan['contactos']=$this->contactos;
			$data_plan['id_segmento']=$this->id_segmento;
			if (!is_null($this->codigo_suscripcion)) $data_plan['codigo_suscripcion']=$this->codigo_suscripcion;
			$ok = $BDBase->BD1->UpdateTabla('clientes',null,$data_plan,array('id'=>$this->id));
			UTLLog::guardaLog('update_clientes', 'update_clientes', print_r($data_plan,true). - 'id '.$this->id.' Trace: '.print_r(debug_backtrace(),true));
			if(!$ok){
				throw new TeException('Error al guardar los parámetros del plan: '.$BDBase->BD2->ultimo_error, __LINE__,__CLASS__);
			}
			$this->mod_data_plan=false;
		}
		
	}
	
	/**
	 * Limpia el Singleton tanto de sesión como de memoria
	 */
	public static function clearSinglenton(){
		
		GBLSession::_setValor("USUPlanMDL", serialize(null));
		USUPlanMDL::$singletone=array();
	}
}
?>
