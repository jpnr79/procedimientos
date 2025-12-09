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
class PluginProcedimientosProcedimiento extends CommonDBTM {
   
   public $dohistory=true;

   const CONFIG_PARENT   = - 2;
   
   // From CommonDBTM
   public $table            = 'glpi_plugin_procedimientos_procedimientos';
   public $type             = 'PluginProcedimientosProcedimiento';

   static $rightname = "plugin_procedimientos";
   
   public static function getTypeName($nb=0) {
      return __('Procedimiento', 'Procedimiento');
   }

   static function getIcon() {
		return "fas fa-cubes";
	}

    // Si borro un procedimiento
   function cleanDBonPurge() {
	   
	  // Borro los documentos asociados
      $temp = new Document_Item();
      $temp->deleteByCriteria(array('itemtype' => 'PluginProcedimientosProcedimiento',
                                    'items_id' => $this->fields['id'])); 
	  
	  // Borro los elementos asociados
      $temp = new PluginProcedimientosProcedimiento_Item();
      $temp->deleteByCriteria(array('plugin_procedimientos_procedimientos_id' => $this->fields['id']));	 

	  // Borro elementos o permisos creados para ese procedimiento
      $temp = new PluginProcedimientosCondicion();
      $temp->deleteByCriteria(array('plugin_procedimientos_procedimientos_id' => $this->fields['id']));
	  
      $temp = new PluginProcedimientosSalto();
      $temp->deleteByCriteria(array('plugin_procedimientos_procedimientos_id' => $this->fields['id']));	  

      $temp = new PluginProcedimientosProcedimiento_TicketRecurrent();
      $temp->deleteByCriteria(array('plugin_procedimientos_procedimientos_id' => $this->fields['id']));
	  
	  $temp = new PluginProcedimientosProcedimiento_Group();
      $temp->deleteByCriteria(array('plugin_procedimientos_procedimientos_id' => $this->fields['id']));	 
	  
	  if (plugin_procedimientos_checkForms()) { 
		$temp = new PluginProcedimientosProcedimiento_Form();
		$temp->deleteByCriteria(array('plugin_procedimientos_procedimientos_id' => $this->fields['id']));
	  }		
   }
   
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

  static function countForItem(CommonDBTM $item) {
	  global $DB;
	  	
	$query = "SELECT COUNT(*) as num_proc
				FROM (SELECT distinct `plugin_procedimientos_procedimientos_id`
				from `glpi_plugin_procedimientos_procedimientos_items`
				where `items_id`=".$item->getField('id')." and `itemtype`='".$item->getType()."') as procedimientos;";				
				
	  $result = $DB->query($query);
	  $data = $DB->fetchassoc($result);
	  return ($data['num_proc']);
	
   }   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $CFG_GLPI;

      // Can exists on template
      if (PluginProcedimientosProcedimiento::canView()) {
         switch ($item->getType()) {
			case 'PluginProcedimientosAccion' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(__('Procedimientos', 'Procedimientos'), self::countForItem($item));
               }
               return _n('Procedimientos', 'Procedimientos', Session::getPluralNumber());
			break;
			case 'PluginProcedimientosProcedimiento' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(__('Procedimientos', 'Procedimientos'), self::countForItem($item));
               }
               return _n('Procedimientos', 'Procedimientos', Session::getPluralNumber());
			break;			
         }
      }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      switch ($item->getType()) {
         case 'PluginProcedimientosAccion' :
            self::showForItem($item);
			break;
			
         case 'PluginProcedimientosProcedimiento':
            self::showForItem($item);
			break;				
      }
      return true;
   }
   
  // Pestaña "Procedimientos" en una accion o procedimiento
  static function showForItem(CommonGLPI $item, $withtemplate=0) {
      global $DB, $CFG_GLPI;
	  
      $instID = $item->fields['id'];
	  $itemtype = $item->getType();
	  
      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='3'>Procedimientos que hacen uso de ".($itemtype == 'PluginProcedimientosAccion'?" esta acci&oacute;n":" este procedimiento")."</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th>Nombre</th>";
	  echo "<th>Descripción</th>";
	  echo "<th>Activo</th>";
      echo "</tr>";	  
	  $query = "SELECT distinct `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id` as procedimientos_id,
				`glpi_plugin_procedimientos_procedimientos`.`name`,
				`glpi_plugin_procedimientos_procedimientos`.`comment`,
				`glpi_plugin_procedimientos_procedimientos`.`active`,
				`glpi_plugin_procedimientos_procedimientos`.`is_deleted`
				from `glpi_plugin_procedimientos_procedimientos_items`
				inner join `glpi_plugin_procedimientos_procedimientos` on 
					(`glpi_plugin_procedimientos_procedimientos`.id = `glpi_plugin_procedimientos_procedimientos_items`.`plugin_procedimientos_procedimientos_id`)
				where `items_id`=".$item->getField('id')." and `itemtype`='".$itemtype."';";
				 
      if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {
                  while ($data = $DB->fetchassoc($result_linked)) {
                     $linkname = $data["name"];
                     if ($_SESSION["glpiis_ids_visible"]
                         || empty($data["name"])) {
                        $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["procedimientos_id"]);
                     }

                     $link = '../front/procedimiento.form.php';
                     $name = "<a href=\"".$link."?id=".$data["procedimientos_id"]."\">".$linkname."</a>";

                     echo "<tr class='tab_bg_1'>";
                     echo "<td ".
                           (isset($data['is_deleted']) && $data['is_deleted']?"class='tab_bg_2_2'":"").
                          " >".$name."</td>";
                     echo "<td ".
                           (isset($data['is_deleted']) && $data['is_deleted']?"class='tab_bg_2_2'":"").
                          " align='left'>".$data['comment']."</td>";
					 if ($data['active']==0) {
						 $active='No';
					 } else {
						  $active='Si';
					 }
                     echo "<td ".
                           (isset($data['is_deleted']) && $data['is_deleted']?"class='tab_bg_2_2'":"").
                          " align='center'>".$active."</td>";						  
                     echo "</tr>";					 
                  }
               }
      }
      echo "</table>";
      echo "</div>";
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
	'table' => $this->getTable(),
	'field' => 'active',
	'name' => __('Activo','Activo'),
	'datatype' => 'bool',
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
  
     
   // Define pestañas de un procedimiento
  function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
	  $this->addStandardTab('PluginProcedimientosProcedimiento_Group', $ong, $options);
	  if (plugin_procedimientos_checkForms()) { 
		$this->addStandardTab('PluginProcedimientosProcedimiento_Form', $ong, $options);
	  }
	  $this->addStandardTab('PluginProcedimientosProcedimiento_TicketRecurrent', $ong, $options);  
	  $this->addStandardTab('PluginProcedimientosProcedimiento_Item', $ong, $options);
	  $this->addStandardTab('Document_Item', $ong, $options);
	  $this->addStandardTab('KnowbaseItem_Item', $ong, $options); //[CRI] JMZ18G Añadir TAB Base de conocimiento
	  $this->addStandardTab('PluginProcedimientosProcedimiento', $ong, $options);	
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }
      
  public function showForm ($ID, $options=array()) {
	global $CFG_GLPI, $DB;	
	  // In percent
      $colsize1 = '13';
      $colsize2 = '37';
	
	  $this->initForm($ID, $options);
      $this->showFormHeader($options);

	 //Nombre del Procedimientos
      echo "<tr class='tab_bg_1'>";
			echo "<th class='left'  colspan='1'>".__('Nombre','Nombre')."</th>";
			echo "<td class='left'  colspan='3'>";
            Html::autocompletionTextField($this, "name", ['option' => 'style="width:99%"', 'size' => "124"]);
			echo "</td>";
      echo "</tr>";

	  // Descripción del procedimientos
	  echo "<tr class='tab_bg_1'>";
	  echo "<th class='left'  colspan='1'>Descripción</th>";
	  echo "<td class='left' colspan='3'><textarea cols='40' style='width: 99%;' rows='4'  name='comment'>".
            $this->fields["comment"]."</textarea>";
      echo "</td></tr>";
	  
	  echo "<tr class='tab_bg_1'>";
	  echo "<th class='left'  colspan='1'>Activo</th>";
	  echo "<td class='left'  widht='10px'>";
			Dropdown::showYesNo("active",$this->fields["active"]);
      echo "</td>";
	  echo "</tr>";	  
	  
	// Ultima modificación
	echo "<tr>";
	  echo "<td class='center' colspan='4'>";
          echo '<input type="hidden" name="uuid" value="' .$this->fields['uuid'] . '">';
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
	echo "</tr>";
	  $this->showFormButtons($options);
	    
      return true;
   }
   
   static function dropdownProcedimientos($itemtype, $options=array()) {
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
      $params['emptylabel']           = '--- Sin procedimiento asignado ---';
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

// Acciones masivas
   
/*   static function showMassiveActionsSubForm(MassiveAction $ma) {
     switch ($ma->getAction()) {
			case "plugin_procedimientos_renumerar" :
				 echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
                     "value='Renumerar elementos del procedimiento'>";
				return true;
         }

      return parent::showMassiveActionsSubForm($ma);
   }*/
  
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $LANG, $DB, $CFG_GLPI;
	
	  $data =  $ma->getInput();
      switch ($ma->getAction()) {
		    // Renumerar
			case "plugin_procedimientos_renumerar" :
					foreach ($ids as $key) {
						renumerar_procedimiento($key);
						
						// Actualizo historico en el indicador.
						$msg = "Accion masiva: Renumeradas las lineas y condiciones";
						$changes[0] = 0;
						$changes[1] = '';
						$changes[2] = addslashes($msg);
						Log::history($key, 'PluginProcedimientosProcedimiento', $changes, '', Log::HISTORY_LOG_SIMPLE_MESSAGE);						 
					}
									
				 $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
				break;
                                
                          case 'Export' :
                                   foreach ($ids as $id) {
                                      if ($item->getFromDB($id)) {
                                         $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                                      } else {
                                         // Example of ko count
                                         $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                      }
                                   }
                                   echo "<br>";
                                   echo "<div class='center'>";
                                   echo "<a href='#' onclick='window.history.back()'>".__("Back")."</a>";
                                   echo "</div>";

                                   $listOfId = array('plugin_procedimientos_procedimientos_id' => array_values($ids));
                                  // echo var_dump($listOfId);
                                   Html::redirect($CFG_GLPI['root_doc'] . "/plugins/procedimientos/front/export.php?".Toolbox::append_params($listOfId));
                                   header("Content-disposition:attachment filename=\"test\"");
                                   return;
   }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }   

      /**
    * Export in an array all the data of the current instanciated form
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   function export($remove_uuid = false) {
       
       global $DB;
       
      if (!$this->getID()) {
         return false;
      }
$ID=$this->getID();
            unset($this->fields['id']);
			
$params = [
"plugin_procedimientos_procedimientos_id" => $ID,
];			
      
      $procedimiento           = $this->fields;
      $procedimiento_item    = new PluginProcedimientosProcedimiento_Item;
      $procedimiento_form   = new PluginProcedimientosProcedimiento_Form; 
      $procedimiento_group   = new PluginProcedimientosProcedimiento_Group; 
     // $procedimiento_ticket   = new PluginProcedimientosProcedimiento_Ticket; 
	  $procedimiento_TicketRecurrent   = new PluginProcedimientosProcedimiento_TicketRecurrent; 
     //$procedimiento_Documen   = new Document_Item; 
      $procedimiento_LOG   = new Log; 
      
      
            if ($procedimiento['entities_id'] > 0) {
         $procedimiento['_entity']
            = Dropdown::getDropdownName('glpi_entities',
                                        $procedimiento['entities_id']);
      }
      
       // get items
      $procedimiento['_items'] = [];
      $all_items = $procedimiento_item->find($params);
      foreach ($all_items as $items_id => $item) {
     //echo var_dump($all_items);
        //echo $items_id;     
            $procedimiento_item->getFromDB($items_id);
            $procedimiento['_items'][] = $procedimiento_item->export($remove_uuid);
      }     
      
       // get FORMS
      $procedimiento['_forms'] = [];
      $all_forms = $procedimiento_form->find($params);
      foreach ($all_forms as $plugin_formcreator_forms_id => $form) {
     //echo var_dump($all_items);
        //echo $items_id;     
            $procedimiento_form->getFromDB($plugin_formcreator_forms_id);
            $procedimiento['_forms'][] = $procedimiento_form->export($remove_uuid);
      }    
      
      // get GROUPS 
      $procedimiento['_groups'] = [];
      $all_groups = $procedimiento_group->find($params);
      foreach ($all_groups as $groups_id => $group) {
     //echo var_dump($all_items);
        //echo $items_id;     
            $procedimiento_group->getFromDB($groups_id);
            $procedimiento['_groups'][] = $procedimiento_group->export($remove_uuid);
      }   

 // get TICKETS 
   /*   $procedimiento['_tickets'] = [];
      $all_tickets = $procedimiento_ticket->find($params);
      foreach ($all_tickets as $items_id => $group) {
     //echo var_dump($all_items);
        //echo $items_id;     
            $procedimiento_ticket->getFromDB($items_id);
            $procedimiento['_tickets'][] = $procedimiento_ticket->export($remove_uuid);
      }  */           

	   // get TICKETS RECURRENTES
     $procedimiento['_tickets_recurrent'] = [];
      $all_tickets = $procedimiento_TicketRecurrent->find($params);
      foreach ($all_tickets as $items_id => $group) {
     //echo var_dump($all_items);
        //echo $items_id;     
            $procedimiento_TicketRecurrent->getFromDB($items_id);
            $procedimiento['_tickets_recurrent'][] = $procedimiento_TicketRecurrent->export($remove_uuid);
      }  
	  

	  
      // get DOCUMENTS 
    /*  $procedimiento['_documents_items'] = [];
	
		  $params = [
"items_id" => $ID,
"itemtype" => 'PluginProcedimientosProcedimiento',
];
	
      $all_documents = $procedimiento_Documen->find($params);
      
            foreach ($all_documents as $valor) {
            unset($valor["id"],
                  $valor["items_id"]);
            $procedimiento['_documents_items'][] = $valor;
      }  */ 

      // get LOGS 
      $procedimiento['_logs'] = [];
	  
	  $array = array('811', '812', '3', '4', '815');
 $questionIds = implode("', '", array_keys($array));
		  $params = [
"items_id" => $ID,
'itemtype' => ['LIKE', '%procedi%'],
];	  

	  $all_LOGS = $procedimiento_LOG->find($params);
      
            foreach ($all_LOGS as $valor) {
            unset($valor["id"],
                  $valor["items_id"]);
            $procedimiento['_logs'][] = $valor;
      }      
      
      if (empty($procedimiento['uuid'])) {
                 
         $procedimiento['uuid']=plugin_procedimientos_getUuid();

      }

      return $procedimiento;
  
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

                                      }
?>