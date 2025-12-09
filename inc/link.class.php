<?php
/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Martínez Ballesta.
   Fecha: Noviembre 2016

   ----------------------------------------------------------
 */
include_once (GLPI_ROOT . "/plugins/procedimientos/inc/function.procedimientos.php");

if (!defined('GLPI_ROOT')) {
        die("Sorry. You can't access directly to this file");
}

// Class of the defined type
class PluginProcedimientosLink extends CommonDBTM {
   
   public $dohistory=true;

   const CONFIG_PARENT   = - 2;
   
   // From CommonDBTM
   public $table            = 'glpi_plugin_procedimientos_links';
   public $type             = 'PluginProcedimientosLink';

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
		return _n('Enlaces','enlaces',$nb, 'links');
   }    
   
   static function getIcon() {
		return "fas fa-link";
	 }

   // Si borro un enlace
   function cleanDBonPurge() {
	   global $DB;
	   
	  // Borro los elementos asociados
      $temp = new PluginProcedimientosProcedimiento_Item();
      $temp->deleteByCriteria(array('itemtype' => 'PluginProcedimientosLink',
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
	'id' => '100',
	'table' => $this->getTable(),
	'field' => 'comment',
	'name' => __('URL','URL'),
	'datatype' => 'text',
	'massiveaction' => false,
	];	
	
	$tab[] = [
	'id' => '101',
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
      $this->addStandardTab('PluginProcedimientosLink', $ong, $options);
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
	  
	 //Nombre del enlace (el que se verá en el procedimiento)
      echo "<tr class='tab_bg_1'>";
			echo "<th class='left'  colspan='1'>".__('Nombre','Nombre')."</th>";
			echo "<td class='left'  colspan='3'>";
				Html::autocompletionTextField($this,"name",array('size' => "124"));
			echo "</td>";
      echo "</tr>";

	  // URL
	  echo "<tr class='tab_bg_1'>";
	  echo "<th class='left'  colspan='1'>URL</th>";
	  echo "<td class='left' colspan='3'>";
	  Html::autocompletionTextField($this,"comment",array('size' => "124"));
      echo "</td></tr>"; 
	  
	// Ultima modificación
	echo "<tr>";
	  echo "<td class='center' colspan='4'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
	echo "</tr>";
	  $this->showFormButtons($options);
	    
      return true;
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