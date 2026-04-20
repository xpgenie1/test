<?php

require_once 'v4/private/class/BDB/BDBase.php';

class UTLExportar{
	
	/**
	 * @var string
	 */
	private $plan;
	
	/**
	 * @param string $plan
	 * @throws TeException
	 */
	public function __construct() {
		
	}
	
	
	/**
	 * Obtiene listado de planes gratuitos con 25 envíos.
	 * @throws TeException
	 */
	public function exportarGratuitos25(){
	
		
		$BDBase = new BDBase();
		$this->BD1 = $BDBase->BD1;
	
		$SQL = 'SELECT nombre FROM clientes WHERE envios="25" and borrado=0';
		//$SQL = 'SELECT nombre FROM clientes WHERE nombre="ipdea" || nombre="teenviov3"';
		$table = $this->BD1->SelectTabla('clientes', "", "", $SQL);
		$result = "PLAN; USUARIO; EMAIL USUARIO; REMITENTES \n";
		
		if ($table!=false && $table->length>0){
			foreach($table->TableCol as $row_planes){
				$result.= $row_planes['nombre'];
				$BDBase2 = new BDBase($row_planes['nombre']);
				$this->BD2 = $BDBase2->BD2;
				
				$SQL_USUARIO = "SELECT * FROM usuarios WHERE borrado=0 LIMIT 1";
				$table_users = $this->BD2->SelectTabla('clientes', "", "", $SQL_USUARIO);
				if ($table_users!=false && $table_users->length>0){
					foreach ($table_users->TableCol as $row_usuarios){
						
						$result.= ";".$row_usuarios['user'].".".$row_planes['nombre'].";".$row_usuarios['email'];
					}
					
				}
				
				$SQL_REMITENTES = "SELECT * FROM remitentes WHERE borrado=0";
				$table_remitentes = $this->BD2->SelectTabla('remitentes', "", "", $SQL_REMITENTES);
				if ($table_remitentes!=false && $table_remitentes->length>0){
					$remitentes = ';';
					foreach ($table_remitentes->TableCol as $row_remitentes){
						
						$remitentes.= $row_remitentes['email'].", ";
					}
					$result = $result.$remitentes;
				}
				$result.= "\n";

			}
		}else{
			echo "ko";
		}
		//if ($rs==false) throw new TeException('Error al eliminar los remitentes - '.$this->BD2->ultimo_error, __LINE__,__CLASS__);
		return $result;

		
	}
}
?>