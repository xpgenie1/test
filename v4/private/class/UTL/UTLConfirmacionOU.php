<?php
require_once 'v4/private/class/GBL/GBLOutput.php';
require_once 'v4/private/class/GUI/GUIController.php';

/**
 * Objeto OU para la página de confirmación de la validaciónn
 * @author David Moreno - david.moreno@teenvio.com
 * @package UTL
 */
class UTLConfirmacionOU implements GBLOutput{
   
    // Array de datos de strings con los que se generará la página
    private $datos;
    
    
    public function __construct(){
            GUIController::getInstance()->setTPLVersion('v4.0');
    }
    
    /**
     * Setea los datos para confirmación de Remitente
     */
    public function setRemitente(){
        $this->datos = array("titular"=>"Confirmación remitente",
                             "subtitular"=>LANGBase::__("Remitente activado"),
                             "texto"=>LANGBase::__("Te confirmamos que ya puedes utilizar tu remitente para realizar envíos."));
    }
    /**
     * Setea los datos para confirmación de usuario
     */
    public function setUsuario(){
        $this->datos = array("titular"=>"Confirmación usuario",
                             "subtitular"=>LANGBase::__("Usuario activado"),
                             "texto"=>LANGBase::__("Te confirmamos que tu usuario está activo."));
    }
    /**
     * Retorna el titular sin traducir, ya que se traduce en GUIController
     * @return string
     */
    public function getTitular(){
        return $this->datos['titular'];
    }

    public function getOutput(){
            $tpl = new GBLTemplate('UTLConfirmacionOU.tpl', 'v4/private/class/UTL/TPL/'.GUIController::getInstance()->getTPLVersion(), 'v4/private/data/precompilados/utl/'.GUIController::getInstance()->getTPLVersion());
            
            $tpl->setVar("SUBTITULAR",$this->datos['subtitular']);
            $tpl->setVar("TEXTO",$this->datos['texto']);
            
            
            return $tpl->parse();
    }
}
	

