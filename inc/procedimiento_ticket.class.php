<?php
/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Martínez Ballesta.
   Fecha: Septiembre 2016

   ----------------------------------------------------------
 */
include_once (GLPI_ROOT . "/plugins/procedimientos/inc/function.procedimientos.php");

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginProcedimientosProcedimiento_Ticket extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginProcedimientosProcedimiento';
   static public $items_id_1 = 'plugin_procedimientos_procedimientos_id';
    
   static public $itemtype_2 = 'Ticket';
   static public $items_id_2 = 'tickets_id';
   
   static $rightname = "plugin_procedimientos";



   static function canView(): bool {
      return Session::haveRight('plugin_procedimientos', READ);
   }
   
   
 /*  function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
		if (($item->getType()=='Ticket')&& ($this->canView())) {
			return _n('Proc. de Trabajo','Proc. de Trabajo',2); // INFORGES - emb97m - Septiembre 2017 - Error detectado en log.
			//return _n('Proc. de Trabajo', 0);
		}
	}*/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
		if (($item->getType()=='Ticket')&& ($this->canView())) {
			$num_elementos = 0;
			if ($_SESSION['glpishow_count_on_tabs']) {
				$num_elementos = countElementsInTable($this->getTable(),
                                            ['tickets_id' => $item->getID()]);
											
				if ($num_elementos > 0){
					return self::createTabEntry('Proc. de Trabajo', 1);
				} else {
					return self::createTabEntry('Proc. de Trabajo');
				}
			} else {
				return self::createTabEntry('Proc. de Trabajo');
			}
		}
	}	


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {  
      if ($item->getType()=='Ticket') {        
        self::showForTicket($item);
      } 
      return true;
   }
   
   // Pestaña "Procedimiento" en un Ticket
   static function showForTicket(Ticket $ticket, $withtemplate='') {
      global $DB, $CFG_GLPI;
	  
	  $instID = $ticket->fields['id'];
	  $entities_id = $ticket->fields['entities_id'];

      if (!$ticket->can($instID, READ)) {
         return false;
      }
      $canedit = $ticket->can($instID, UPDATE);
      $rand    = mt_rand();
	  
	  $pedido = false;
      
	  if ($canedit) {
	  
			echo "<div class='spaced'>";
			echo "<form name='procedimientoticket_form$rand' id='procedimientoticket_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL("PluginProcedimientosProcedimiento_Ticket")."'>";
			echo "<table class='tab_cadre_fixe'>";
			echo "<tr>";
			echo "<th>Seleccionar procedimiento de trabajo a aplicar</th>";
			echo "</tr>";

			// Buscamos id pedido de catalogo del ticket (si lo hay)
			if (plugin_procedimientos_checkForms()) { 
				if ($DB->TableExists("glpi_plugin_formcreator_forms") && $DB->TableExists("glpi_plugin_formcreator_forms_items")) {
					// Buscamos si dicho pedido de catálogo está en algún procedimiento de trabajo. si es así lo mostramos
					$query="SELECT 
						glpi_plugin_procedimientos_procedimientos.id as id, 
						glpi_plugin_procedimientos_procedimientos.name as nombre 
						FROM glpi_plugin_formcreator_forms inner join glpi_plugin_formcreator_forms_items
						on glpi_plugin_formcreator_forms.id=glpi_plugin_formcreator_forms_items.plugin_formcreator_forms_id 
						inner join glpi_plugin_procedimientos_procedimientos_forms
						on glpi_plugin_procedimientos_procedimientos_forms.plugin_formcreator_forms_id= glpi_plugin_formcreator_forms_items.plugin_formcreator_forms_id
						inner join glpi_plugin_procedimientos_procedimientos
						on glpi_plugin_procedimientos_procedimientos_forms.plugin_procedimientos_procedimientos_id=glpi_plugin_procedimientos_procedimientos.id
						where glpi_plugin_formcreator_forms_items.itemtype='Ticket' and glpi_plugin_formcreator_forms.is_active=1
						and glpi_plugin_procedimientos_procedimientos.is_deleted=0  
						and glpi_plugin_procedimientos_procedimientos.active=1 
						and glpi_plugin_formcreator_forms_items.items_id=".$instID." order by glpi_plugin_procedimientos_procedimientos_forms.id ASC";
					//echo "<br>Query pedidos:".$query."<br>";
					$result = $DB->query($query);
					if ($row = $DB->fetchassoc($result)) {
						if (isset($row['id'])){
							$params["toadd"]= array($row["id"] => $row["nombre"]);
							$params["value"]= $row["id"];
							$procedimientos_id = $row["id"];
							$pedido = true;
						}
					}
				}
			}
			if ($pedido == false) { // Si no ha encontrado un pedido de catálogo que aplicar.
				// Comprobamos si el ticket procede de un "Ticket recurrente", para ello comprobamos su nombre en la tabla "glpi_ticketrecurrents"	  	  
				$query = "SELECT glpi_tickets.id as tickets_id, 
					glpi_ticketrecurrents.id as ticketrecurents_id,
					glpi_plugin_procedimientos_procedimientos_ticketrecurrents.plugin_procedimientos_procedimientos_id as procedimientos_id,
					glpi_plugin_procedimientos_procedimientos.name
					FROM glpi_tickets, glpi_ticketrecurrents, glpi_plugin_procedimientos_procedimientos, glpi_plugin_procedimientos_procedimientos_ticketrecurrents
					where 
					glpi_ticketrecurrents.name = glpi_tickets.name 
					and glpi_plugin_procedimientos_procedimientos.id=glpi_plugin_procedimientos_procedimientos_ticketrecurrents.plugin_procedimientos_procedimientos_id 
					and glpi_plugin_procedimientos_procedimientos.active=1
					and glpi_tickets.id=".$instID;
					//echo "<br>Query t.recurrente:".$query."<br>";
					$result = $DB->query($query);
					if ($row = $DB->fetchassoc($result)) {
						$params["toadd"]= array($row["procedimientos_id"] => $row["name"]);
						$params["value"]= $row["procedimientos_id"];
						$procedimientos_id = $row["procedimientos_id"];					
					}
 
			}
			
			// Ids de los procedimientos públicos
			
			$id_publicos = get_procedimientos_publicos();
		/*	$publicos="";
			foreach ($id_publicos as $id_publico) {
				$publicos.= $id_publico.",";
			}
			$publicos.="0";*/
			
			// Grupos a los que pertenece el usuario
			$groups = Group_User::getUserGroups(Session::getLoginUserID());
			$ingrupos="(";
			foreach ($groups as $group) {
				$ingrupos.= $group["id"].",";
			}
			$ingrupos.="0)";	

	$query='select distinct plugin_procedimientos_procedimientos_id as id from glpi_plugin_procedimientos_procedimientos_groups 
							where groups_id in '.$ingrupos;
	
	$plugin_procedimientos_procedimientos_id=plugin_procedimientos_jointypes($query, $id_publicos); // [CRI] jmz18g Función con dos parametros pueden ser query o array.
	
	      $params['condition'] = [
         "active" => 1, 		 
		 'id'    => [
                  'jointype'  => $plugin_procedimientos_procedimientos_id,
               ],			  
			];
	
		/*	$params['condition'] = " active=1 and (id in (select distinct plugin_procedimientos_procedimientos_id from glpi_plugin_procedimientos_procedimientos_groups 
							where groups_id in ".$ingrupos.") or id in ".$publicos.")";*/
			$params["entity"]=-1; 	  
	  
			// Si se está ejecutando un procedimeinto en ese Ticket
			$procedimientos_id  = get_procedimiento_principal($instID);
			if ((isset($procedimientos_id )) && ($procedimientos_id >0)){
				$params["value"] = $procedimientos_id ;
			}
	
			// DESPLEGABLE PROCEDIMIENTOS // 
			echo "<tr><td class='center'><br>";
			$params["name"] = '_procedimiento';
			$params['emptylabel']  = '--- Sin procedimiento asignado ---';
			$addrand = Dropdown::show('PluginProcedimientosProcedimiento',$params);
			$params["procedimientos_id"] = '__VALUE__';
			Ajax::updateItemOnSelectEvent("dropdown__procedimiento".$addrand,"docs$rand",
                                       $CFG_GLPI["root_doc"]."/plugins/procedimientos/ajax/docs.php", $params);									   
			// BOTÓN INICIAR //
			echo "<input type='hidden' name='tickets_id' value='".$instID."'>";
			echo "     <input type='submit' name='crear' value='Actualizar' class='submit'>";
			echo "</td></tr>";
	  
			// DOCUMENTACION
			echo "<tr><td><span id='docs$rand'>"; 
			if (isset($params["value"])){ // Si hay un procedimiento seleccionado muestro su documentación
				echo "<div class='center'>";
				echo "<table class='tab_cadre_fixehov'>";
	
	
				$query = "SELECT DISTINCT `itemtype`
					FROM `glpi_documents_items`
					WHERE `items_id` = '$procedimientos_id' AND `itemtype` = 'PluginProcedimientosProcedimiento'
					ORDER BY `itemtype`";
				$result = $DB->query($query);
				$number = $DB->numrows($result);
				$i = 0;
			if (Session::isMultiEntitiesMode()) {
				$colsup = 1;
			} else {
				$colsup = 0;
			}
			
		  	$proc = new PluginProcedimientosProcedimiento();
			$proc->getFromDB($procedimientos_id);
			
			if ($number > 0) {
				echo "<tr><th colspan=5 class='center'>Documentaci&oacute;n del procedimiento '".$proc->fields['name']."'</th>";	  
				echo "<tr><th>".__('Heading')."</th>";
				echo "<th>".__('Name')."</th>";
				echo "<th>".__('Web link')."</th>";
				echo "<th>".__('File')."</th>";
				echo "<th>".__('Entity')."</th>";
				echo "</tr>";
			}		  
		  
		  
			 for ($i=0 ; $i < $number ; $i++) {
				$type = $DB->result($result, $i, "itemtype");
				if (!class_exists($type)) {
					continue;
				}
				$item = new $type();
				$column = "name";
				$query1 = "SELECT glpi_documents.*, glpi_documents_items.id AS IDD, glpi_entities.id AS entity
					FROM glpi_documents_items, glpi_documents LEFT JOIN glpi_entities ON (glpi_entities.id = glpi_documents.entities_id)
					WHERE glpi_documents.id = glpi_documents_items.documents_id
					AND glpi_documents_items.itemtype = 'PluginProcedimientosProcedimiento'
					AND glpi_documents_items.items_id = ".$procedimientos_id."
					AND glpi_documents.is_deleted = 0
					ORDER BY glpi_entities.completename, glpi_documents.name";		
			
				if ($result_linked1 = $DB->query($query1)) {
				   if ($DB->numrows($result_linked1)) {

					 $document = new Document();
					 while ($data = $DB->fetchassoc($result_linked1)) {
						 $item->getFromDB($data["id"]);
						 Session::addToNavigateListItems($type,$data["id"]);
						 $ID = "";
						 $downloadlink = NOT_AVAILABLE;
							if ($document->getFromDB($data["id"])) {
							   $downloadlink = $document->getDownloadLink();
							}						 
						 

						 if($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
							$ID = " (".$data["id"].")";
						 }

						 echo "<tr class='tab_bg_1'>";
							echo "<td class='center'>".Dropdown::getDropdownName("glpi_documentcategories",
																				 $data["documentcategories_id"]);
							echo "</td>";						  
						  

						 $nombre = $data['name'];
						 echo "<td class='center' ".
							   (isset($data['deleted']) && $data['deleted']?"class='tab_bg_2_2'":"").">".
							   $nombre."</td>";
						echo "<td class='center'>";
						if (!empty($data["link"])) {
						   echo "<a target=_blank href='".Toolbox::formatOutputWebLink($data["link"])."'>".$data["link"];
						   echo "</a>";
						} else {;
						   echo "&nbsp;";
						}
						echo "</td>";								   
						 echo "<td class='center'>$downloadlink</td>";						
						if (Session::isMultiEntitiesMode()) {
							echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
																				 $data['entity']).
								  "</td>";
						 }
						 echo "</tr>";
					  }

					}
				}
			 }
		 
			 echo "</table>";
			 echo "</div>";
			} 	  
		echo "</span></td></tr>"; // Fin Documentación	   
		echo "</table>";
		
		// Instancia del procedimiento en el ticket si se está ejecutando.
		if ((isset($procedimientos_id))&&($procedimientos_id>0)){
			
			$query = "select * from `glpi_plugin_procedimientos_procedimientos_tickets`
			  where `tickets_id`='".$instID."' order by id ;";	
			  
			$result = $DB->query($query);
			$nb = $DB->numrows($result);
			
			$proc = new PluginProcedimientosProcedimiento();
			$proc->getFromDB($procedimientos_id);
			
			echo "<div class='spaced'>";
			echo "<table class='tab_cadre_fixehov'>";
			echo "<tr><th colspan=7 class='center'>Procedimiento en ejecuci&oacute;n: '".$proc->fields['name']."'</th>";					
			$header_begin  = "<tr>";
			$header_top    = '';
			$header_bottom = '';
			$header_end    = '';	
			$header_end .= "<th>".__('Estado')."</th>";	
			$header_end .= "<th>".__('#Proc')."</th>";
			$header_end .= "<th>".__('#Linea')."</th>";				
			$header_end .= "<th>".__('Elemento')."</th>";
			$header_end .= "<th>".__('Name')."</th>";
			$header_end .= "<th>".__('Descripci&oacute;n')."</th>";
			$header_end .= "<th>".__('Inputs')."</th>";		
			$header_end .= "</tr>";
			echo $header_begin.$header_top.$header_end;
			$show_leyenda = false; // Solo muestra la leyenda si hay procedimientos anidados o elementos que están en la papelera
			
			if ($nb>0){
				$p_anidado = NULL;

			// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
			//while ($row = $DB->fetch_array($result)) {
				while ($row = $DB->fetchAssoc($result)) {																																																																						
			// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function		

					// Para distinguir procedimientos (activos y no activos/borrados)
					if ((isset($p_anidado))&&($p_anidado == $row['plugin_procedimientos_procedimientos_id'])){
						$item_anidado = 1;
					} else {
						$item_anidado = 0;
					}
					if ((isset($p_anidado_borrado))&&($p_anidado_borrado == $row['plugin_procedimientos_procedimientos_id'])){
						$item_anidado_borrado = 1;
					} else {
						$item_anidado_borrado = 0;
					}					
					// Si es un procedimiento muestro la leyenda
					if ($row['itemtype'] == 'PluginProcedimientosProcedimiento'){
						$show_leyenda = true;
					}
					// Obtengo nombre y descripción del elemento en su tabla (según su tipo)
					/* if ($_SESSION['glpi_use_mode'] == 2) {	
						echo "<br>Tipo elemento:".$row['itemtype'];
					} */
					$itemtable = getTableForItemType($row['itemtype']);
					$item_details = "SELECT `$itemtable`.name,`$itemtable`.comment";
					if (($row['itemtype'] == 'PluginProcedimientosAccion')|| ($row['itemtype'] == 'PluginProcedimientosProcedimiento')){
						$item_details = $item_details.",`$itemtable`.is_deleted";
					}
					if ($row['itemtype'] == 'PluginProcedimientosCondicion'){
					  /*$item_details = $item_details.",`$itemtable`.tag_0";
						$item_details = $item_details.",`$itemtable`.tag_1";*/
		// ========== jmz18g INFORGES CONDICIONES NUEVAS =========================						
						$item_details = $item_details.",`$itemtable`.id_1";
						$item_details = $item_details.",`$itemtable`.tag_id_1";
						$item_details = $item_details.",`$itemtable`.id_2";
						$item_details = $item_details.",`$itemtable`.tag_id_2";
						$item_details = $item_details.",`$itemtable`.id_3";
						$item_details = $item_details.",`$itemtable`.tag_id_3";
						$item_details = $item_details.",`$itemtable`.id_4";
						$item_details = $item_details.",`$itemtable`.tag_id_4";
						$item_details = $item_details.",`$itemtable`.id_5";
						$item_details = $item_details.",`$itemtable`.tag_id_5";
		// ========== jmz18g INFORGES CONDICIONES NUEVAS =========================						
					}					
					$item_details =$item_details." FROM `$itemtable` where id=".$row['items_id'].";";
					
					$result_details = $DB->query($item_details);

			// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
			 // $details = $DB->fetch_array($result_details);
				  $details = $DB->fetchAssoc($result_details);
			// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function						
	
					$name = $details["name"];
					if ($row['itemtype'] == 'PluginProcedimientosProcedimiento'){
						$name = $name." (ID: ".$row["items_id"].")";
					}					
					$comment =  html_entity_decode($details["comment"]);
					// Si es una acción o procedimiento borrado definitivamente, no muestra ese registro. 
				    if (isset($details)){	
						$elemento = nameItemtype($row["itemtype"]);
						//echo "<br>".$elemento;
						if ((isset($details['is_deleted']))&& ($details['is_deleted']==1)){
							if ($elemento == 'Procedimiento'){
								$p_anidado_borrado = $row["items_id"];
							}
							$show_leyenda = true;
							echo "<tr bgcolor='#e0dede'>"; // Linea con fondo gris para distinguir elementos no activos o en la papelera
						} else if (($elemento == 'Procedimiento')){
							$p_anidado = $row["items_id"];
							echo "<tr bgcolor='#ffead6'>"; // Linea con fondo naranja para distinguir procedimientos anidados
							
						} else if ($item_anidado == 1){
							echo "<tr bgcolor='#ffead6'>";
							
						} else if ($item_anidado_borrado == 1){
							echo "<tr bgcolor='#e0dede'>";
						} 						else {
							echo "<tr class='tab_bg_1'>";
						}							
						// Estado
						echo"<td class='center'>".
						(isset($row["state"])? "".icono_estado($row["state"])."" :"-")."</td>";
						// Procedimiento
						echo"<td class='center'>".
						(isset($row["plugin_procedimientos_procedimientos_id"])? "".$row["plugin_procedimientos_procedimientos_id"]."" :"-")."</td>";
						// Linea
						echo"<td class='center'>".
						(isset($row["line"])? "".$row["line"]."" :"-")."</td>";
						// Elemento
						echo"<td class='center'>".
						(isset($row["itemtype"])? "".$elemento."" :"-")."</td>";					  
						// Nombre
						echo "<td class='center'>";
						echo (isset($name)? "".$name."" :"-")."</td>";						
						// Descripción
						if ($elemento == 'Enlace'){
							$link = "<a href='$comment'>Abrir enlace</a>";
							echo "<td class='center'>".
							(isset($comment)? "".$link."" :"-")."</td>";		
						} else {
							echo "<td class='center'>".
							(isset($comment)? "".$comment."" :"-")."</td>";						
						}						
						//Inputs - respuestas/entradas de parte del técnico.
						echo "<td class='center' width='50px'>";
						if ($row["itemtype"] == 'PluginProcedimientosCondicion'){
							if ($row["state"] == 2 ) {
								echo "<input type='hidden' name='items_id' value='".$row["items_id"]."'>";
								echo "<input type='hidden' name='linea_condicion' value='".$row["line"]."'>";
							//	echo "<input type='submit' name='way_yes' value=\""._sx('button',$details['tag_0'])."\" class='submit'>";
							//	echo "<input type='submit' name='way_no' value=\""._sx('button',$details['tag_1'])."\" class='submit'>";
							    

								// ========== jmz18g INFORGES CONDICIONES NUEVAS =========================
								
				if (!empty($details['id_1'])) { echo "<input type='submit' name='line_id_1' value=\""._sx('button',$details['tag_id_1'])."\" class='submit'>"; }
				if (!empty($details['id_2'])) { echo "<input type='submit' name='line_id_2' value=\""._sx('button',$details['tag_id_2'])."\" class='submit'>"; }
				if (!empty($details['id_3'])) { echo "<input type='submit' name='line_id_3' value=\""._sx('button',$details['tag_id_3'])."\" class='submit'>"; }
                if (!empty($details['id_4'])) { echo "<input type='submit' name='line_id_4' value=\""._sx('button',$details['tag_id_4'])."\" class='submit'>"; }
				if (!empty($details['id_5'])) { echo "<input type='submit' name='line_id_5' value=\""._sx('button',$details['tag_id_5'])."\" class='submit'>"; }
								
								// ========== jmz18g INFORGES CONDICIONES NUEVAS =========================
								
								
							} else if ($row["instancia_id"]!= NULL){
									//echo ($row["instancia_id"]== 0? " ".$details['tag_0']." " : " ".$details['tag_1']." ");
																		
									switch ($row["instancia_id"]) {
									case 0:	
									echo $details['tag_id_1'];
									break;
									case 1:	
									echo $details['tag_id_2'];
									break;
									case 2:	
									echo $details['tag_id_3'];
									break;
									case 3:	
									echo $details['tag_id_4'];
									break;
									case 4:	
									echo $details['tag_id_5'];
									break;									
									}
									
							} else {
								echo "-";
							}
						} else if ($row["itemtype"] == 'PluginProcedimientosLink'){
							if ($row["state"] == 2 ) {
								echo "<input type='hidden' name='items_id' value='".$row["items_id"]."'>";
								echo "<input type='submit' name='checked' value=\""._sx('button','Revisado')."\" class='submit'>";
							}
						} else if ($row["itemtype"] == 'PluginProcedimientosAccion'){
							$tipo_query = "SELECT plugin_procedimientos_tipoaccions_id 
										   FROM glpi_plugin_procedimientos_accions
									       where id=".$row["items_id"].";";
							$tipo_result = $DB->query($tipo_query);
							if ($tipo_row = $DB->fetchassoc($tipo_result)) {
								if ($tipo_row['plugin_procedimientos_tipoaccions_id']== 3){									
									if ($row["state"] == 2){
										echo "<input type='hidden' name='items_id' value='".$row["items_id"]."'>";
										echo "<input type='submit' name='solved' value=\""._sx('button','He revisado ticket')."\" class='submit'>";
									}
								}
							}
						} else {
							echo "-";
						}				
						echo "</td>";
						echo "</tr>";
					}
				}
			}
			echo "</table>";
			
	
			echo "</div>";				
			// Fin instancia del procedimiento.
			
			if ($show_leyenda){ 
				echo "<div class='spaced'><table class='tab_cadre_fixehov'>";				
				// Leyendas - colores
				echo "<tr><td><img src='".$_SESSION["glpiroot"]."/plugins/procedimientos/imagenes/leyenda.png' /></td></tr>";	
				echo "</table></div>";				
			}					
		}
		Html::closeForm();
		echo "</div>";		
	  } 
   } 
   

   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAccion() {

      $forbidden   = parent::getForbiddenStandardMassiveAccion();
      $forbidden[] = 'update';
      return $forbidden;
   }
   
   
           
    /**
    * Export in an array all the data of the current instanciated ITEMS TICKETS
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $procedimiento_item = $this->fields;
      
      if (!empty($procedimiento_item['itemtype'])){
          
      $subitem="_".strtolower (explode("Procedimientos",$procedimiento_item['itemtype'])[1])."s";
      $item[$subitem] = [];
     $items_id=$procedimiento_item['items_id'];
     $itemtype=$procedimiento_item['itemtype'];
      // remove key and fk
      unset($procedimiento_item['id'],
            $procedimiento_item['plugin_procedimientos_procedimientos_id'],
            $procedimiento_item['items_id']/*,
            $procedimiento_item['itemtype']*/);

      if (is_subclass_of($itemtype, 'CommonDBTM')) {
         $procedimiento_item_obj = new $itemtype;
         if ($procedimiento_item_obj->getFromDB($items_id)) {
          $resultado=$procedimiento_item_obj->fields;
       
       
          unset($resultado['id'],
             $resultado['plugin_procedimientos_procedimientos_id'],
             $resultado['items_id']);
        
          $item[$subitem]=$resultado;
        
       if ($itemtype=="PluginProcedimientosAccion") {
              $item[$subitem]=$procedimiento_item_obj->export($remove_uuid, $item[$subitem]);
       }
       
     
 
       }
      }

      $procedimiento_item[$subitem] =  $item[$subitem];
      
      }
      
      if (empty($procedimiento_item['uuid'])) {
                 
         $procedimiento_item['uuid']=plugin_procedimientos_getUuid();

      }
      

      return $procedimiento_item;
   }

}
?>