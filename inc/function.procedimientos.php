<?php
/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Mart�nez Ballesta.
   Fecha: Septiembre 2016

   ----------------------------------------------------------
 */


//[INICIO] [CRI] jmz18g  Fusiona array y convierte Query en array 
 function plugin_procedimientos_jointypes($array_1, $array_2) {
	
	    global $DB;
	
	$condition = array();
	
	if (!is_array($array_1)) {
	
	if($result = $DB->query($array_1)){
					
    while ($data = $DB->fetchassoc($result)) {

    array_push($condition, $data["id"]);

	}

	}
	
	} else {  
	
	foreach ($array_1 as $clave=>$valor) {
		
		array_push($condition, $valor);
		
	}		
	
	}		


	if (!is_array($array_2)) {
	
	$result = $DB->query($array_2);
	
    while ($data = $DB->fetchassoc($result)) {

    array_push($condition, $data["id"]);

	}

	} else {  
		
	foreach ($array_2 as $clave=>$valor) {
		
		array_push($condition, $valor);
		
	}		
	
	}

return plugin_procedimientos_jointype($condition);


}	

//[FINAL] [CRI] jmz18g  Fusiona array y convierte Query en array 

 
//[INICIO] [CRI] jmz18g  Devuelve un listado de id's para hacer un WHERE id IN (1,2,3,5)
 function plugin_procedimientos_jointype($array) {
	
$elementos=COUNT($array);
$indices="";
$indice=1;

foreach ($array as $clave=>$valor) {

if (empty($indices)) {

	if ($elementos==$indice) {
	
	$indices .= $valor;
	
	} else { 
	
	$indices .= $valor."'";
	
	}
	
} else {
if ($elementos==$indice){

	$indices .= ", '".$valor."";	

} else {

	$indices .= ", '".$valor."'";
	
}}
	
$indice++;

}

return $indices;

}	

//[FINAL] [CRI] jmz18g  Devuelve un listado de id's para hacer un WHERE id IN (1,2,3,5)


//Comprueba si para el procedimiento indicado existe un grupo dado
 function plugin_procedimientos_existeGrupo($procedimientos_id, $groups_id) {
	
	Global $DB;
	
	$query = "SELECT id FROM glpi_plugin_procedimientos_procedimientos_groups 
	          WHERE plugin_procedimientos_procedimientos_id=".$procedimientos_id." and groups_id =".$groups_id.";";
	
	$result = $DB->query($query);
	$result=$DB->query($query);
	$num_rows = $DB->numrows($result);
	
	if ($num_rows > 0){		
		return true;
	} else {
		return false;
	}
}	

//Comprueba si para el plugin FormCreator esta instalado y activado.	 
function plugin_procedimientos_checkForms () {
	
	Global $DB;
	
	$query = "SELECT state FROM glpi_plugins WHERE name='Forms' or name ='Form Creator';";
	//echo $query;
	//exit();
	$result = $DB->query($query);
					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
          //$row = $DB->fetch_array($result);
						$row = $DB->fetchAssoc($result);
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	
	$estado= $row["state"];
	if ((isset($estado))&&($estado == 1)){
		return true;
	} else {
		return false;
	}
}	
 
 //Devuelve aquellos procedimientos que no tengan asociado ning�n grupo
function get_procedimientos_publicos() {
	
	Global $DB;
	
	$publicos = array();
	$query = "SELECT 
			`glpi_plugin_procedimientos_procedimientos`.`id` 
			FROM `glpi_plugin_procedimientos_procedimientos`
			WHERE `glpi_plugin_procedimientos_procedimientos`.`id` 
			not in (select distinct `plugin_procedimientos_procedimientos_id`  from `glpi_plugin_procedimientos_procedimientos_groups`);";
	$result = $DB->query($query);
	
	foreach ($DB->request($query) as $data) {  // Para cada tarea del proyecto
		array_push($publicos, $data['id']);
	}
	return($publicos);
}	 
 
 
//Devuelve un array con los n�meros de l�nea de los elementos del procedimiento 
function getLineasProcedimiento($procedimientos_id) { 
	global $DB;
	
	$lineas = array(); // Array con las lineas que forman parte de un procedimiento.
	array_push($lineas,'---'); 
	
	$query = "SELECT line FROM glpi_plugin_procedimientos_procedimientos_items
			  where plugin_procedimientos_procedimientos_id=".$procedimientos_id.";";
	$result=$DB->query($query);
	$num_rows = $DB->numrows($result);
	
	if ($num_rows > 0){
	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//while ($row = $DB->fetch_array($result)) {
		while ($row = $DB->fetchAssoc($result)) {
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function		
			array_push($lineas,$row['line']); 
		}
	} else {
		array_push($lineas, 0); 
	}
	return $lineas;			
}

//Devuelve un array con los n�meros de l�nea de los elementos del procedimiento
function getLineasProcedimiento_id($procedimientos_id) { 
	global $DB;
	
	$lineas = array(); // Array con las lineas que forman parte de un procedimiento.
	array_push($lineas,'---'); 
	
	$query = "SELECT id, line FROM glpi_plugin_procedimientos_procedimientos_items
			  where plugin_procedimientos_procedimientos_id=".$procedimientos_id." order by line;";
	$result=$DB->query($query);
	$num_rows = $DB->numrows($result);
	
	if ($num_rows > 0){

	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//while ($row = $DB->fetch_array($result)) {
		while ($row = $DB->fetchAssoc($result)) {		
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function				
			$lineas[$row['id']]=$row['line']; 
		}
	} else {
		array_push($lineas, 0); 
	}
	return $lineas;			
}

//Devuelve los elementos que conforman un procedimiento perteneciente a una entidad dada.
function getItemsForProcedimiento($procedimientos_id, $entities_id) {
      global $DB;

      $items = array();

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_plugin_procedimientos_procedimientos_items`
                WHERE `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id` = '$procedimientos_id'
                ORDER BY `itemtype`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $data    = array();
      $totalnb = 0;
      for ($i=0 ; $i<$number ; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }
         $itemtable = getTableForItemType($itemtype);
         $query     = "SELECT `$itemtable`.*,
                              `glpi_plugin_procedimientos_procedimientos_items`.`id` AS IDD,
							  `glpi_plugin_procedimientos_procedimientos_items`.`line` AS linea,
							  `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id` AS procedimientos_id
                        FROM `glpi_plugin_procedimientos_procedimientos_items`,
                              `$itemtable`";
         $query .= " WHERE `$itemtable`.`id` = `glpi_plugin_procedimientos_procedimientos_items`.`items_id`
                           AND `glpi_plugin_procedimientos_procedimientos_items`.`itemtype` = '$itemtype'
                           AND `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id` = '$procedimientos_id' ORDER BY `linea`";
		
        $result_linked = $DB->query($query);
        $nb            = $DB->numrows($result_linked);

         while ($objdata = $DB->fetchassoc($result_linked)) {
		   $items[$objdata['linea']][$itemtype] = $objdata;
        }
     }
	  ksort($items); // Ordeno la tabla con los elementos del procedimiento por el campo n�mero de linea 
	  return $items;
   }

//Crea registro de los pasos de un procedimiento en un ticket 
function instancia_procedimiento($procedimientos_id, $tickets_id){ 
	global $DB;
	
	$procedimiento_Item   = new PluginProcedimientosProcedimiento_Item(); 
	$procedimiento_Ticket = new PluginProcedimientosProcedimiento_Ticket(); 
	
	// Seleccionamos los elementos que forman parte del procedimiento
	$iterator = $DB->request(['SELECT'    => [$procedimiento_Item->getTable().'.*',
											  PluginProcedimientosProcedimiento::getTable().'.name'],
							 
							  'FROM'      => $procedimiento_Item->getTable(),
												   
							  'LEFT JOIN' => [ PluginProcedimientosProcedimiento::getTable() => [

												  'ON' => [$procedimiento_Item->getTable() => 'plugin_procedimientos_procedimientos_id',
														   PluginProcedimientosProcedimiento::getTable() => 'id']

																							    ]
											],
											
							  'WHERE'     => ['plugin_procedimientos_procedimientos_id' => $procedimientos_id],
							  
							  'ORDERBY'   => [$procedimiento_Item->getTable().'.line']
							
							]);
							
	$error = [];
	
	//[INICIO] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO
	$nombre_procedimiento = "";
	//[FINAL] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO

	while ($data = $iterator->next()) {

			//[INICIO] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO
			$nombre_procedimiento = $data['name'];
		//[FINAL] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO

			$input = ['plugin_procedimientos_procedimientos_id' => $procedimientos_id,
					  'tickets_id' 								=> $tickets_id,
					  'line'  	 								=> $data['line'],
					  'itemtype'  	 							=> $data['itemtype'],
					  'items_id'  	 							=> $data['items_id'],
					  'state'  	 								=> 0];			 

			if ($procedimiento_Ticket->getFromDBByCrit(['plugin_procedimientos_procedimientos_id' => $procedimientos_id,
														'tickets_id' => $tickets_id,
														'line'  	 => $data['line']])) {

			$error[$procedimientos_id]=" ".$data['name']." ($procedimientos_id)";

			} else {


		//[INICIO] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO
				
		//$procedimiento_Ticket->add($input);

			$query = "INSERT INTO `glpi_plugin_procedimientos_procedimientos_tickets` 
			(`plugin_procedimientos_procedimientos_id`, `tickets_id`, `line`, `itemtype`, `items_id`, `state`) 
			VALUES ('".$input["plugin_procedimientos_procedimientos_id"]."',
							'".$input["tickets_id"]."', 
							'".$input["line"]."', 
							'".$input["itemtype"]."', 
							'".$input["items_id"]."', 
							'".$input["state"]."');";

				$DB->query($query);

		//[FINAL] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO				

			if ($data['itemtype'] == 'PluginProcedimientosProcedimiento'){ // Procedimiento anidado
					instancia_procedimiento($data['items_id'], $tickets_id);	
			}	

			}
    }


	if (count($error)>0) {
		
		$table='<table><tr>
				<td class="center" colspan="2"><strong>Procedimiento duplicado:</strong><br>';

				foreach ($error as $clave=>$valor) {

				$table.='- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>
						 <strong><FONT color="#51067b">'.$valor.'</FONT></strong><br>
						 - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br>';
				}				
			
				$table.='</td>
				</tr></table>';		
		
		Session::addMessageAfterRedirect( $table, false, ERROR );
	} else {
	//[INICIO] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO
		$message = $nombre_procedimiento."(".$procedimientos_id.")";
		Toolbox::logInFile('procedimientos', sprintf(
			'INFO [function.procedimientos.php:instancia_procedimiento] TicketID=%s, ProcedimientoID=%s, User=%s, Message=%s',
			$tickets_id,
			$procedimientos_id,
			isset($_SESSION['glpiname']) ? $_SESSION['glpiname'] : 'unknown',
			$message
		));
		//[FINAL] [CRI] JMZ18G SI USAMOS $procedimiento_Ticket->add($input) SE INCLUYE EN EL HISTÓRICO DEL TICKET UN EVENTO POR CADA LINEA DEL PROCEDIMIENTO
	
	}

	Toolbox::logInFile("procedimientos", "Procedimiento con ID ".$procedimientos_id." Instanciado para el ticket con ID ".$tickets_id. "\n");




/*
		// Seleccionamos los elementos que forman parte del procedimiento
		$query = "select * from glpi_plugin_procedimientos_procedimientos_items
				  where plugin_procedimientos_procedimientos_id=".$procedimientos_id." order by `line`;";
		$result = $DB->query($query);
		//echo "<br>Instancia: ".$query;
		while ($data = $DB->fetchassoc($result)) {			
			$query = "INSERT INTO glpi_plugin_procedimientos_procedimientos_tickets 
					 (plugin_procedimientos_procedimientos_id, tickets_id, line, itemtype, items_id, state)
					  VALUES (".$procedimientos_id.",".$tickets_id.",".$data['line'].",'".$data['itemtype']."',".$data['items_id'].",0)";
			$DB->query($query);
			
			if ($data['itemtype'] == 'PluginProcedimientosProcedimiento'){ // Procedimiento anidado
					instancia_procedimiento($data['items_id'], $tickets_id);	
			}		
		}	
        Toolbox::logInFile("procedimientos", "Procedimiento con ID ".$procedimientos_id." Instanciado para el ticket con ID ".$tickets_id. "\n");	*/	
}

function nameItemtype ($itemtype){
   switch ($itemtype) {
      case 'PluginProcedimientosMarcador' :
            return('Marcador');
      case 'PluginProcedimientosAccion' :
            return('Acci&oacute;n');
	  case 'PluginProcedimientosCondicion' :
            return('Condici&oacute;n');
	  case 'PluginProcedimientosSalto' :
            return('Salto');			
      case 'PluginProcedimientosProcedimiento' :
            return('Procedimiento');
      case 'PluginProcedimientosLink' :
            return('Enlace');			
   }			
}

// Renumera las l�neas de un procedimiento para que sean consecutivas.
function renumerar_procedimiento($procedimientos_id){
	global $DB;
		
	$select = "SELECT * FROM `glpi_plugin_procedimientos_procedimientos_items`
			  where plugin_procedimientos_procedimientos_id=".$procedimientos_id." and line<>99999 order by `line` ASC;";
	$result = $DB->query($select);
	$linea = 1;
	$lineas_originales = array();
	$lineas_nuevas = array();
	// Renumera las lineas del procedimiento (1,2,3...)
	if ($DB->numrows($result)){

	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//while ($item = $DB->fetch_array($result)){
		while ($item = $DB->fetchAssoc($result)){		
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	

			//echo "<br>-------------------------<br>";
			//echo "<br>Elemento a actualizar<br>";
			//print_r($item);
			array_push($lineas_originales,$item['line']); 
			array_push($lineas_nuevas,$linea);
			$update_linea = "update `glpi_plugin_procedimientos_procedimientos_items` set `line`='".$linea."' where `id`='".$item['id']."';";
			//echo "SQL:".$update_linea;
			$result_update = $DB->query($update_linea);		
			$linea = $linea +1;				
		}
	}
	

$update="UPDATE glpi_plugin_procedimientos_condicions a
LEFT join glpi_plugin_procedimientos_procedimientos_items b
on a.id_1 = b.id 
SET a.id_1 =  IF(b.line>99998, 0, IFNULL(b.id,0)),
a.line_id_1 = IF(b.line>99998, 0, IFNULL(b.line,0))
Where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

$DB->query($update); 	

$update="UPDATE glpi_plugin_procedimientos_condicions a
LEFT join glpi_plugin_procedimientos_procedimientos_items b
on a.id_2 = b.id 
SET a.id_2 =  IF(b.line>99998, 0, IFNULL(b.id,0)),
a.line_id_2 = IF(b.line>99998, 0, IFNULL(b.line,0))
Where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

$DB->query($update); 
	
$update="UPDATE glpi_plugin_procedimientos_condicions a
LEFT join glpi_plugin_procedimientos_procedimientos_items b
on a.id_3 = b.id 
SET a.id_3 =  IF(b.line>99998, 0, IFNULL(b.id,0)),
a.line_id_3 = IF(b.line>99998, 0, IFNULL(b.line,0))
Where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

$DB->query($update); 

$update="UPDATE glpi_plugin_procedimientos_condicions a
LEFT join glpi_plugin_procedimientos_procedimientos_items b
on a.id_4 = b.id 
SET a.id_4 =  IF(b.line>99998, 0, IFNULL(b.id,0)),
a.line_id_4 = IF(b.line>99998, 0, IFNULL(b.line,0)) 
Where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

$DB->query($update); 

$update="UPDATE glpi_plugin_procedimientos_condicions a
LEFT join glpi_plugin_procedimientos_procedimientos_items b
on a.id_5 = b.id 
SET a.id_5 =  IF(b.line>99998, 0, IFNULL(b.id,0)),
a.line_id_5 = IF(b.line>99998, 0, IFNULL(b.line,0))
Where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

$DB->query($update); 
/*	
$update="UPDATE glpi_plugin_procedimientos_condicions a
LEFT join glpi_plugin_procedimientos_procedimientos_items b
on a.id_1 = b.id 
LEFT join glpi_plugin_procedimientos_procedimientos_items c
on a.id_2 = c.id 
LEFT join glpi_plugin_procedimientos_procedimientos_items d
on a.id_3 = d.id 
LEFT join glpi_plugin_procedimientos_procedimientos_items e
on a.id_4 = e.id 
LEFT join glpi_plugin_procedimientos_procedimientos_items f
on a.id_5 = f.id 
SET a.id_1 =  IF(b.line>99998, 0, IFNULL(b.id,0)),
a.line_id_1 = IF(b.line>99998, 0, IFNULL(b.line,0)), 
a.id_2      = IF(c.line>99998, 0, IFNULL(c.id,0)), 
a.line_id_2 = IF(c.line>99998, 0, IFNULL(c.line,0)), 
a.id_3      = IF(d.line>99998, 0, IFNULL(d.id,0)), 
a.line_id_3 = IF(d.line>99998, 0, IFNULL(d.line,0)), 
a.id_4      = IF(e.line>99998, 0, IFNULL(e.id,0)),  
a.line_id_4 = IF(e.line>99998, 0, IFNULL(e.line,0)), 
a.id_5      = IF(f.line>99998, 0, IFNULL(f.id,0)), 
a.line_id_5 = IF(f.line>99998, 0, IFNULL(f.line,0))
Where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

$DB->query($update); 
*/
//echo $update;	

	$select="SELECT * FROM glpi_plugin_procedimientos_condicions where plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";

//echo $select."<br><br><br>";


	$result = $DB->query($select);
	
	if ($DB->numrows($result)){
	 
	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//while ($lineas = $DB->fetch_array($result)){ // Para cada condición que se encuentra
		while ($lineas = $DB->fetchAssoc($result)){ // Para cada condición que se encuentra	
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	

    $comment="<div class=\'condiciones\'>";
	if ((isset($lineas["line_id_1"])) and ($lineas["line_id_1"]>0)){ 
	
	$comment=$comment."Respuesta <strong>".$lineas['tag_id_1']."</strong>, ir a #linea: <strong><span id=\'linea_".$lineas['id']."\'>".$lineas['line_id_1']."</span></strong><BR>";  
	
	}
	
	if ((isset($lineas["line_id_2"])) and ($lineas["line_id_2"]>0)){ 
	
	$comment=$comment."Respuesta <strong>".$lineas['tag_id_2']."</strong>, ir a #linea: <strong><span id=\'linea_".$lineas['id']."\'>".$lineas['line_id_2']."</span></strong><BR>";  
	
	}
	
	if ((isset($lineas["line_id_3"])) and ($lineas["line_id_3"]>0)){ 
	
	$comment=$comment."Respuesta <strong>".$lineas['tag_id_3']."</strong>, ir a #linea: <strong><span id=\'linea_".$lineas['id']."\'>".$lineas['line_id_3']."</span></strong><BR>";  
	
	}
	if ((isset($lineas["line_id_4"])) and ($lineas["line_id_4"]>0)){ 
	
	$comment=$comment."Respuesta <strong>".$lineas['tag_id_4']."</strong>, ir a #linea: <strong><span id=\'linea_".$lineas['id']."\'>".$lineas['line_id_4']."</span></strong><BR>";  
	
	}
	if ((isset($lineas["line_id_5"])) and ($lineas["line_id_5"]>0)){ 
	
	$comment=$comment."Respuesta <strong>".$lineas['tag_id_5']."</strong>, ir a #linea: <strong><span id=\'linea_".$lineas['id']."\'>".$lineas['line_id_5']."</span></strong><BR>";  
	
	}
	
	$comment=$comment."</div>";
	


	
	if ($comment=="<div class=\'condiciones\'></div>")	{
		$comment = "<font color=\"red\">Error, La condicion no tiene aosciada ninguna linea</font>";
	}			
	
		//echo $comment."<br><br><br>";
	
	$update = "Update `glpi_plugin_procedimientos_condicions` SET `comment`='".$comment."'				
					   WHERE `id`='".$lineas['id']."';";	
				//echo "<br>Select update:".$update."<br>";					   
				$DB->query($update); 
				
	 }


//echo $update."<br><br><br>";	 
	

	}
	
	
		
/*// Actualiza condiciones.
	$select = "Select `glpi_plugin_procedimientos_condicions`.* from `glpi_plugin_procedimientos_procedimientos_items`
			  inner join `glpi_plugin_procedimientos_condicions` on (`glpi_plugin_procedimientos_condicions`.`id`=`glpi_plugin_procedimientos_procedimientos_items`.`items_id`)
			  where `glpi_plugin_procedimientos_procedimientos_items`.`itemtype`='PluginProcedimientosCondicion' 
			  and `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id`='".$procedimientos_id."'";
	$result = $DB->query($select);
	if ($DB->numrows($result)){

	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//while ($cond = $DB->fetch_array($result)){ // Para cada condición que se encuentra
		while ($cond = $DB->fetchAssoc($result)){ // Para cada condición que se encuentra
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function			
		
			// Renumera way_yes
			if (in_array($cond['way_yes'], $lineas_originales)) {
				$clave = array_search($cond['way_yes'], $lineas_originales); 
				$cond['way_yes'] = $lineas_nuevas[$clave];					
			}
			// Renumera way_no
			if (in_array($cond['way_no'], $lineas_originales)) {
				$clave = array_search($cond['way_no'], $lineas_originales); 
				$cond['way_no'] = $lineas_nuevas[$clave];
			}
			// Actualiza condici�n.
			$update = "Update `glpi_plugin_procedimientos_condicions` SET `comment`='Respuesta <strong>Si</strong>, ir a #linea: <strong>".$cond['way_yes']."</strong><BR>Respuesta <strong> No</strong>, ir a #linea: <strong>".$cond['way_no']."</strong>', 
						`way_no`='".$cond['way_no']."',
						`way_yes`='".$cond['way_yes']."'						
						WHERE `id`='".$cond['id']."';";			
			$DB->query($update);			
		}
	}	*/
	// Actualiza saltos de linea
	
	$update="UPDATE glpi_plugin_procedimientos_saltos a
join glpi_plugin_procedimientos_procedimientos_items b on a.goto_id = b.id and b.plugin_procedimientos_procedimientos_id=a.plugin_procedimientos_procedimientos_id 
SET a.goto_id =  if (b.line>99998,0,IFNULL(b.id,0)),
	a.goto =  if (b.line>99998,0,IFNULL(b.line,0)),
	a.comment =  if (b.line>99998, \"<font color=\'red\'>Error, El salto no tiene aosciada ninguna linea</font>\", CONCAT(\"Ir a #linea: \", b.line))
where a.plugin_procedimientos_procedimientos_id='".$procedimientos_id."'";
	
	
	//echo $update;

	/*
	exit();*/
	
		$DB->query($update);
/*
	$select = "Select `glpi_plugin_procedimientos_saltos`.* from `glpi_plugin_procedimientos_procedimientos_items`
			  inner join `glpi_plugin_procedimientos_saltos` on (`glpi_plugin_procedimientos_saltos`.`id`=`glpi_plugin_procedimientos_procedimientos_items`.`items_id`)
			  where `glpi_plugin_procedimientos_procedimientos_items`.`itemtype`='PluginProcedimientosSalto' 
			  and `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id`='".$procedimientos_id."'";
	//echo "<br>Select salto:".$select."<br>";
	$result = $DB->query($select);
	if ($DB->numrows($result)){

	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//while ($salto = $DB->fetch_array($result)){ // Para cada salto que se encuentra
		while ($salto = $DB->fetchAssoc($result)){ // Para cada salto que se encuentra
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function
		
			//echo "<br>Datos del salto: ";
			//print_r($salto);
			// Renumera goto
			if (in_array($salto['goto'], $lineas_originales)) {
				$clave = array_search($salto['goto'], $lineas_originales); 
				$salto['goto'] = $lineas_nuevas[$clave];

				// Actualiza salto
				$update = "Update `glpi_plugin_procedimientos_saltos` SET `comment`='Ir a #linea: <strong>".$salto['goto']."</strong>', 
						`goto`='".$salto['goto']."'					
					   WHERE `id`='".$salto['id']."';";	
				//echo "<br>Select update:".$update."<br>";					   
				$DB->query($update);
			}				
		}
	}*/
}

//Devuelve el ICONO perteneciente un estado dado. (Pendiente, Ejecutado, Actual y No operativo) 
function icono_estado($estado){
	   switch ($estado){
		   case 0:  return "<img title='Item Pendiente' src='../plugins/procedimientos/imagenes/check_off.png' />";
					break;
		   case 1:  return "<img title='Item Ejecutado' src='../plugins/procedimientos/imagenes/check.png' />";
					break;					   
		   case 2:  return "<img title='Item Actual' src='../plugins/procedimientos/imagenes/actual.png' />";
					break;
		   case 3:  return "<img title='Item No operativo' src='../plugins/procedimientos/imagenes/error.png' />";
					break;						
	   }
}

// Devuelve el ID del procedimiento principal (el que se ha seleccionado en el ticket)
function get_procedimiento_principal($tickets_id){
	global $DB;
	
	$select_procedimiento = "SELECT DISTINCT `plugin_procedimientos_procedimientos_id`
							FROM `glpi_plugin_procedimientos_procedimientos_tickets`
							where `tickets_id`='".$tickets_id."' and `plugin_procedimientos_procedimientos_id` not in
							(Select `items_id`
								FROM `glpi_plugin_procedimientos_procedimientos_tickets`
								where `tickets_id`='".$tickets_id."' and `itemtype`='PluginProcedimientosProcedimiento')";
	
	$result_procedimiento = $DB->query($select_procedimiento);

	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
	//$data = $DB->fetch_array($result_procedimiento);
		$data = $DB->fetchAssoc($result_procedimiento);		
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function

	return ($data['plugin_procedimientos_procedimientos_id']);
								
}

// Devuelve TRUE si el procedimiento $procedimientos_id ha sido ejecutado completamente en el ticket $tickets_id
function procedimiento_finalizado($tickets_id, $procedimientos_id){
	global $DB;

	$select_fin = "SELECT id FROM `glpi_plugin_procedimientos_procedimientos_tickets`
							where `tickets_id`='".$tickets_id."' 
							and `plugin_procedimientos_procedimientos_id`='".$procedimientos_id."'
							and `itemtype`='PluginProcedimientosMarcador'
							and `items_id`='2'
							and `state`=1;";
							
	$result_fin = $DB->query($select_fin);
// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
//$data = $DB->fetch_array($result_fin);
	$data = $DB->fetchAssoc($result_fin);	
// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	

	if (isset($data['id'])){
		return true;
	} else {
		return false;
	}											
}

//Devuelve el ESTADO del elemento solicitado en un procedimiento ejecutado en un ticket.
function get_estado($id){	
	$proc_ticket = new PluginProcedimientosProcedimiento_Ticket();
	$proc_ticket->getFromDB($id);

	$state = $proc_ticket->fields['state'];
	return ($state);
}

//Se encarga de ejecutar el procedimiento de un ticket.
function ejecutar_Procedimiento($tickets_id) {
	global $DB;
	
	$procedimiento_principal = get_procedimiento_principal($tickets_id);
	
	$select_items = "Select *
					from `glpi_plugin_procedimientos_procedimientos_tickets`
					where `tickets_id`='".$tickets_id."' order by id;";
	$result_items = $DB->query($select_items);
	$number_items = $DB->numrows($result_items);
	
	if (procedimiento_finalizado($tickets_id, $procedimiento_principal) == false) {
		// Ticket con procedimiento instanciado.
		if ($number_items > 0) {
			$continua = true;

	// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
		//while (($item = $DB->fetch_array($result_items)) && ($continua == true)) {
			while (($item = $DB->fetchAssoc($result_items)) && ($continua == true)) {			
	// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	

				$id_registro = $item['id'];
				$procedimientos_id = $item['plugin_procedimientos_procedimientos_id'];
				$line = $item['line'];
				$itemtype = $item['itemtype'];
				$items_id = $item['items_id'];
				// El estado puede cambiar durante la ejecuci�n, se consulta de la tabla del elemento.
				$state = get_estado($id_registro);
				if ($state == 0){ // Si el paso del procedimiento NO se ha ejecutado A�N
					
					// TRATAMIENTO MARCADOR
					if ($itemtype == 'PluginProcedimientosMarcador'){ // Lo marca "ejecutado" y pasa siguiente registro.
						//echo "<BR>Marcador<BR>";										
						$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`=1 WHERE id=".$id_registro.";";
						$result_update = $DB->query($update);
						
						Toolbox::logInFile("procedimientos", "TRATAMIENTO MARCADOR: Actualizado Marcador. Estado = 1 en el registro ".$id_registro. "\n");	
						
						// Si se trata de una marca de fin dentro del procedimiento principal en ejecuci�n.
						// Debe parar el procedimiento.
						if ($item['items_id']==2){	
							if ($procedimientos_id == $procedimiento_principal){
								$continua = false; 
								Html::redirect($_SERVER['HTTP_REFERER']);
							}	
						}
					
					// TRATAMIENTO DE ACCIONES				
					} else if ($itemtype == 'PluginProcedimientosAccion'){ 
						$select_accion = "SELECT *  FROM glpi_plugin_procedimientos_accions WHERE ID = ".$items_id.";";
						$result_accion = $DB->query($select_accion);

					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
					//$accion = $DB->fetch_array($result_accion);
						$accion = $DB->fetchAssoc($result_accion);								
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function							

						if (isset($accion)) {
							$nombre = $accion['name'];
							$descripcion = $accion['comment'];
							$tipo = $accion['plugin_procedimientos_tipoaccions_id'];
							$is_deleted = $accion['is_deleted'];						
						}
						// SOLO TRATA ACCIONES QUE NO ESTAN EN LA PAPELERA
						if ($is_deleted == 0){ 					
							// Tipo TAREA: crea la tarea, la inserta en el ticket, lo marca "ejecutado" y para la ejecuci�n.
							if ($tipo == 1){							
								$select_tarea = "Select * from `glpi_plugin_procedimientos_accions` left join `glpi_plugin_procedimientos_tareas` 
											on (`glpi_plugin_procedimientos_tareas`.plugin_procedimientos_accions_id = `glpi_plugin_procedimientos_accions`.id ) 
											where `glpi_plugin_procedimientos_accions`.id = ".$items_id.";";
																			
								$result_tarea = $DB->query($select_tarea);
							// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
							//$accion_tarea = $DB->fetch_array($result_tarea);		
								$accion_tarea = $DB->fetchAssoc($result_tarea);																	
							// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function									
											
					
								$tarea = new TicketTask();
								
								// INFORGES - emb97m - 12/03/2018 - Si hace uso de plantilla de tarea, toma los campos de estado
								
								if ($accion_tarea["tasktemplates_id"]>0){
									$template = new TaskTemplate();
									$template -> getFromDB($accion_tarea["tasktemplates_id"]);
									
									$input['taskcategories_id'] = $template->getField('taskcategories_id');
									$input['actiontime'] = $template->getField('actiontime');
									$input['content'] = $template->getField('content');
									// [INICIO] [CRI] - JMZ18G - 06/11/2020 Nuevos campos 9.4.5
									$input['tasktemplates_id'] = $accion_tarea["tasktemplates_id"]; 
									$input['users_id_tech'] = $template->getField('users_id_tech');
									$input['groups_id_tech'] = $template->getField('groups_id_tech');
									$input['state'] = $template->getField('state');
									$input['is_private'] = $template->getField('is_private');
									// [FIN] [CRI] - JMZ18G - 06/11/2020 Nuevos campos 9.4.5
								} else {
									$input['actiontime']= 0;
									$content_task = addslashes($accion_tarea["comment"]);
									$input["content"]= $content_task;									
									
									if ((isset($accion_tarea["taskcategories_id"]))&&($accion_tarea["taskcategories_id"]>0)){
										$input["taskcategories_id"]= $accion_tarea["taskcategories_id"];
									} else {
										$input["taskcategories_id"]=0;
									}
									// [INICIO] [CRI] - JMZ18G - 06/11/2020 Nuevos campos 9.4.5
									$input['actiontime'] = $accion_tarea["actiontime"]; // Duraci�n de la tarea (Si, No)
									$input['is_private']= $accion_tarea["is_private"]; // Privado (Si, No)
									$input['state'] = $accion_tarea["state"]; // Estado (Informaci�n, Por hacer, Hecho)
									$input['users_id_tech'] = $accion_tarea["users_id_tech"];
									$input['groups_id_tech'] = $accion_tarea["groups_id_tech"];	
									// [FIN] [CRI] - JMZ18G - 06/11/2020 Nuevos campos 9.4.5
								}
								
								$input["tickets_id"]=$tickets_id;	
								//[INICIO] [CRI] JMZ18G 01/03/2021 EL USUARIO QUE CREA LA TAREA ES GLPI	
								//$input["users_id"]=Session::getLoginUserID();
								  $input["users_id"]=2;
								//[FINAL] [CRI] JMZ18G 01/03/2021 EL USUARIO QUE CREA LA TAREA ES GLPI	
								
								if (isset($input["id"])){
									unset($input["id"]);
								}
								$instancia_id = $tarea->add($input);
								$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='2', `instancia_id`='".$instancia_id."' WHERE `id`='".$id_registro."';";
								$result_update = $DB->query($update);
								//echo "<BR>Espero a que la tarea se realice<BR>";
								$continua = false;						
                                Toolbox::logInFile("procedimientos", "TRATAMIENTO ACCIONES: Insertada Tarea con ID ".$instancia_id.". Estado = 2 en el registro ".$id_registro. "\n");									
							
								// INCLUIMOS EN EL TICKET LOS DOCUMENTOS ASOCIADOS A UNA TAREA
								// ======================= 30/04/2018 JDMARINZ ================================== 
								$documento = new Document();
								$Document_Item = new Document_Item();	
								
								$select_documento = "Select documents_id from `glpi_plugin_procedimientos_documents` 
											where `glpi_plugin_procedimientos_documents`.items_id = ".$items_id." and `glpi_plugin_procedimientos_documents`.itemtype = 'PluginProcedimientosTarea'";	
								
							$result = $DB->query($select_documento);
							$number = $DB->numrows($result);
						    $values = array();
						 
						 if ($number) {
							  
							     while ($data = $DB->fetchassoc($result)) {

// [INICIO] jmz18g 30/05/2019 CONTROLA SI LA RELACI�N TICKET Vs DOCUMENTO EXISTE
			
 $params = [
         "items_id" => $tickets_id, 
		 "itemtype" => "Ticket", 
		 "documents_id" => $data['documents_id'], 		 		 		 
			];
						
$rel_document = $Document_Item->find($params); 

if (count($rel_document) != 1) {
			
// [FINAL] jmz18g 30/05/2019 CONTROLA SI LA RELACI�N TICKET Vs DOCUMENTO EXISTE	 
			
									   $document_id = $data['documents_id'];
									   $documento -> getFromDB($document_id);
									   $values["documents_id"]= $documento->getField('id');
									   $values["items_id"]= $tickets_id;
									   $values["itemtype"]= 'Ticket';
									   $values["entities_id"]= $documento->getField('entities_id');
									   $values["is_recursive"]= $documento->getField('is_recursive');
									   $values["date_mod"]= $documento->getField('date_mod');
									
									$Document_Item->add($values);															
									  									   
								 }
								 
								 }
							  
						  }													
							
							// ======================= 30/04/2018 JDMARINZ ================================== 
							
							}						
							// Tipo ESCALADO: toma los datos del escalado, modifica el ticket, lo marca ejecutado y continua
							else if ($tipo == 2){							
								$select_escalado = "Select * from `glpi_plugin_procedimientos_accions`
									 inner join `glpi_plugin_procedimientos_escalados` on (`glpi_plugin_procedimientos_escalados`.plugin_procedimientos_accions_id = `glpi_plugin_procedimientos_accions`.id )
									 where `glpi_plugin_procedimientos_accions`.id = ".$items_id.";";							
								$result_escalado = $DB->query($select_escalado);

							// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
							//$accion_escalado = $DB->fetch_array($result_escalado);
								$accion_escalado = $DB->fetchAssoc($result_escalado);																										
							// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function													

								$ticket = new Ticket;
								$grupo =  new Group_Ticket();
								$usuario =  new Ticket_User();
								$proveedor =  new Supplier_Ticket();
								$errores="";

								if($accion_escalado['groups_id_observ']!=0){ 
									$input_itil_observer_g = array();
									$input_itil_observer_g['id'] = $tickets_id;							
                                    $input_itil_observer_g['_itil_observer'] = array (
                                             '_type' => 'group',
                                             'groups_id'=>$accion_escalado['groups_id_observ']
                                    );
									
									    //solicitante -->  ["actortype"]=> string(1) "1"
                                        //Observador -->  ["actortype"]=> string(1) "3"
                                        //Asignado a -->  ["actortype"]=> string(1) "2"
									
									   //============================== jmz18g inforges error mysql.log duplicate ===================================  
									 if ($groups_id = plugin_porcedimientos_getFromDBByField_3($grupo,
                                                          'groups_id',
                                                          $accion_escalado['groups_id_observ'],
														  'tickets_id',
														  $tickets_id,
														  'type',
														  3)) { // si existe en los grupos de GLPI 
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Grupo Observador:</font><FONT color='red'> ya estaba escalado</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['groups_id_observ']));  						  
														  } else {									
									$ticket->update($input_itil_observer_g);	
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Grupo Observador:</font><FONT color='green'> escalado correctamente</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['groups_id_observ']));  									
														  }
                                       //============================== jmz18g inforges error mysql.log duplicate ===================================      																		
								} 
								if($accion_escalado['groups_id_asignado']!=0){
									$input_itil_assign_g = array();
									$input_itil_assign_g['id'] = $tickets_id;									
                                    $input_itil_assign_g['_itil_assign'] = array (
                                            '_type' => 'group',
                                            'groups_id'=>$accion_escalado['groups_id_asignado']
                                     );
									    //============================== jmz18g inforges error mysql.log duplicate ===================================  
									 if ($groups_id = plugin_porcedimientos_getFromDBByField_3($grupo,
                                                          'groups_id',
                                                          $accion_escalado['groups_id_asignado'],
														  'tickets_id',
														  $tickets_id,
														  'type',
														  2)) { // si existe en los grupos de GLPI 
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Grupo Asignado:</font><FONT color='red'> ya estaba escalado</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['groups_id_asignado']));   						  
														  } else {
									$ticket->update($input_itil_assign_g);	
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Grupo Asignado:</font><FONT color='green'> escalado correctamente</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['groups_id_asignado']));  									
														  }
										//============================== jmz18g inforges error mysql.log duplicate ===================================  														  									
								}                                                                               
								if($accion_escalado['users_id_asignado']!=0){
									$input_itil_assign_u = array();
									$input_itil_assign_u['id'] = $tickets_id;								
                                    $input_itil_assign_u['_itil_assign'] = array (
											'_type' => 'user',
											'users_id'=>$accion_escalado['users_id_asignado'],
											'use_notification'=>0,
											'alternative_email' =>''
									); 
									
									    //============================== jmz18g inforges error mysql.log duplicate ===================================  
									 if ($groups_id = plugin_porcedimientos_getFromDBByField_3($usuario,
                                                          'users_id',
                                                          $accion_escalado['users_id_asignado'],
														  'tickets_id',
														  $tickets_id,														  
														  'type',
														  2)) { // si existe en los grupos de GLPI 
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Usuario Asignado:</font><FONT color='red'> ya estaba escalado</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['users_id_asignado']));   						  
														  } else {
									$ticket->update($input_itil_assign_u);	
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Usuario Asignado:</font><FONT color='green'> escalado correctamente</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['users_id_asignado']));  									
														  }
										//============================== jmz18g inforges error mysql.log duplicate ===================================  									
																		
								}
								if($accion_escalado['users_id_observ']!=0){
									$input_itil_observer_u = array();
									$input_itil_observer_u['id'] = $tickets_id;
                                    $input_itil_observer_u['_itil_observer'] = array (
                                           '_type' => 'user',
                                           'users_id'=>$accion_escalado['users_id_observ'],
                                           'use_notification'=>0,
                                           'alternative_email' =>''                                                                                                                                              
                                    ); 
									
									
									    //============================== jmz18g inforges error mysql.log duplicate ===================================  
									 if ($groups_id = plugin_porcedimientos_getFromDBByField_3($usuario,
                                                          'users_id',
                                                          $accion_escalado['users_id_observ'],
														  'tickets_id',
														  $tickets_id,
														  'type',
														  3)) { // si existe en los grupos de GLPI 
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Usuario Observador:</font><FONT color='red'> ya estaba escalado</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['users_id_observ']));   						  
														  } else {
									$ticket->update($input_itil_observer_u);	
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Usuario Observador:</font><FONT color='green'> escalado correctamente</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['users_id_observ']));  									
														  }
										//============================== jmz18g inforges error mysql.log duplicate ===================================  										
																	
								}
								
								if($accion_escalado['suppliers_id']!=0){
									$input_itil_assign_s = array();
									$input_itil_assign_s['id'] = $tickets_id;									
                                    $input_itil_assign_s['_itil_assign'] = array (
                                            '_type' => 'supplier',
                                            'suppliers_id'=>$accion_escalado['suppliers_id']
                                     );
									 
									    //============================== jmz18g inforges error mysql.log duplicate ===================================  
									 if ($groups_id = plugin_porcedimientos_getFromDBByField_3($proveedor,
                                                          'suppliers_id',
                                                          $accion_escalado['suppliers_id'],
														  'tickets_id',
														  $tickets_id,
														  'type',
														  2)) { // si existe en los grupos de GLPI 
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Proveedor Asignado:</font><FONT color='red'> ya estaba escalado</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['suppliers_id']));   						  
														  } else {
									$ticket->update($input_itil_assign_s);
									 $errores.=(sprintf(__("<STRONG><FONT color='#a42090'>Proveedor Asignado:</font><FONT color='green'> escalado correctamente</font></STRONG>.<br>", "procedimiento"),
                                                  $accion_escalado['suppliers_id']));  									
														  }
										//============================== jmz18g inforges error mysql.log duplicate ===================================  
									 
									 
									 
								}
								
								if (!empty($errores)){
									
							$error=(sprintf(__("<STRONG><FONT color='#339966'> %s </font></STRONG>:<br>", "procedimiento"),
                                                  $accion_escalado['name']));

									 Session::addMessageAfterRedirect($error.$errores);  			

								}
							
								$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1' WHERE `id`='".$id_registro."';";
								$result_update = $DB->query($update);															
                                Toolbox::logInFile("procedimientos", "TRATAMIENTO ACCIONES: Insertado Escalado. Estado = 1 en el registro ".$id_registro. "\n");									
							}						
							// Tipo MODIFICACI�N TICKET:
							else if ($tipo == 3) {
								$check_solution = false; // Revisar si pudo solucionar.
								$select_modify = "Select * from `glpi_plugin_procedimientos_accions`
									 inner join `glpi_plugin_procedimientos_updatetickets` on (`glpi_plugin_procedimientos_updatetickets`.plugin_procedimientos_accions_id = `glpi_plugin_procedimientos_accions`.id )
									 where `glpi_plugin_procedimientos_accions`.id = ".$items_id.";";							
								$result_modify = $DB->query($select_modify);

							// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
							//$accion_modify = $DB->fetch_array($result_modify);
								$accion_modify = $DB->fetchAssoc($result_modify);																																	
							// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function									
							
								$ticket = new Ticket();
								$ticket-> getFromDB($tickets_id);
								$estado_ticket = $ticket->fields['status'];
								$requesttypes_id_ticket = $ticket->fields['requesttypes_id'];
								$itilcategories_id_ticket = $ticket->fields['itilcategories_id'];
								$type_ticket = $ticket->fields['type'];
								$slts_ttr_id_ticket = $ticket->fields['slas_id_ttr']; // [CRI] jmz18g cambiar slts_ttr_id por slas_id_ttr
								$input = array();
								$input['id'] = $tickets_id;
							
								if ((isset($accion_modify['status'])) && 
									($accion_modify['status']!== $estado_ticket)&&($accion_modify['status']>0)){ // El estado tiene valor y es distinto del estado del ticket
									$input['status'] = $accion_modify['status'];
									if (($accion_modify['status'] == 5) || ($accion_modify['status'] == 6)){
										$check_solution = true; // Cambio de estado a resuelto o cerrado.
										$nuevo_estado = $accion_modify['status'];
									}									
								} 
								if ((isset($accion_modify['requesttypes_id'])) && 
									($accion_modify['requesttypes_id']!== $requesttypes_id_ticket)&&($accion_modify['requesttypes_id']>0)){ // El requesttypes_id tiene valor y es distinto del requesttypes_id del ticket
									$input['requesttypes_id'] = $accion_modify['requesttypes_id'];
								}
								if ((isset($accion_modify['itilcategories_id'])) && 
									($accion_modify['itilcategories_id']!== $itilcategories_id_ticket )&&($accion_modify['itilcategories_id']>0)){ // El itilcategories_id tiene valor y es distinto del itilcategories_id del ticket
									$input['itilcategories_id'] = $accion_modify['itilcategories_id'];
								}
								if ((isset($accion_modify['type'])) && 
									($accion_modify['type']!== $type_ticket)&&($accion_modify['type']>0)){ // El type tiene valor y es distinto del type del ticket
									$input['type'] = $accion_modify['type'];
								}
								
								//  [INICIO] [CRI] jmz18g cambiar slts_ttr_id por slas_id_ttr 
								
								if ((isset($accion_modify['slas_id_ttr'])) && 
									($accion_modify['slas_id_ttr']!== $slts_ttr_id_ticket )&&($accion_modify['slas_id_ttr']>0)){ // El slas_id tiene valor y es distinto del slas_id del ticket
									$input['slas_id_ttr'] = $accion_modify['slas_id_ttr'];
								} 
								
								//  [FINAL] [CRI] jmz18g cambiar slts_ttr_id por slas_id_ttr
								
								$ticket->update($input);
	
								if ((isset($accion_modify['solutiontemplates_id']))&&($accion_modify['solutiontemplates_id']>0)){ // Tiene una plantilla de soluci�n asignada.
									sleep(5);
									//$ticket2 = new Ticket();
									//$ticket2-> getFromDB($tickets_id);
								/*	$input2 = array();
								    $input2['id'] = $tickets_id;
									
									
									$template = new SolutionTemplate();
									$template->getFromDB($accion_modify['solutiontemplates_id']);								
									$input2['solution'] = addslashes($template->getField('content')); // INFORGES - emb97m - 29-09-2017 - Tratamiento del texto de soluci�n de la plantilla.
									$input2['solutiontypes_id'] = $template->getField('solutiontypes_id');
									$input2['solutiontemplates_id'] = $accion_modify['solutiontemplates_id'];
									$input2['_solve_to_kb'] = 0;
									$input2['status'] = 5;
									$nuevo_estado = 5;
									$check_solution = true; // Cambio de estado a resuelto.							
									//$ticket2->update($input2);
									$ticket->update($input2);*/

									$template = new SolutionTemplate();
									$template->getFromDB($accion_modify['solutiontemplates_id']);					

									 $solution = new ITILSolution();
									 $added = $solution->add([
										'itemtype'  => $ticket->getType(),
										'items_id'  => $ticket->getID(),
										'solutiontypes_id'   => $template->getField('solutiontypes_id'),
										'content'            => addslashes($template->getField('content'))
									 ]);
  
	  								$check_solution = true; // Cambio de estado a resuelto.
									$nuevo_estado = 5;
									$ticket->getFromDB($ticket->getID());
									
								}
								if ($check_solution == false) { // No existe cambio de estado a resuelto o cerrado.
									$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1' WHERE `id`='".$id_registro."';";
									$result_update = $DB->query($update);							
									Toolbox::logInFile("procedimientos", "TRATAMIENTO ACCIONES: Actualizado ticket. Estado = 1 en el registro ".$id_registro. "\n");									
								} else { // La modificaci�n implica cambio de estado a resuelto o cerrado.
									$state_ticket = $ticket->getField('status');
									if ($state_ticket > 4){ // Si se ha efectuado la modificaci�n.
										$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1' WHERE `id`='".$id_registro."';";
										$result_update = $DB->query($update);							
										Toolbox::logInFile("procedimientos", "TRATAMIENTO ACCIONES: Actualizado ticket. Estado = 1 en el registro ".$id_registro. "\n");									
									} else {
										if ($nuevo_estado == 5) {
											$nuevo_estado = 'Resuelto';
										} else {
											$nuevo_estado = 'Cerrado';
										}
										Session::addMessageAfterRedirect(sprintf(__("<STRONG><FONT color='red'>NO ha sido posible cambiar el estado del ticket a 
										       </font><FONT color='#a42090'> %s. </font> <FONT color='red'>Por favor, REVISA los campos obligatorios en este ticket.
											   </font></STRONG>.<br>", "procedimiento"), $nuevo_estado));										
										
										$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='2' WHERE `id`='".$id_registro."';";
										$result_update = $DB->query($update);	
										$continua = false;																			
									}
								}
							}						
							// Tipo SEGUIMIENTO
							else if ($tipo == 4){
								$select_followup = "Select * from `glpi_plugin_procedimientos_accions`
									 inner join `glpi_plugin_procedimientos_seguimientos` on (`glpi_plugin_procedimientos_seguimientos`.plugin_procedimientos_accions_id = `glpi_plugin_procedimientos_accions`.id )
									 where `glpi_plugin_procedimientos_accions`.id = ".$items_id.";";
									 
								$result_followup = $DB->query($select_followup);

							// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
							//$accion_followup = $DB->fetch_array($result_followup);
								$accion_followup = $DB->fetchAssoc($result_followup);																																									
							// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	
																															
								$fup = new ITILFollowup();
								$input = array();
								$input['items_id'] = $tickets_id;
								$input['users_id'] = Session::getLoginUserID();
								//[INICIO] [CRI] JMZ18G TEXTO ENRIQUECIDO SEGUIMIENTOS
								//$input['content']= $accion_followup['content'];
								$input['content']=  html_entity_decode($accion_followup['comment']); 
								//[FINAL] [CRI] JMZ18G TEXTO ENRIQUECIDO SEGUIMIENTOS
								$input['is_private']= $accion_followup['is_private'];
								$input['requesttypes_id']= $accion_followup['requesttypes_id'];							
								// Solo en la CARM existe este campo
								$input['followuptypes_id']= $accion_followup['followuptypes_id'];
								$input['itemtype']= 'Ticket';
								$input['_status']= 2;
								$input['_no_history']= false;
								
							
							/*
							
array(9) { ["content"]=> string(24) "<p>xxxxx</p>" ["itemtype"]=> string(6) "Ticket" 
["items_id"]=> string(2) "28" ["requesttypes_id"]=> string(1) "1" ["is_private"]=> string(1) "0" 
["_status"]=> string(1) "2" ["_glpi_csrf_token"]=> string(32) "ca886ddae6ba3792bf319161685bae21" 
["_no_history"]=> bool(false) ["_add"]=> string(7) "A�adir" } 							
							
							
array(7) { ["tickets_id"]=> string(2) "28" ["users_id"]=> string(1) "7" 
["content"]=> string(19) "Esto es la Prueba 1" ["is_private"]=> string(1) "1" ["requesttypes_id"]=> string(1) "0" ["followuptypes_id"]=> string(1) "0" ["_no_history"]=> bool(false) } 							
							*/
							
								$instancia_id = $fup->add($input);
							
								$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1', `instancia_id`='".$instancia_id."' WHERE `id`='".$id_registro."';";
								$result_update = $DB->query($update);								
                                Toolbox::logInFile("procedimientos", "TRATAMIENTO SEGUIMIENTO: Insertado seguimiento con ID ".$instancia_id.". Estado = 1 en el registro ".$id_registro. "\n");									
							
							
															// INCLUIMOS EN EL TICKET LOS DOCUMENTOS ASOCIADOS A UNA TAREA
								// ======================= 30/04/2018 JDMARINZ ================================== 
								$documento = new Document();
								$Document_Item = new Document_Item();	
								
								$select_documento = "Select documents_id from `glpi_plugin_procedimientos_documents` 
											where `glpi_plugin_procedimientos_documents`.items_id = ".$items_id." and `glpi_plugin_procedimientos_documents`.itemtype = 'PluginProcedimientosSeguimiento'";	
									
							$result = $DB->query($select_documento);
							$number = $DB->numrows($result);
						    $values = array();
						 
						 if ($number) {
							  
							     while ($data = $DB->fetchassoc($result)) {

// [INICIO] jmz18g 30/05/2019 CONTROLA SI LA RELACI�N TICKET Vs DOCUMENTO EXISTE

	      $params = [
         "items_id" => $tickets_id, 
		 "itemtype" => "Ticket", 
		 "documents_id" => $data['documents_id'], 		 		 		 
			];
						
$rel_document = $Document_Item->find($params); 

if (count($rel_document) != 1) {

// [FINAL] jmz18g 30/05/2019 CONTROLA SI LA RELACI�N TICKET Vs DOCUMENTO EXISTE
		
									   $document_id = $data['documents_id'];
									   $documento -> getFromDB($document_id);
									   $values["documents_id"]= $documento->getField('id');
									   $values["items_id"]= $tickets_id;
									   $values["itemtype"]= 'Ticket';
									   $values["entities_id"]= $documento->getField('entities_id');
									   $values["is_recursive"]= $documento->getField('is_recursive');
									   $values["date_mod"]= $documento->getField('date_mod');
									
									$Document_Item->add($values);	
}																	
									  									   
								 }
							  
						  }													
							
							// ======================= 30/04/2018 JDMARINZ ================================== 
							
							
							}
							// Tipo VALIDACI�N
							else if ($tipo == 5){
								$select_validation = "Select * from `glpi_plugin_procedimientos_accions`
									 inner join `glpi_plugin_procedimientos_validacions` on 
									 (`glpi_plugin_procedimientos_validacions`.plugin_procedimientos_accions_id = `glpi_plugin_procedimientos_accions`.id )
									 where `glpi_plugin_procedimientos_accions`.id = ".$items_id.";";

								$result_validation = $DB->query($select_validation);											

							// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
							//while ($row_val = $DB->fetch_array($result_validation)) {	
								while ($row_val = $DB->fetchAssoc($result_validation)) {																																															
							// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function																							
									$val = new TicketValidation();
									$input = array();
									$input['tickets_id'] = $tickets_id;
									$input['users_id'] = Session::getLoginUserID();
									$input['comment_submission']= $row_val['comment_submission'];
									if ($row_val["groups_id"]>0) { $input['groups_id_tech'] = $row_val["groups_id"]; }
									
									// [INICIO] jmz18g validaci�n Query de Usuario
									if (!empty($row_val['validador'])){
										$existe=0;
										$query_validation= $row_val['validador'].$_SESSION["glpiID"];
										
													if ($validation = $DB->query($query_validation)) {
													
													   if ($DB->numrows($validation)) {
													$existe = $DB->numrows($validation);	   
													   }
													
													}	

										if ($existe==1){

									// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
									//$id_validator = $DB->fetch_array($validation);
										$id_validator = $DB->fetchAssoc($validation);																																																								
									// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function										
										
										if (isset($id_validator["id"])) { 
										
										$input['users_id_validate']= $id_validator["id"];	
										
										} else { 
										
										$input['users_id_validate']=null; 
										
										}
										
										} else { 
										
										$input['users_id_validate']=null; 
										
										}

									} else {
										$input['users_id_validate']= $row_val['users_id_validate'];
									}	
									// [FINAL] jmz18g validaci�n Query de Usuario	
									
							if (is_null($input['users_id_validate'])){ // jmz18g validaci�n Query de Usuario
								    Toolbox::logInFile("procedimientos", "TRATAMIENTO VALIDACION: No Insertada validacion. No encontrada users_id_validate en ".$query_validation." - id_registro ".$id_registro. "\n");																	
									Session::addMessageAfterRedirect("<strong>Query <font color='#b12231'>NO VALIDA</font>.</strong>
			<BR><BR><font color='#22b147'>".$query_validation."</font>
			<br><br><strong>Resultados: <font color='#b12231'>".$existe."</font>.</strong>" ,true);
							} else {
									$instancia_id = $val->add($input);
							}
								}
								
							if (is_null($input['users_id_validate'])){ // jmz18g validaci�n Query de Usuario
								    Toolbox::logInFile("procedimientos", "TRATAMIENTO VALIDACION: No Insertada validacion. No encontrada users_id_validate en ".$query_validation." - id_registro ".$id_registro. "\n");																	
									Session::addMessageAfterRedirect("<strong>Query <font color='#b12231'>NO VALIDA</font>.</strong>
			<BR><BR><font color='#22b147'>".$query_validation."</font>
			<br><br><strong>Resultados: <font color='#b12231'>".$existe."</font>.</strong>" ,true);
							} else {
								
								$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1' WHERE `id`='".$id_registro."';";
								$result_update = $DB->query($update);									
                                Toolbox::logInFile("procedimientos", "TRATAMIENTO VALIDACION: Insertada validacion. Estado = 1 en el registro ".$id_registro. "\n");																	
							
							/*	$tarea = new TicketTask();
								
								// INFORGES - emb97m - 12/03/2018 - Si hace uso de plantilla de tarea, toma los campos de estado
								
									$input['actiontime']= 0;
									
									$input["content"]= "TRATAMIENTO VALIDACION: <hr>
									El ticket se encuentra en estado: <STRONG><font color=\'#b12231\'>Pendiente de validaci&oacute;n</font></STRONG><br>
									No se puede seguir el proceso mientras <STRONG><font color=\'#b12231\'>NO SE APRUEBE</font></STRONG> la validaci&oacute;n.
									";									
									
									$input["taskcategories_id"]=0;
									
									$input["users_id"]=Session::getLoginUserID();
																	
								
								// INFORGES - emb97m - 12/03/2018 Nuevos campos 9.1.6
								$input['is_private']= 1; // Privado (Si, No)
								$input['state'] = 1; // Estado (Informaci�n, Por hacer, Hecho)								
								$input['users_id_tech'] = $input['users_id_validate'];

								$instancia_id = $tarea->add($input);*/

								$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='2', `instancia_id`='".$instancia_id."' WHERE `id`='".$id_registro."';";
								
								$result_update = $DB->query($update);								
                                Toolbox::logInFile("procedimientos", "VALIDACI�N INSERTADA: <hr>Insertada validaci�n con ID ".$instancia_id.". Estado = 2 en el registro ".$id_registro. "\n");									
							
							
							
							}
							$continua = false;
							
							}					
	
							// [INICIO] [CRI] - JMZ18G - 06/05/2022 Añadir accion Eliminar Técnicos
							// Eliminar técnico		
							else if ($tipo == 6){
							
							$tu  = new Ticket_User();

							$params = [
								"tickets_id" => $tickets_id,
								'type' => 2,
							];										

							$all_relations = $tu->find($params);

							foreach ($all_relations as $relation_id => $relation) {
							
								//if (!Group_User::isUserInGroup($relation['users_id'], $accion_escalado['groups_id_asignado'])){

									$tu->delete(['id' => $relation_id]);									

								//}
							}									

							$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1' WHERE `id`='".$id_registro."';";
							$result_update = $DB->query($update);	
							Toolbox::logInFile("procedimientos", "ELLMINAR TÉCNICOS DEL TICKET: ".$tickets_id. "\n");																	

						}
						// [FINAL] [CRI] - JMZ18G - 06/05/2022 Añadir accion Eliminar Técnicos
						} else {
							$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='3' WHERE `id`='".$id_registro."';";
							$result_update = $DB->query($update);	
						
						}				
					}
					// TRATAMIENTO DE CONDICIONES			
					else if ($itemtype == 'PluginProcedimientosCondicion'){
						//echo "<BR>Condicion<BR>";
						$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='2' WHERE `id`='".$id_registro."';";
						$result_update = $DB->query($update);					
						$continua = false;
					} 
					// TRATAMIENTO DE SALTOS		
					else if ($itemtype == 'PluginProcedimientosSalto'){
						$select_salto = "SELECT *  FROM glpi_plugin_procedimientos_saltos WHERE ID = ".$items_id.";";
						$result_salto = $DB->query($select_salto);

						// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
						//$salto = $DB->fetch_array($result_salto);
							$salto = $DB->fetchAssoc($result_salto);																																																												
						// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function							

						if (isset($salto['goto'])) {											
							if ($salto['goto'] < $line){
								/*$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='0' WHERE `id`='".$id_registro."';";
								$result_update = $DB->query($update);*/
                                Toolbox::logInFile("procedimientos", "TRATAMIENTO SALTOS: reset_camino_salto_atras. Procedimiento ".$procedimientos_id. "
								                    , ticket ".$tickets_id.", salto ".$salto['goto'].", linea ".$line."\n");									
								reset_camino_salto_atras($procedimientos_id , $tickets_id, $salto['goto'], $line);
							} else if ($salto['goto'] > $line){
								$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1', 
										  `instancia_id`='".$salto['goto']."' WHERE `id`='".$id_registro."';";
								$result_update = $DB->query($update);							
                                Toolbox::logInFile("procedimientos", "TRATAMIENTO SALTOS: reset_camino_salto_adelante. Procedimiento ".$procedimientos_id. "
								                    , ticket ".$tickets_id.", salto ".$salto['goto'].", linea ".$line."\n");	
									reset_camino_salto_adelante($procedimientos_id , $tickets_id, $salto['goto'], $line);
							}
							$continua = false;
						}						
						
					// TRATAMIENTO DE PROCEDIMIENTOS ANIDADOS
					} else if ($itemtype == 'PluginProcedimientosProcedimiento'){
						$select_proc = "SELECT *  FROM glpi_plugin_procedimientos_procedimientos WHERE ID = ".$items_id.";";
						if ($_SESSION['glpi_use_mode'] == 2) {	
							//echo "<br>Select_proc:".$select_proc;
						}
						$result_proc = $DB->query($select_proc);

					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
					//$proc = $DB->fetch_array($result_proc);
						$proc = $DB->fetchAssoc($result_proc);																																																																		
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	

						if (isset($proc)) {
							if ($_SESSION['glpi_use_mode'] == 2) {	
							//	echo "<br>procedimiento:";
								//print_r($proc);
							}
							$nombre = $proc['name'];
							$descripcion = $proc['comment'];
							$active = $proc['active'];
							$is_deleted = $proc['is_deleted'];						
						}						
						// SOLO TRATA PROCEDIMIENTOS QUE NO ESTAN EN LA PAPELERA
						if (($is_deleted == 0) && ($active == 1)){ 	
							$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='1' WHERE `id`='".$id_registro."';";
							if ($_SESSION['glpi_use_mode'] == 2) {	
							//	echo "<br>Procedimiento activo y disponible";
							}
							$result_update = $DB->query($update);
							//Actualizamos el hist�rico 
							$message = "Ejecutar ".$nombre;
							Toolbox::logInFile('procedimientos', sprintf(
								'INFO [function.procedimientos.php:some_function] TicketID=%s, ProcedimientoID=%s, User=%s, Message=%s',
								$tickets_id,
								$procedimientos_id,
								isset($_SESSION['glpiname']) ? $_SESSION['glpiname'] : 'unknown',
								$message
							));
							
						} else {
							if ($_SESSION['glpi_use_mode'] == 2) {	
							//	echo "<br>Procedimiento no activo o en la papelera";
							}
							$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='3' WHERE `id`='".$id_registro."';";
							$result_update = $DB->query($update);
							
							$update_proc_delete = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='3'
								WHERE `plugin_procedimientos_procedimientos_id`='".$items_id."'
								and `tickets_id`= '".$tickets_id."';";								
							$result_proc_delete = $DB->query($update_proc_delete);								
						}						
					}
					// TRATAMIENTO DE ENLACES		
					else if ($itemtype == 'PluginProcedimientosLink'){
						//echo "<BR>Enlaces<BR>";
						$select_link = "SELECT *  FROM glpi_plugin_procedimientos_links WHERE ID = ".$items_id.";";
						$result_link = $DB->query($select_link);

					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
					//$link = $DB->fetch_array($result_link);
						$link = $DB->fetchAssoc($result_link);																																																																			
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function							
						
						if (isset($link)) {
							$nombre = $link['name'];
							$url = $link['comment'];					
						}						
						$update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='2' WHERE `id`='".$id_registro."';";
						$result_update = $DB->query($update);					
						$continua = false;
						//header ("Location: $url");
					} 					
				}
			}
		}
	}
}

//Ejecuta un salto hacia delante en la ejecuci�n de un procedimiento en un ticket
function reset_camino_salto_adelante($procedimientos_id, $tickets_id, $way, $linea) {
	global $DB;
	
	// Para obtener el ID de las lineas que se han de poner en estado "saltadas" (ID1, ID2)
	$select_ID1 = "select `id`
				   FROM `glpi_plugin_procedimientos_procedimientos_tickets`
				   WHERE `plugin_procedimientos_procedimientos_id`='".$procedimientos_id."'
				   and `tickets_id`= '".$tickets_id."'
				   and line =".$linea.";";  	
	$results_ID1 = $DB->query($select_ID1 );	

// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
//$elemento1 = $DB->fetch_array($results_ID1);
	$elemento1 = $DB->fetchAssoc($results_ID1);																																																							
// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	

	$id1 = $elemento1['id'];
	
	//echo $select_ID1."<br><br><br>";

	$select_ID2 = "select `id`
				   FROM `glpi_plugin_procedimientos_procedimientos_tickets`
				   WHERE `plugin_procedimientos_procedimientos_id`='".$procedimientos_id."'
				   and `tickets_id`= '".$tickets_id."'
				   and line =".$way.";";   	
	$results_ID2 = $DB->query($select_ID2 );

// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
//$elemento2 = $DB->fetch_array($results_ID2);
	$elemento2 = $DB->fetchAssoc($results_ID2);																																																																		
// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function			
	$id2 = $elemento2['id'];
	
//	echo $select_ID2."<br><br><br>";
	
	$update_estado = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='3'
					  WHERE `id`> '".$id1."' and `id`<'".$id2."'";
				//	  echo $update_estado;
		
	$result_estado = $DB->query($update_estado);	
	ejecutar_Procedimiento($tickets_id);
}

//Ejecuta un salto hacia atr�s en la ejecuci�n de un procedimiento en un ticket
function reset_camino_salto_atras($procedimientos_id, $tickets_id, $way, $linea) {
	global $DB;
	
	// Para obtener el ID de las lineas que se han de poner en estado "saltadas" ( >= ID2)
	$select_ID2 = "select `id`
				   FROM `glpi_plugin_procedimientos_procedimientos_tickets`
				   WHERE `plugin_procedimientos_procedimientos_id`='".$procedimientos_id."'
				   and `tickets_id`= '".$tickets_id."'
				   and line =".$way.";";   	
	$results_ID2 = $DB->query($select_ID2 );	

// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
//$elemento2 = $DB->fetch_array($results_ID2);
	$elemento2 = $DB->fetchAssoc($results_ID2);																																																																
// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function		
	$id2 = $elemento2['id'];
	
	$update_estado = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`='0'
					  WHERE `id`>= '".$id2."' and `tickets_id`= '".$tickets_id."'";
	$result_estado = $DB->query($update_estado);	
    Toolbox::logInFile("procedimientos", "reset_camino_salto_atras. ".$select_ID2 . "\n".
								                    $update_estado. "\n");
	ejecutar_Procedimiento($tickets_id);
}

   /**
    * Dropdown of validator
    *
    * @param $options   array of options
    *  - name                    : select name
    *  - id                      : ID of object > 0 Update, < 0 New
    *  - entity                  : ID of entity
    *  - right                   : validation rights
    *  - groups_id               : ID of group validator
    *  - users_id_validate       : ID of user validator
    *  - applyto
    *
    * @return nothing (display)
   **/
   
   //Genera la validaci�n de una lista desplegable y la pinta en un formulario
   
   function dropdownValidator(array $options=array()) {
      global $CFG_GLPI;
	  
      $params['name']               = '';
      $params['id']                 = 0;
      $params['entity']             = $_SESSION['glpiactive_entity'];
      $params['right']              = array('validate_request', 'validate_incident');
      $params['groups_id']          = 0;
	  $params['validador']          = "";
      $params['users_id_validate']  = array();
      $params['applyto']            = 'show_validator_field';

      foreach ($options as $key => $val) {
         $params[$key] = $val;
      }
	  if ((isset($params['groups_id']))&& ($params['groups_id']>0)){
			$types = array(0       => '---------------',
                     'user'  => __('User'),
                     'group' => __('Group'),
					 'validador' => __('Query de Usuario'));			
			$type = 'group';
			$params['users_id_validate']['groups_id'] = $params['groups_id'];		
			
	  } else {
			$types = array(0       => '---------------',
                     'user'  => __('User'),
                     'group' => __('Group'),
					 'validador' => __('Query de Usuario'));
			$type  = '';					 
	  }
	  	  
      if (isset($params['users_id_validate']['groups_id'])) {
         $type = 'group';
      } else if (!empty($params['users_id_validate'])) {
         $type = 'user';
      }
	  
	  	if ((isset($params['validador']))&&(!empty($params['validador']))){				
			$validador = $params['validador'];
			$type = 'validador';			
		} 

      $rand = Dropdown::showFromArray("validatortype", $types, array('value' => $type));

      if ($type) {
         $params['validatortype'] = $type;
         Ajax::updateItem($params['applyto'], $CFG_GLPI["root_doc"]."/plugins/procedimientos/ajax/dropdownValidator.php",
                          $params);
      }
      $params['validatortype'] = '__VALUE__';
      Ajax::updateItemOnSelectEvent("dropdown_validatortype$rand", $params['applyto'],
                                    $CFG_GLPI["root_doc"]."/plugins/procedimientos/ajax/dropdownValidator.php", $params);

      if (!isset($options['applyto'])) {
         echo "<br><span id='".$params['applyto']."'>&nbsp;</span>\n";
      }
   }

?>