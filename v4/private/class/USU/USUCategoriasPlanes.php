<?php
require_once 'v4/private/class/BDB/BDBase.php';

/**
 * Clase encargada de las categprías de planes
 * @package USU
 * @author Victor J Chamorro - victor@ipdea.com
 */
class USUCategoriasPlanes{
	
	/**
	 * Obtiene un array con los tipos de planes en activo extraidos de la tabla planes.
	 *   SELECT DISTINCT categoria FROM `planes` WHERE borrado =0
	 * @return array:string
	 */
	public static function getCategorias(){
		
		$array_planes=array();
		
		$BDBase = new BDBase();
		$objTable=$BDBase->BD1->SelectTabla('planes', array('DISTINCT categoria'), array('categoria'=>'/creditos_sms','borrado'=>0),'','amp_minima,id');
		if (!$objTable instanceof BDB\Table && !$objTable instanceof Table) throw new TeException ('No se ha podido obtener los tipos de planes '.$BDBase->BD1->ultimo_error, __LINE__, __CLASS__);
		foreach ($objTable->Table as $row){
			$array_planes[]=$row[0];
		}
		
		return $array_planes;
		
	}
	
	/**
	 * Obtiene los planes correspondientes a una categoría pasada con los siguientes datos:
	 * id, nombre, precio, contactos, envios
	 * @param string $categoria original|limitado|ilimitado|gratuito|bono|creditos_sms
	 * @return array
	 */
	public static function getTiposPlanes($categoria){
		
		if (empty($categoria)) throw new TeException ('No se ha pasado una categoría para obtener sus planes', __LINE__, __CLASS__);
		
		$BDBase = new BDBase();
		$BDBase->BD1->setCharset('utf8');
		$objTable=$BDBase->BD1->SelectTabla('planes', array('id', 'nombre', 'precio', 'contactos', 'envios'), array('borrado'=>0,'categoria'=>$categoria),'','categoria, contactos,envios ASC');
		if (!$objTable instanceof BDB\Table && !$objTable instanceof Table) throw new TeException ('No se ha podido obtener los tipos de planes '.$BDBase->BD1->ultimo_error, __LINE__, __CLASS__);
				
		return $objTable->TableCol;
	}
}

?>