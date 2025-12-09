<?php
/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Martínez Ballesta.
   Fecha: Septiembre 2016

   ----------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginProcedimientosTipoaccion extends CommonDropdown {

  static $rightname = "plugin_procedimientos";

  static function canCreate(): bool {
	return false;
  } 
  
   static function getTypeName($nb = 0) {
      global $LANG;

      return 'Tipo de Acci&oacute;n';
   }

}

?>