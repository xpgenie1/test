<?php
require_once "v4/private/class/GBL/GBLOutput.php";
require_once 'v4/private/class/GUI/GUIController.php';
require_once 'v4/private/class/GBL/GBLTemplate.php';
require_once 'v4/private/class/AJAX/AJAXRequest.php';
require_once 'v4/private/class/TICK/TICKTicketMDL.php';
require_once 'v4/private/class/TICK/TICKBase.php';
require_once 'v4/private/class/MBCA/MBCAConf.php';
include_once 'v4/private/class/UTL/UTLSendEmail.php';

/**
 * Objeto Output de Contactas
 * @author Javier Fernández Gutiérrez <javi.fernandez@ipdea.com>
 * @package CONTA
 */

class USUContactaOU implements GBLOutput{

	/**
	 * @var string 
	 */
	private $tpl;

        private $mbcaObj;
	/**
	 * @var string
	 */
	private $tpl_version;

	/**
	 * @var string
	 */
	private $plan;

	public function __construct($plan){
		if (empty($plan)) throw new TeException("No se ha pasado al constructor el plan.", __LINE__, __CLASS__);
		$this->plan = $plan;
		$this->usuario = GBLSession::getUsuario();
		$this->tpl_version = GUIController::getInstance()->getTPLVersion();
		$this->tpl = new GBLTemplate('USUContactaOU.tpl','v4/private/class/USU/TPL/'.$this->tpl_version,'v4/private/data/precompilados/usu/'.$this->tpl_version);
		$this->gui=GUIController::getInstance();
	}
        
        /**
	 * Genera la salida HTML
	 * @see GBLOutput::getOutput()
	 */
	public function getOutput(){
		
		$gui=GUIController::getInstance();
		$objUser=USUUsuarioMDL::getUsuarioActivo();
			
		$gui->addJS('/v4/public/js/UTL/jquery-last.js');
		$gui->addJS('/v4/public/js/USU/contacta.js');
		$this->tpl->setVar('NOMBRE', $objUser->getNombre());
		$this->tpl->setVar('REFERER', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-'));
		$this->tpl->setVar('EMAIL', $objUser->getEmail());
		$this->tpl->setVar('ID_USUARIO', $objUser->getId());
                
                $this->mbcaObj = MBCAConf::getInstance($this->plan);
                if($this->mbcaObj->isTeenvio()){
                        $objPlan=USUPlanMDL::getInstance($this->plan);
                        if (!$objPlan->isGratuito()){
                                $this->tpl->setVarBlock('BLOQUE_TEENVIO','BLOQUE_CLIENTES', $this->tpl->parseBlock('BLOQUE_CLIENTES',true));
                        }else{
                                $this->tpl->setVarBlock('BLOQUE_TEENVIO','BLOQUE_GRATUITO', $this->tpl->parseBlock('BLOQUE_GRATUITO',true));
                        }
                        $this->tpl->setVar('BLOQUE_TEENVIO',$this->tpl->parseBlock('BLOQUE_TEENVIO',true));
                }else{
                        $contenido=$this->getComercialBloque();
                        $contenido.=$this->getSoporteBloque();
                        if(!empty($contenido)){
                                $this->tpl->setVarBlock('BLOQUE_MBCA','CONTENIDO',$contenido);
                                $this->tpl->setVar('BLOQUE_MBCA',$this->tpl->parseBlock('BLOQUE_MBCA',true));
                        }
                }
                
		return $this->tpl->parse();
	
	}
        
        private function getComercialBloque(){
                $datos_comercial='';

                if(!empty($this->mbcaObj->getComercialTelf())){
                        $this->tpl->setVarBlock('BLOQUE_TELEFONO','TELEFONO',$this->mbcaObj->getComercialTelf());
                        $datos_comercial .= $this->tpl->parseBlock('BLOQUE_TELEFONO');
                }
                if(!empty($this->mbcaObj->getComercialCorreo())){
                        $this->tpl->setVarBlock('BLOQUE_EMAIL','EMAIL',$this->mbcaObj->getComercialCorreo());
                        $datos_comercial .= $this->tpl->parseBlock('BLOQUE_EMAIL');
                }
                if(!empty($datos_comercial)){
                        $this->tpl->setVarBlock('BLOQUE_DATOS','TITULO','__#Comercial#__');
                        $this->tpl->setVarBlock('BLOQUE_DATOS','DATOS',$datos_comercial);
                        return $this->tpl->parseBlock('BLOQUE_DATOS');
                }
                return FALSE;
        }
        
         private function getSoporteBloque(){
                $datos_soporte='';

                if(!empty($this->mbcaObj->getSoporteTelf())){
                        $this->tpl->setVarBlock('BLOQUE_TELEFONO','TELEFONO',$this->mbcaObj->getSoporteTelf());
                        $datos_soporte .= $this->tpl->parseBlock('BLOQUE_TELEFONO');
                }
                if(!empty($this->mbcaObj->getSoporteCorreo())){
                        $this->tpl->setVarBlock('BLOQUE_EMAIL','EMAIL',$this->mbcaObj->getSoporteCorreo());
                        $datos_soporte .= $this->tpl->parseBlock('BLOQUE_EMAIL');
                }
                if(!empty($datos_soporte)){
                        $this->tpl->setVarBlock('BLOQUE_DATOS','TITULO','__#Soporte#__');
                        $this->tpl->setVarBlock('BLOQUE_DATOS','DATOS',$datos_soporte);
                        return $this->tpl->parseBlock('BLOQUE_DATOS');
                }
                return FALSE;
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
			
			case 'CONTACTA_ENVIAR':
                                $this->mbcaObj = MBCAConf::getInstance($this->plan);
                                if($this->mbcaObj->isTeenvio()){
                                    $data = $this->generaTicketTeenvio($dataPOST);
                                }else{
                                    $data = $this->generaTicketMBCA($dataPOST);
                                }   
                                    
			break;
				
		
		}
		echo json_encode($data);
	}
        
        private function generaTicketTeenvio($dataPOST){
                try{
                        $dataPOST['comentario'] = $dataPOST['comentario']."<br /><br />Nombre: ".$dataPOST['nombre']."<br />User agent: ".$_SERVER['HTTP_USER_AGENT']."<br />Enviado desde: ".$dataPOST['donde_viene'];
                        TICKBase::init()->iniciaTicket($dataPOST['email'], $dataPOST['asunto'], $dataPOST['comentario'],'Ninguno',USUPlanMDL::getInstance($this->plan)->getId());
                        $data=array('ok'=>true);
                }catch(TeException $e){
                        $data=array('ok'=>false,'error'=>$e->getMessage());
                }
                return $data;
        }
        
        private function generaTicketMBCA($data){
                try{
                    $idioma = LANGBase::getInstance()->getCurrentLocale();
                    $remSoporte = $this->mbcaObj->getRemitenteSoporte($idioma);
                    
                    $sendMail=new UTLSendEmail();
                    $sendMail->setEncodingUTF8();
                    $sendMail->setAsunto("Nuevo contacto desde EMKT Link Mobility");
                    
                    $sendMail->setTo($this->mbcaObj->getSoporteCorreo());
                    $sendMail->setFrom($remSoporte['remitente']);
                    $sendMail->setReplyTo($data['email']);

                    $body = array("Plan: ".$this->plan,"Nombre: ". $data['nombre'],"Email: ".$data['email'],"Asunto: ".$data["asunto"],"Comentario: ".$data['comentario']);
                    $html = implode("<br/><br/>",$body);
                    $plain = implode("\n",$body);

                    $sendMail->setCuerpoHTML($html);
                    $sendMail->setCuerpoPlano($plain);
                    $sendMail->send();
                    $return=array('ok'=>true);
                 }catch(TeException $e){
                        $return=array('ok'=>false,'error'=>$e->getMessage());
                }
                return $return;
            
        }
		
}

?>