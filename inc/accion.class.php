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

// Class of the defined type
class PluginProcedimientosAccion extends CommonDBTM {
   
  // public $dohistory=true;

   const CONFIG_PARENT   = - 2;
   
   // From CommonDBTM
   public $table            = 'glpi_plugin_procedimientos_accions';
   public $type             = 'PluginProcedimientosAccion';

   static $rightname = "plugin_procedimientos";

   // Permisos
   public static function canCreate(): bool {

      return (Session::haveRight(self::$rightname, CREATE));
   }

   public static function canView(): bool {

      return (Session::haveRight(self::$rightname, READ));
   }

   public function canViewItem(): bool {

      return (Session::haveRight(self::$rightname, READ));
   }

   public function canCreateItem(): bool {
     return (Session::haveRight(self::$rightname, CREATE));
   }

   public function canUpdateItem(): bool {

      return (Session::haveRight(self::$rightname, UPDATE));
   }

   public function canPurgeItem(): bool {

      return (Session::haveRight(self::$rightname, PURGE));
   }

   public static function canUpdate(): bool {
     return (Session::haveRight(self::$rightname, UPDATE));
   }

  public static function canPurge(): bool {
      return (Session::haveRight(self::$rightname, PURGE));
   }   
  
   static function getTypeName($nb=0) {
		return _n('Acciones','Acciones',$nb, 'accions');
   }    
   
   static function getIcon() {
		return "fas fa-check-double";
	 }

   // Si borro una acción
   function cleanDBonPurge() {
	   global $DB;
	   
	  // Borro los elementos asociados
      $temp = new PluginProcedimientosProcedimiento_Item();
      $temp->deleteByCriteria(array('itemtype' => 'PluginProcedimientosAccion',
                                    'items_id' => $this->fields['id'])); 

	  // Borro detalles de la acción en la tabla correspondiente	  
	  if(isset($this->fields["plugin_procedimientos_tipoaccions_id"])){    
		switch ($this->fields["plugin_procedimientos_tipoaccions_id"]){
			case 1:  //Tarea
				$select = "DELETE FROM `glpi_plugin_procedimientos_tareas` WHERE `plugin_procedimientos_accions_id`=".$this->fields["id"];
				$result = $DB->query($select );
				break;
				
			case 2: //Escalado
				$select = "DELETE FROM `glpi_plugin_procedimientos_escalados` WHERE `plugin_procedimientos_accions_id`=".$this->fields["id"];
				$result = $DB->query($select );	
				break;			
			
			case 3: // Modificación ticket
				$select = "DELETE FROM `glpi_plugin_procedimientos_updatetickets` WHERE `plugin_procedimientos_accions_id`=".$this->fields["id"];
				$result = $DB->query($select );
				break;				
			
			case 4: // Seguimiento
				$select = "DELETE FROM `glpi_plugin_procedimientos_seguimientos` WHERE `plugin_procedimientos_accions_id`=".$this->fields["id"];
				$result = $DB->query($select );
			break;				
		}
	  }
	  
	  // Borro las instancias con referencia a esa acción
	  $temp = new PluginProcedimientosProcedimiento_Ticket();
      $temp->deleteByCriteria(array('itemtype' => 'PluginProcedimientosAccion',
                                    'items_id' => $this->fields['id'])); 	  
   }

   
/**
    * Get search function for the class
    *
    * @return array of search option
   **/
   
	 function rawSearchOptions() {

	$tab = [];

	$tab = array_merge($tab, parent::rawSearchOptions());
	
	$tab[] = [
	'id' => '101',
	'table' => $this->getTable(),
	'field' => 'comment',
	'name' => __('Descripcion','Descripcion'),
	'datatype' => 'text',
	'massiveaction' => false,
	];
	
	$tab[] = [
	'id' => '102',
	'table' => 'glpi_plugin_procedimientos_tipoaccions',
	'field' => 'name',
	'name' => __('Tipo','Tipo'),
	'datatype' => 'dropdown',
	'massiveaction' => false,
	];	
	
	$tab[] = [
	'id' => '103',
	'table' => 'glpi_entities',
	'field' => 'completename',
	'name' => _n('Entity', 'Entities', 1),
	'datatype' => 'dropdown',
	'massiveaction' => true,
	];	

	
	return $tab;

	}      


   
      //define header form
  function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginProcedimientosAccion', $ong, $options);
      $this->addStandardTab('PluginProcedimientosAcciondetalle', $ong, $options);
	   $this->addStandardTab('PluginProcedimientosProcedimiento', $ong, $options);	  
	   $this->addStandardTab('PluginProcedimientosTarea', $ong, $options);	
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }
//[INICIO] [CRI] JMZ18G para incluir imagenes pegadas en texto enriquecido.
   function post_updateItem($history = 1) { 
      $this->input = $this->addFiles($this->input, ['force_update' => true,  'content_field' => 'comment']);
   }

	function post_addItem() {
      $this->input = $this->addFiles($this->input, ['force_update' => true,  'content_field' => 'comment']);
	}	 
//[FINAL] [CRI] JMZ18G para incluir imagenes pegadas en texto enriquecido.	    

  public function showForm ($ID, $options=array()) {
	global $CFG_GLPI, $DB;
	
	  // In percent
      $colsize1 = '13';
      $colsize2 = '37';
	  
	  $this->initForm($ID, $options);
      $this->showFormHeader($options);
	  
	 //Nombre de la accion
      echo "<tr class='tab_bg_1'>";
			echo "<th class='left'  colspan='1'>".__('Nombre','Nombre')."</th>";
			echo "<td class='left'  colspan='3'>";

         Html::autocompletionTextField($this, "name", ['option' => 'style="width:99%"', 'size' => "124"]);

			echo "</td>";
      echo "</tr>";

	  // Descripción de la accion
	  echo "<tr class='tab_bg_1'>";
	  echo "<th class='left'  colspan='1'>Descripción</th>";
   // [INICIO] [CRI] JMZ18G 15/03/2021 PONER TEXTO ENRIQUECIDO EN EL CAMPO DESCRIPCIÓN	  
/*	  echo "<td class='left' colspan='3'><textarea cols='125' rows='3' name='comment'>".
            $this->fields["comment"]."</textarea>";*/
	  echo "<td class='left' colspan='3'>";	

	   $rand       = mt_rand();
      $rand_text  = mt_rand();
      $content_id = "comment$rand_text";
      $cols       = 90;
      $rows       = 15;	  

      $content = $this->fields['comment'];     

      Html::initEditorSystem("$content_id");
		
      Html::textarea(['name'            	=> 'comment',
                      'value'           	=> $content,
                      'rand'            	=> $rand_text,
                      'editor_id'       	=> $content_id,
                      'enable_richtext' 	=> true,
							 'enable_fileupload' => true,
                      'cols'            	=> $cols,
                      'rows'            	=> $rows]);

			
      echo "</td></tr>";
	  // [FINAL] [CRI] JMZ18G 15/03/2021 PONER TEXTO ENRIQUECIDO EN EL CAMPO DESCRIPCIÓN
	  
	  // Tipo: Tarea, Escalado, Seguimiento, Modificar ticket
	  echo "<tr class='tab_bg_1'>";
	  echo "<th class='left'  colspan='1'>Tipo</th>";
	  echo "<td class='left'  widht='10px'>";
	  
      Dropdown::show('PluginProcedimientosTipoaccion', array('name' =>'plugin_procedimientos_tipoaccions_id',
					       'value'  => $this->fields["plugin_procedimientos_tipoaccions_id"]));
						      
      echo "</td>";
	  echo "</tr>";	  
	  
	// Ultima modificación
	echo "<tr>";
	  echo "<td class='center' colspan='4'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
	echo "</tr>";
	  $this->showFormButtons($options);
	    
      return true;
   }

   static function dropdownAcciones($itemtype, $options=array()) {
      global $DB, $CFG_GLPI;

      if ($itemtype && !($item = getItemForItemtype($itemtype))) {
         return false;
      }

      $table = $item->getTable();

      $params['name']                 = $item->getForeignKeyField();
      $params['value']                = (($itemtype == 'Entity') ? $_SESSION['glpiactive_entity'] : '');
      $params['comments']             = true;
      $params['entity']               = -1;
      $params['entity_sons']          = false;
      $params['toupdate']             = '';
      $params['width']                = '80%';
      $params['used']                 = array();
      $params['toadd']                = array();
      $params['on_change']            = '';
      $params['condition']            = '';
      $params['rand']                 = mt_rand();
      $params['displaywith']          = array();
      $params['emptylabel']           = '---';
      $params['display_emptychoice']  = ($itemtype != 'Entity');
      $params['display']              = true;
      $params['permit_select_parent'] = false;
      $params['addicon']              = true;
      $params['specific_tags']        = array();

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }
      $output       = '';
      $name         = $params['emptylabel'];
      $comment      = "";
      $limit_length = 100;

      // Check default value for dropdown : need to be a numeric
      if ((strlen($params['value']) == 0) || !is_numeric($params['value'])) {
         $params['value'] = 0;
      }

      if (isset($params['toadd'][$params['value']])) {
         $name = $params['toadd'][$params['value']];
      } else if (($params['value'] > 0)
                 || (($itemtype == "Entity")
                     && ($params['value'] >= 0))) {
         $tmpname = self::getDropdownName($table, $params['value'], 1);

         if ($tmpname["name"] != "&nbsp;") {
            $name    = $tmpname["name"];
            $comment = $tmpname["comment"];

            if (Toolbox::strlen($name) > 100) {
               if ($item instanceof CommonTreeDropdown) {
                  $pos          = strrpos($name, ">");
                  $limit_length = max(Toolbox::strlen($name) - $pos,
                                      100);

                  if (Toolbox::strlen($name) > $limit_length) {
                     $name = "...".Toolbox::substr($name, -$limit_length);
                  }

               } else {
                  $limit_length = Toolbox::strlen($name);
               }

            } else {
               $limit_length = 100;
            }
         }
      }

      // Manage entity_sons
      if (!($params['entity'] < 0)
          && $params['entity_sons']) {
         if (is_array($params['entity'])) {
            // translation not needed - only for debug
            $output .= "entity_sons options is not available with entity option as array";
         } else {
            $params['entity'] = getSonsOf('glpi_entities',$params['entity']);
         }
      }


      $field_id = Html::cleanId("dropdown_".$params['name'].$params['rand']);

      // Manage condition
      if (!empty($params['condition'])) {
        $params['condition'] = static::addNewCondition($params['condition']);
      }

      if (!$item instanceof CommonTreeDropdown) {
         $name = Toolbox::unclean_cross_side_scripting_deep($name);
      }
      $p = array('value'                => $params['value'],
                 'valuename'            => $name,
                 'width'                => $params['width'],
                 'itemtype'             => $itemtype,
                 'display_emptychoice'  => $params['display_emptychoice'],
                 'displaywith'          => $params['displaywith'],
                 'emptylabel'           => $params['emptylabel'],
                 'condition'            => $params['condition'],
                 'used'                 => $params['used'],
                 'toadd'                => $params['toadd'],
                 'entity_restrict'      => $params['entity'],
                 'limit'                => $limit_length,
                 'on_change'            => $params['on_change'],
                 'permit_select_parent' => $params['permit_select_parent'],
                 'specific_tags'        => $params['specific_tags'],
                );

      $output = Html::jsAjaxDropdown($params['name'], $field_id,
                                     $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
                                     $p);
      // Display comment
      if ($params['comments']) {
         $comment_id      = Html::cleanId("comment_".$params['name'].$params['rand']);
         $link_id         = Html::cleanId("comment_link_".$params['name'].$params['rand']);
         $options_tooltip = array('contentid' => $comment_id,
                                  'linkid'    => $link_id,
                                  'display'   => false);

         if ($item->canView()) {
             if ($params['value']
                 && $item->getFromDB($params['value'])
                 && $item->canViewItem()) {
               $options_tooltip['link']       = $item->getLinkURL();
            } else {
               $options_tooltip['link']       = $item->getSearchURL();
            }
            $options_tooltip['linktarget'] = '_blank';
         }

         $output .= "&nbsp;".Html::showToolTip($comment,$options_tooltip);

         if ( $item->canCreate() && $params['addicon']) {

               $output .= "<img alt='' title=\"".__s('Add')."\" src='".$CFG_GLPI["root_doc"].
                            "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                            onClick=\"".Html::jsGetElementbyID('add_dropdown'.$params['rand']).".dialog('open');\">";
               $output .= Ajax::createIframeModalWindow('add_dropdown'.$params['rand'],
                                                        $item->getFormURL(),
                                                        array('display' => false));
         }
         $paramscomment = array('value' => '__VALUE__',
                                'table' => $table);
         if ($item->canView()) {
            $paramscomment['withlink'] = $link_id;
         }

         $output .= Ajax::updateItemOnSelectEvent($field_id, $comment_id,
                                                  $CFG_GLPI["root_doc"]."/ajax/comments.php",
                                                  $paramscomment, false);
      }
      $output .= Ajax::commonDropdownUpdateItem($params, false);
      if ($params['display']) {
         echo $output;
         return $params['rand'];
      }
      return $output;
   }

  /**
    * Add new condition
    *
    * @todo should not use session to pass query parameters...
    *
    * @param array $condition Condition to add
    *
    * @return string
    */
   static function addNewCondition($condition) {
      if (!is_array($condition)) {
         Toolbox::deprecated('Using a string in dropdown condition is deprecated.');
         $condition = Toolbox::cleanNewLines($condition);
         $sha1 = sha1($condition);
      } else {
         $sha1 = sha1(serialize($condition));
      }

      $_SESSION['glpicondition'][$sha1] = $condition;
      return $sha1;
   }	
   
     /*     static function addNewCondition($condition) {
        $condition = Toolbox::cleanNewLines($condition);
        $sha1=sha1($condition);
        $_SESSION['glpicondition'][$sha1] = $condition;
        return $sha1;
    }
	*/
	
    
/**
    * Export in an array all the data of the current instanciated accion
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
    
    
public function export($remove_uuid = false, $item) {
   
             
    if (!$this->getID()) {
         return false;
      }
        						
      	$query = "Select * from `glpi_plugin_procedimientos_seguimientos` where plugin_procedimientos_accions_id = ".$this->getID().";";
        
$item=self::consulta($query,"_seguimientos", $item);

        $query = "Select * from glpi_plugin_procedimientos_validacions where plugin_procedimientos_accions_id = ".$this->getID().";";

 $item=self::consulta($query,"_validacions", $item);
 
       $query = "Select * from `glpi_plugin_procedimientos_escalados` WHERE `plugin_procedimientos_accions_id`=".$this->getID().";";
 
 $item=self::consulta($query,"_escalados", $item);
 
        $query = "Select * FROM `glpi_plugin_procedimientos_updatetickets` WHERE `plugin_procedimientos_accions_id`=".$this->getID().";";
 
 $item=self::consulta($query,"_updatetickets", $item);
 
        $query = "Select * FROM `glpi_plugin_procedimientos_tareas` WHERE `plugin_procedimientos_accions_id`=".$this->getID().";";
 
 $item=self::consulta($query,"_tareas", $item);
 
    return $item;
      
   }
   
   
 public function consulta($query, $nombre, $data) {
    global $DB;
   // echo $query;
                  
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {      
            while ($line = $DB->fetchassoc($result)) {           
               unset($line["id"],
                     //$line["plugin_procedimientos_accions_id"],
                     $line["plugin_procedimientos_accions_id"]);

					 // ============================ JMZ18G Exportación de  documentos asociados a tareas y seguimientos ===============================
					 
					 if (($nombre=="_tareas") or ($nombre=="_seguimientos")) {
						 
						 $query_documents = "Select a.documentcategories_id, a.documents_id, a.itemtype, a.uuid, b.tag FROM `glpi_plugin_procedimientos_documents` a inner join glpi_documents b on a.documentcategories_id=b.documentcategories_id and b.id=a.documents_id WHERE a.`items_id`=".$this->getID().";";

						  if ($result2 = $DB->query($query_documents)) {
						          if ($DB->numrows($result2)) {      
            while ($documents = $DB->fetchassoc($result2)) {
			
			$line["_documents"][]=$documents; 
			
			}  }  }	 }

					// ============================ JMZ18G Exportación de  documentos asociados a tareas y seguimientos ===============================					
					 
                $data[$nombre] = $line;               
            }
         } else {
          
            // $data[$nombre]= array(); 
             
         }
      
      }
      
      return $data;
     
 }
 
  public function existe($query) {
    global $DB;
  //  echo "<br>".$query."<br>";
                  
      if ($result = $DB->query($query)) {
	
         if ($DB->numrows($result)) {      
		
			return $DB->numrows($result);	
			
         } else {
          
            return 0;
             
         }
      
      } else {
		  
		    return 0;
		  
	  }  }
	  
  public function documents($documentos, $items_id) {
    global $DB;
  //  echo "<br>".$query."<br>";

foreach ($documentos as $documento){

$query= "select id,
(SELECT uuid FROM glpi_plugin_procedimientos_documents where uuid ='".$documento["uuid"]."') as uuid,
(Select tag from glpi_documents where tag ='".$documento["tag"]."') as tag 
from glpi_documents where id = 1";

 $result = $DB->query($query);
 
 $item=self::consulta($query,"documentos", "");

 /* echo "uuid: ".$item["documentos"]["uuid"]."<br>";
    echo "tag: ".$item["documentos"]["tag"]."<br>";  */

if ((is_null($item["documentos"]["uuid"])) and (!is_null($item["documentos"]["tag"])))  {

 $query= "INSERT INTO `glpi_plugin_procedimientos_documents` (`documentcategories_id`, `documents_id`, `itemtype`, `uuid`, `items_id`)
	       VALUES (".$documento["documentcategories_id"].",
		   ".$documento["documents_id"].",
		   '".$documento["itemtype"]."',
		   '".$documento["uuid"]."',
		   ".$items_id.")";
		   
  //echo $query."<br>";	 
 
 $result = $DB->query($query);		  
	
}

	}	  

 }	  
 
       /**
    * Prepare input datas for updating the form
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input) {
      // Decode (if already encoded) and encode strings to avoid problems with quotes
        // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_procedimientos_getUuid();
      }

      return $input;
   }
 
   
    /**
    * Import accion into the db
    * @see PluginProcedimientosProcedimiento_Item::import
    *
    * @param  integer $items_id  id of the parent section
    * @param  array   $accion the question data (match the question table)
    * @return integer the question's id
    */
   public function import($accion = array()) {
        global $DB;
           
            if (isset($accion["items_id"])
          && isset($accion["_accions"]["_seguimientos"])) {					 
                             
                   $seguimiento=$accion["_accions"]["_seguimientos"];
         $query= "INSERT INTO `glpi_plugin_procedimientos_seguimientos` (`plugin_procedimientos_accions_id`, `content`, `is_private`, `requesttypes_id`, `followuptypes_id` )
	       VALUES (".$accion["items_id"].",'".$seguimiento["content"]."',".$seguimiento["is_private"].",".$seguimiento["requesttypes_id"].",".$seguimiento["followuptypes_id"].");";
         
         $result = $DB->query($query);
		 
		 	              if (isset($accion["_accions"]["_seguimientos"]["_documents"])) {	
				  
		 $documentos=$accion["_accions"]["_seguimientos"]["_documents"];
	 
		self::documents($documentos, $accion["items_id"]);  
		
	  }
                   
      } 
      
            if (isset($accion["items_id"])
          && isset($accion["_accions"]["_validacions"])) {

             $validacions=$accion["_accions"]["_validacions"];
      $query= "INSERT INTO `glpi_plugin_procedimientos_validacions` (`plugin_procedimientos_accions_id`, `comment_submission`, `groups_id`, `users_id_validate`)
					         VALUES (".$accion["items_id"].",'".$validacions["comment_submission"]."',".$validacions["groups_id"].",".$validacions["users_id_validate"].");";
      $result = $DB->query($query);                                               
         
                
      } 
      
            if (isset($accion["items_id"])
          && isset($accion["_accions"]["_escalados"])) {

                 $escalados=$accion["_accions"]["_escalados"];
        $query= "INSERT INTO `glpi_plugin_procedimientos_escalados` 
		(`plugin_procedimientos_accions_id`, `users_id_asignado`, `groups_id_asignado`, `users_id_observ`, `groups_id_observ`, `suppliers_id`)
		 VALUES (".$accion["items_id"].",".$escalados["users_id_asignado"].",".$escalados["groups_id_asignado"].",".$escalados["users_id_observ"].",".$escalados["groups_id_observ"].",".$escalados["suppliers_id"].");";
		
        $result = $DB->query($query);                                            
         
                
      }   
      
            if (isset($accion["items_id"])
          && isset($accion["_accions"]["_updatetickets"])) {

                 $updatetickets=$accion["_accions"]["_updatetickets"];
      $query= "INSERT INTO `glpi_plugin_procedimientos_updatetickets` (`plugin_procedimientos_accions_id`, `requesttypes_id`, `status`, `itilcategories_id`, `type`, `slts_ttr_id`, `solutiontemplates_id`)
               VALUES (".$accion["items_id"].",".$updatetickets["requesttypes_id"].",".$updatetickets["status"].",".$updatetickets["itilcategories_id"].",".$updatetickets["type"].",".$updatetickets["slts_ttr_id"].",".$updatetickets["solutiontemplates_id"].");";

      $result = $DB->query($query);                                           
         
                
      }                  

            if (isset($accion["items_id"])
          && isset($accion["_accions"]["_tareas"])) {

                 $tareas=$accion["_accions"]["_tareas"];
	$query= "INSERT INTO `glpi_plugin_procedimientos_tareas` (`plugin_procedimientos_accions_id`, `taskcategories_id`)
	         VALUES (".$accion["items_id"].",".$tareas["taskcategories_id"].");";
	
        $result = $DB->query($query);     

	              if (isset($accion["_accions"]["_tareas"]["_documents"])) {	
				  
		 $documentos=$accion["_accions"]["_tareas"]["_documents"];
		 
		 self::documents($documentos, $accion["items_id"]);  		  
		
	  } 		
         
     // exit();          
      }          
   

   }
   
   
      public function update_detalle($accion = array()) {

	      global $DB;
		  
		$item_id = intval($accion["_accions"]["id"]);
		$procedimiento_id = intval($accion["plugin_procedimientos_procedimientos_id"]);
	  
		if (isset($accion["_accions"]["_seguimientos"])) {
			  
		$seguimiento=$accion["_accions"]["_seguimientos"];
		
		$query= "select id from glpi_plugin_procedimientos_seguimientos where plugin_procedimientos_accions_id='".$item_id."'";
		
		$existe=self::existe($query);
		
		if ($existe>0) {		
		
         $query= "UPDATE `glpi_plugin_procedimientos_seguimientos` SET `users_id`= ".$seguimiento["users_id"].",
		 `content`='".$seguimiento["content"]."', 
		 `is_private`='".$seguimiento["is_private"]."', 
		 `requesttypes_id`='".$seguimiento["requesttypes_id"]."', 
		 `followuptypes_id`='".$seguimiento["followuptypes_id"]."',
		 `tag`='".$seguimiento["tag"]."', 
		 `filename`='".$seguimiento["filename"]."'
		 where plugin_procedimientos_accions_id='".$item_id."'";
         
		} else {
		 		 
		           $seguimiento=$accion["_accions"]["_seguimientos"];
         $query= "INSERT INTO `glpi_plugin_procedimientos_seguimientos` 
		 (`plugin_procedimientos_accions_id`, `content`, `is_private`, `requesttypes_id`, `followuptypes_id` )
	     VALUES (".$item_id.",'".$seguimiento["content"]."',".$seguimiento["is_private"].",".$seguimiento["requesttypes_id"].",".$seguimiento["followuptypes_id"].");";         		
			  
		}

			// echo $query."<br>";
			 	
		 $result = $DB->query($query);	
		 
	              if (isset($accion["_accions"]["_seguimientos"]["_documents"])) {	
				  
		 $documentos=$accion["_accions"]["_seguimientos"]["_documents"];
	 
	 		self::documents($documentos, $item_id);  				  
		
	  }
//exit(); 
	  }
 
 
    if (isset($accion["_accions"]["_tareas"])) {
			  
		$tarea=$accion["_accions"]["_tareas"];
		
		$query= "select id from glpi_plugin_procedimientos_tareas where plugin_procedimientos_accions_id='".$item_id."'";
		
		$existe=self::existe($query);
		
		if ($existe>0) {
		
 $query= "UPDATE `glpi_plugin_procedimientos_tareas` SET 
		 `taskcategories_id`='".$tarea["taskcategories_id"]."', 
		 `users_id_tech`='".$tarea["users_id_tech"]."', 
		 `groups_id_tech`='".$tarea["groups_id_tech"]."', 
		 `is_private`='".$tarea["is_private"]."',
		 `state`='".$tarea["state"]."', 
		 `tasktemplates_id`='".$tarea["tasktemplates_id"]."'
		 where plugin_procedimientos_accions_id='".$item_id."'";
			
		} else {
		
		           $tarea=$accion["_accions"]["_tareas"];
         $query= "INSERT INTO `glpi_plugin_procedimientos_tareas` 
		 (`plugin_procedimientos_accions_id`, `users_id_tech`, `state`, `tasktemplates_id`, `taskcategories_id`, `groups_id_tech`, `is_private` )
	     VALUES (".$item_id.",".$tarea["users_id_tech"].",".$tarea["state"].",".$tarea["tasktemplates_id"].",".$tarea["taskcategories_id"].",".$tarea["groups_id_tech"].",".$tarea["is_private"].");";
         			
		}	
		
		//echo "<br><br><br>numero: ".$existe."<br><br><br>".$query."<br><br><br>";

		$result = $DB->query($query);			
	 
	              if (isset($accion["_accions"]["_tareas"]["_documents"])) {	
				  
		 $documentos=$accion["_accions"]["_tareas"]["_documents"];
		 
		 self::documents($documentos, $item_id);
		 
	  } 
//exit(); 
	  }
 
 
 if (isset($accion["_accions"]["_validacions"])) {
			  
		$validacion=$accion["_accions"]["_validacions"];
		
		$query= "select id from glpi_plugin_procedimientos_validacions where plugin_procedimientos_accions_id='".$item_id."'";
		
		$existe=self::existe($query);
		
		if ($existe>0) {		
		
         $query= "UPDATE `glpi_plugin_procedimientos_validacions` SET 
		 `groups_id`=".$validacion["groups_id"].", 
		 `users_id_validate`=".$validacion["users_id_validate"].", 
		 `comment_submission`='".$validacion["comment_submission"]."'
		 where plugin_procedimientos_accions_id=".$item_id."";       
		 
		} else {
		 
         $query= "INSERT INTO `glpi_plugin_procedimientos_validacions` 
		 (`plugin_procedimientos_accions_id`, `groups_id`, `users_id_validate`, `comment_submission` )
	     VALUES (".$item_id.",".$validacion["groups_id"].",".$validacion["users_id_validate"].",'".$validacion["comment_submission"]."');";
         					 
		}
	//	echo "<br><br><br>numero ".$existe."<br><br><br>".$query."<br><br><br>";
			 
			 		  $result = $DB->query($query);

//exit();			 
 
 
}

 if (isset($accion["_accions"]["_escalados"])) {
			  
		$validacion=$accion["_accions"]["_escalados"];
		
		$query= "select id from glpi_plugin_procedimientos_escalados where plugin_procedimientos_accions_id='".$item_id."'";
		
		$existe=self::existe($query);
		
		if ($existe>0) {		
		//[INICIO] [CRI] JMZ18G ERROR SQL CUANDO suppliers_id ES NULL. PASAMOS NULL A ENTERO CON intval()
         $query= "UPDATE `glpi_plugin_procedimientos_escalados` SET 
		 `users_id_asignado`=".intval($validacion["users_id_asignado"]).", 
		 `groups_id_asignado`=".intval($validacion["groups_id_asignado"]).", 
		 `users_id_observ`=".intval($validacion["users_id_observ"]).",
		 `groups_id_observ`=".intval($validacion["groups_id_observ"]).",
		 `suppliers_id`=".intval($validacion["suppliers_id"])."
		 where plugin_procedimientos_accions_id=".$item_id."";       
		 
		} else { 
					 
         $query= "INSERT INTO `glpi_plugin_procedimientos_escalados` 
		 (`plugin_procedimientos_accions_id`, `users_id_asignado`, `groups_id_asignado`, `users_id_observ`, groups_id_observ, suppliers_id)
	     VALUES (".$item_id.",".intval($validacion["users_id_asignado"]).",".intval($validacion["groups_id_asignado"]).",".intval($validacion["users_id_observ"]).",".intval($validacion["groups_id_observ"]).",".intval($validacion["suppliers_id"]).");";
       //[FINAL] [CRI] JMZ18G ERROR SQL CUANDO suppliers_id ES NULL. PASAMOS NULL A ENTERO CON intval()  					 
		}
	//	echo "<br><br><br>numero ".$existe."<br><br><br>".$query."<br><br><br>";
			 
			 		  $result = $DB->query($query);

//exit();			 
 
 
} 


if (isset($accion["_accions"]["_updatetickets"])) {
			  
		$validacion=$accion["_accions"]["_updatetickets"];
		
		$query= "select id from glpi_plugin_procedimientos_updatetickets where plugin_procedimientos_accions_id='".$item_id."'";
		
		$existe=self::existe($query);
		
		if ($existe>0) {		
		
         $query= "UPDATE `glpi_plugin_procedimientos_updatetickets` SET 
		 `requesttypes_id`=".$validacion["requesttypes_id"].", 
		 `status`=".$validacion["status"].", 
		 `itilcategories_id`=".$validacion["itilcategories_id"].",
		 `type`=".$validacion["type"].",
		 `slts_ttr_id`=".$validacion["slts_ttr_id"].",
		 `solutiontemplates_id`=".$validacion["solutiontemplates_id"]."
		 where plugin_procedimientos_accions_id=".$item_id."";       
		 
		} else { 
					 
         $query= "INSERT INTO `glpi_plugin_procedimientos_updatetickets` 
		 (`plugin_procedimientos_accions_id`, `requesttypes_id`, `status`, `itilcategories_id`, type, slts_ttr_id, solutiontemplates_id)
	     VALUES (".$item_id.",".$validacion["requesttypes_id"].",".$validacion["status"].",".$validacion["itilcategories_id"].",".$validacion["type"].",".$validacion["slts_ttr_id"].",".$validacion["solutiontemplates_id"].");";
         					 
		}
	//	echo "<br><br><br>numero ".$existe."<br><br><br>".$query."<br><br><br>";
			 
			 		  $result = $DB->query($query);

//exit();			 
 
 
} 

 
}  


}
    
?>