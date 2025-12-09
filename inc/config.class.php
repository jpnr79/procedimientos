<?php

/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Martínez Ballesta.
   Fecha: Septiembre 2016

   ----------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginProcedimientosConfig extends CommonDBTM {
   static $rightname = 'config';
   
	
   public static function getTypeName($nb = 0) {
      return __('Procedimientos', 'Procedimientos');
   }   
   
   static function getIcon() {
		return "fas fa-share-alt";
	 }

  static function getMenuContent() {
      global $CFG_GLPI;

      $menu['page'] = "/plugins/procedimientos/front/config.php";
      $menu['title'] = self::getTypeName();
      $menu['icon']   = self::getIcon();
      
       $import_image     = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/procedimientos/pics/import.png"
                                title="Import Procedimiento">';   
       
      $menu['links'][$import_image]     = PluginProcedimientosProcedimiento_Form::getFormURL(false)."?import_form=1"; 

      $menu['options']['procedimiento']['page']               = "/plugins/procedimientos/front/procedimiento.php";
      $menu['options']['procedimiento']['title']              = __('Procedimientos', 'procedimientos');
      $menu['options']['procedimiento']['icon']               = PluginProcedimientosProcedimiento::getIcon();
	  if (Session::haveRight('plugin_procedimientos', CREATE)) {
		$menu['options']['procedimiento']['links']['add']       = '/plugins/procedimientos/front/procedimiento.form.php';
	  }
      $menu['options']['procedimiento']['links']['search']    = '/plugins/procedimientos/front/procedimiento.php';
      if (Session::haveRight('plugin_procedimientos', CREATE)) {
		$menu['options']['procedimiento']['links'][$import_image]    = PluginProcedimientosProcedimiento_Form::getFormURL(false)."/plugins/procedimientos/front/procedimiento.php?import_form=1";
	  }
	  $menu['options']['accion']['page']               = "/plugins/procedimientos/front/accion.php";
     $menu['options']['accion']['title']              = __('Acciones', 'procedimientos');
     $menu['options']['accion']['icon']               = PluginProcedimientosAccion::getIcon();
	  if (Session::haveRight('plugin_procedimientos', CREATE)) {
			$menu['options']['accion']['links']['add']       = '/plugins/procedimientos/front/accion.form.php';
	  }
      $menu['options']['accion']['links']['search']    = '/plugins/procedimientos/front/accion.php';      

	  $menu['options']['link']['page']               = "/plugins/procedimientos/front/link.php";
     $menu['options']['link']['title']              = __('Enlaces', 'procedimientos');
     $menu['options']['link']['icon']               = PluginProcedimientosLink::getIcon();
	  if (Session::haveRight('plugin_procedimientos', CREATE)) {
			$menu['options']['link']['links']['add']       = '/plugins/procedimientos/front/link.form.php';
	  }
      $menu['options']['link']['links']['search']    = '/plugins/procedimientos/front/link.php';
      
	  return $menu;
          
  }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
   {
      switch ($item->getType()) {
         case "PluginProcedimientosConfig":
            $object  = new self;
            $found = $object->find();
            $number  = count($found);
            return self::createTabEntry(self::getTypeName($number), $number);
            break;
      }
      return '';
   }     
   
   static function showConfigPage()	 {
       global $CFG_GLPI;

	   
	if (Session::haveRight('plugin_procedimientos', READ)) {	   
		echo "<div class='center'>";
		echo "<table class='tab_cadre'>";
		echo "<tr><th>".__('Configuraci&oacute;n plugin Procedimientos','Configuraci&oacute;n plugin Procedimientos')."</th></tr>";

		 
		   // Gestion de procedimientos
		   echo "<tr class='tab_bg_1 center'><td>";
		   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/procedimientos/front/procedimiento.php' >".__('Crear o modificar procedimientos','Crear o modificar procedimientos')."</a>";
		   echo "</td/></tr>\n";

		   // Gestion de acciones
		   echo "<tr class='tab_bg_1 center'><td>";
		   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/procedimientos/front/accion.php' >".__('Crear o modificar acciones','Crear o modificar acciones')."</a>";
		   echo "</td/></tr>\n";
		   
		   // Gestion de enlaces
		   echo "<tr class='tab_bg_1 center'><td>";
		   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/procedimientos/front/link.php' >".__('Crear o modificar enlaces','Crear o modificar enlaces')."</a>";
		   echo "</td/></tr>\n";		   

		echo "</table></div>";
       } else {
	      Html::displayRightError();
       }
   }
   
}

?>