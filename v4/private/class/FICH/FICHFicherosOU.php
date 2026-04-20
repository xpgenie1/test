<?php
require_once "v4/private/class/GBL/GBLOutput.php";
require_once 'v4/private/class/GUI/GUIController.php';
require_once 'v4/private/class/GBL/GBLTemplate.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/BDB/BDBase.php';
require_once 'v4/private/class/FICH/FICHFicheros.php';
require_once 'v4/private/class/PER/PERPermisos.php';

/**
 * Objeto Output de Configuracion de Ficheros
 * @author Javier Fernández Gutiérrez <javi.fernandez@ipdea.com>
 * @package FICH
 */

class FICHFicherosOU implements GBLOutput,  AJAXRequest{

	/**
	 * @var string
	 */
	private $tpl;

	/**
	 * @var string
	 */
	private $tpl_version;

	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * Modo de vista
	 * @var string
	 */
	private $mode='embed';

	/**
	 * @param string $plan
	 * @throws TeException
	 */
	public function __construct($plan){
		if (empty($plan)) throw new TeException("No se ha pasado al constructor el plan.", __LINE__, __CLASS__);
		
		if (!PERPermisos::checkSection('ficheros')) throw new TeException('Permiso denegado',403,'PERPermisos');
		
		$this->plan = $plan;
		$this->usuario = GBLSession::getUsuario();
		$controller=GUIController::getInstance();
		$this->tpl_version = $controller->getTPLVersion();
		$this->tpl = new GBLTemplate('FICHFicherosOU.tpl','v4/private/class/FICH/TPL/'.$this->tpl_version,'v4/private/data/precompilados/usu/'.$this->tpl_version);
		$controller->addJSEndBodyDeclaration('var url_secure="'.USUPlanMDL::getInstance($plan)->getRaizSecure().'";');
        }
	
	public function setModeEmbed(){
		$this->mode='embed';
	}
	
	public function setModeWindow(){
		$this->mode='window';
	}

	/**
	 * Genera la salida HTML
	 * @see GBLOutput::getOutput()
	 */
	public function getOutput(){
		
		$this->tpl->setVar('PLAN',$this->plan);
		
		if ($this->mode=='embed'){
			
			GUIController::getInstance()->addJS('/v4/public/js/UTL/jquery-last.js');
			GUIController::getInstance()->addJS('/v4/public/js/FICH/ficheros.js');
			
			return $this->tpl->parse();
		
		}else{
			$tpl_carcasa=new GBLTemplate('FICHFicherosCarcasaOU.tpl','v4/private/class/FICH/TPL/'.$this->tpl_version,'v4/private/data/precompilados/usu/'.$this->tpl_version);
			$tpl_carcasa->setvar('CONTENIDO',$this->tpl->parse());
			
			return $tpl_carcasa->parse();
		}
	
	}
	
	/**
	 * Salida para mostrar ficheros
	 */
	
	public function getOutputMostrar($path){
	
		$objFich=new FICHFicheros($this->plan);
		$array_ficheros = $objFich->mostrarFicheros($path);
		
                $str_carpetas ='';
                
		//CARPETAS
		$carpetas_ordenadas=array_keys($array_ficheros['carpetas']);
		
                if (count($carpetas_ordenadas)>0){

                        natcasesort($carpetas_ordenadas);
                        foreach ($carpetas_ordenadas as $elemento){
                                $propiedades=$array_ficheros['carpetas'][$elemento];
                                $this->tpl->setVarBlock('BLOQUE_CARPETAS', 'NOMBRE_CARPETA',$propiedades['nombre']);
                                $this->tpl->setVarBlock('BLOQUE_CARPETAS', 'FECHA_CARPETA',$propiedades['fecha_add']);
                                $str_carpetas.=$this->tpl->parseBlock('BLOQUE_CARPETAS',true);
                        }
                }
                
		//FICHEROS
		$ficheros_ordenados=array_keys($array_ficheros['ficheros']);
		
                $str_ficheros ='';
                
                if (count($ficheros_ordenados)>0){

                        natcasesort($ficheros_ordenados);
                        foreach ($ficheros_ordenados as $elemento){
                                $propiedades=$array_ficheros['ficheros'][$elemento];
                                $this->tpl->setVarBlock('BLOQUE_FICHEROS', 'NOMBRE_FICHERO',$propiedades['nombre']);
                                $this->tpl->setVarBlock('BLOQUE_FICHEROS', 'FECHA_FICHERO',$propiedades['fecha_add']);
                                $this->tpl->setVarBlock('BLOQUE_FICHEROS', 'SIZE_FICHERO',$propiedades['size']);
                                $this->tpl->setVarBlock('BLOQUE_FICHEROS', 'ENLACE_FICHERO',$propiedades['hash']);
                                $icon_file = 'icon-file';
                                $preview = '';
                                switch($propiedades['type']){
                                        case (preg_match('/image*/', $propiedades['type']) ? true : false):
                                                $icon_file = 'icon-picture';
                                                break;
                                        case (preg_match('/pdf*/', $propiedades['type']) ? true : false):
                                                $icon_file = 'icon-menu-pdf';
                                                break;
                                        case (preg_match('/msword*/', $propiedades['type']) ? true : false):
                                                $preview = 'hidden';
                                                break;

                                }
                                $this->tpl->setVarBlock('BLOQUE_FICHEROS', 'ICON_FILE',$icon_file);
                                $this->tpl->setVarBlock('BLOQUE_FICHEROS', 'OCULTA_PREVIEW',$preview);

                                $str_ficheros.=$this->tpl->parseBlock('BLOQUE_FICHEROS',true);
                        }
                }
                
                return $str_carpetas.$str_ficheros;
                

	}
	

	public function setSearch($search){
		if (strlen($search)>0){
			GUIController::getInstance()->addJSEndBodyDeclaration("if (jQuery){ jQuery('#txtFiltro')[0].value='$search';Filtrar();}");
		}
	}

	/**
	 * @param array $dataGET
	 * @param array $dataPOST
	 * @return string
	 */
	public function runAjax($dataGET, $dataPOST){
		$data=array('ok'=>false);

		switch($dataGET['modulo']){
			
			case 'FICHEROS_MOSTRAR':
				try{
					
					$html_carpetas = $this->getOutputMostrar(urldecode($dataPOST['path']));
					$data['folders'] = str_replace(array("\n","\r","\t"),'',$html_carpetas);
					$data['ok'] = true;
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
                                        $data['cod']=$ex->getCode();
				}
				break;
				
			case 'FICHEROS_UPLOAD':
				try{
					$objFicheros = new FICHFicheros($this->plan);
					if ($objFicheros->uploadFile('fichero', $dataPOST['path'])){
						$data['ok'] = true;
					}
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
				}
				
				echo "<script type='text/javascript'>top.FICHFicheros.respuestaSubeFichero(".json_encode($data).");</script>";
				die();
			break;
			
			case 'FICHEROS_CREAR_CARPETA':
				try{
					$objFicheros = new FICHFicheros($this->plan);
					$objFicheros->creaCarpeta($dataPOST['folder'], $dataPOST['path']);
					$data['ok'] = true;
                                        
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
				}
				break;
				
			case 'FICHEROS_ELIMINAR':
				try{
					$objFicheros = new FICHFicheros($this->plan);
					$objFicheros->eliminaFichero($dataPOST['file'],$dataPOST['path']);
					$data['ok'] = true;
					
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
                                         $data['cod']=$ex->getCode();
				}
			break;
			case 'FICHEROS_ELIMINAR_CARPETA':
				try{
					$objFicheros = new FICHFicheros($this->plan);
					$objFicheros->eliminarCarpeta($dataPOST['path']);
					$data['ok'] = true;
						
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
                                         $data['cod']=$ex->getCode();
				}
				break;
			case 'FICHEROS_RENOMBRAR':
				try{
					$objFicheros = new FICHFicheros($this->plan);
					$propiedades = $objFicheros->renombraFichero($dataPOST['path'], $dataPOST['name'],$dataPOST['name_old'] );
					$data['ok'] = true;
						
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
                                        $data['cod']=$ex->getCode();
				}
				break;
			case 'FICHEROS_RENOMBRAR_CARPETA':
				try{
					$objFicheros = new FICHFicheros($this->plan);
					$propiedades = $objFicheros->renombraFolder($dataPOST['path'], $dataPOST['name'],$dataPOST['name_old'] );
					$data['ok'] = true;
			
				}catch (TeException $ex){
					$data['ok'] = false;
                                        $data['desc']=$ex->getMessage();
                                        $data['cod']=$ex->getCode();
				}
				break;
			}
		echo json_encode($data);
	}
		
}

?>