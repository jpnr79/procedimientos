if (!class_exists('Ticket') && defined('GLPI_ROOT')) {
	require_once(GLPI_ROOT . '/inc/ticket.class.php');
// End of file
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

// Shared logging helper for this plugin to support GLPI 11+ fallbacks
if (!function_exists('procedimientos_log')) {
	function procedimientos_log(string $message): void {
		if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
			Toolbox::logInFile('procedimientos', $message);
		} else {
			error_log('[procedimientos] ' . $message);
		}
	}
}

// Provide a minimal Toolbox fallback when GLPI Toolbox is not available (prevents fatal errors during CLI ops)
if (!class_exists('Toolbox')) {
	class Toolbox {
		public static function logInFile($file, $message) {
			error_log('[' . $file . '] ' . $message);
		}
	}
}

// [INICIO] [CRI] JMZ18G MIGRACIÓN GLPI 9.5.7 - 1 columnas utilizan el tipo de campo de fecha y hora en desuso.
function procedimientos_notMigratedDatetime() {
	global $DB;
	// Get current DB name
	$dbname = '';
	$res = $DB->request("SELECT DATABASE() AS dbname");
	if ($res && count($res) && isset($res[0]['dbname']) && !empty($res[0]['dbname'])) {
		$dbname = $res[0]['dbname'];
	}
	$result = $DB->request([
		'FROM'  => 'information_schema.columns',
		'WHERE' => [
			'table_schema' => $dbname,
			'table_name'   => ['LIKE', 'glpi\_plugin\_procedimientos\_%'],
			'data_type'    => ['datetime']
		]
	]);
	if ($result && count($result)) {
		foreach ($result as $data) {
			$query = "ALTER TABLE `".$data["TABLE_NAME"]."` MODIFY `".$data["COLUMN_NAME"]."` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;";
			$DB->request($query);
		}
	}

}
// [FINAL] [CRI] JMZ18G MIGRACIÓN GLPI 9.5.7 - 1 columnas utilizan el tipo de campo de fecha y hora en desuso.

// Install process for plugin : need to return true if succeeded
function plugin_procedimientos_install() {
   global $DB;

   $__msg = "Plugin installation\n";
	if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
		Toolbox::logInFile('procedimientos', $__msg);
	} else {
		error_log('[procedimientos] ' . $__msg);
	}

	   // Check if table exists (GLPI 11+ compatible, no nested request)
	   $table_exists = false;
	   // Workaround: use DB_DEFAULT constant if defined, else fallback to a placeholder
	$dbname = 'glpidb'; // Set to your actual database name

	procedimientos_log('DEBUG: $dbname value before table existence check: ' . var_export($dbname, true));
	   $res = $DB->request([
		   'FROM' => 'information_schema.tables',
		   'WHERE' => [
			   'table_schema' => $dbname,
			   'table_name'   => 'glpi_plugin_procedimientos_procedimientos'
		   ]
	   ]);
	   if ($res && count($res)) {
		   $table_exists = true;
	   }
	if (!$table_exists) {
		$fichero_install = GLPI_ROOT . '/plugins/procedimientos/sql/install.sql';
		if (file_exists($fichero_install)) {
			Session::addMessageAfterRedirect("Ejecutando fichero <strong><font color='#40b122'>INSTALL.sql</font></strong>", true);
			$sql = file_get_contents($fichero_install);
			foreach (explode(';', $sql) as $statement) {
				$statement = trim($statement);
				if ($statement) {
					   $DB->request($statement);
				}
			}
			Session::addMessageAfterRedirect("<br>Scripts ejecutado<br>", true);
		} else {
			Session::addMessageAfterRedirect("No existe el fichero " . $fichero_install, true);
		}
	}

	// Check if glpi_followuptypes table exists
	$followup_exists = false;
	   $res = $DB->request([
		   'FROM' => 'information_schema.tables',
		   'WHERE' => [
			   'table_schema' => $dbname,
			   'table_name'   => 'glpi_followuptypes'
		   ]
	   ]);
	if ($res && count($res)) {
		$followup_exists = true;
	}
	if ($table_exists && !$followup_exists) {
		   $DB->request("CREATE TABLE IF NOT EXISTS `glpi_followuptypes` (
			   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			   `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			   `comment` text COLLATE utf8mb4_unicode_ci,
			   PRIMARY KEY (`id`),
			   KEY `name` (`name`)
		   ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
		   $DB->request("INSERT INTO `glpi_followuptypes` (`id`,`name`,`comment`) VALUES (9,'Comunicación con el solicitante','Cuando queramos informar al solicitante sobre el ticket. Seleccionar Privado \"No\"\r\n');");
		   $DB->request("INSERT INTO `glpi_followuptypes` (`id`,`name`,`comment`) VALUES (10,'Comunicación entre técnicos','Cuando reasignamos el ticket a otro grupo técnico y le queremos pasar información');");
		   $DB->request("INSERT INTO `glpi_followuptypes` (`id`,`name`,`comment`) VALUES (11,'Mal escalado','Cuando nos llega un ticket que no es para nuestro grupo.');");
		   $DB->request("INSERT INTO `glpi_followuptypes` (`id`,`name`,`comment`) VALUES (12,'Petición de Información','Cuando necesitamos información del solicitante para poder tramitar el ticket. Debe de seleccionarse Privado \"No\"');");
		   $DB->request("INSERT INTO `glpi_followuptypes` (`id`,`name`,`comment`) VALUES (13,'Anotación','');");

		// Check if field exists
		$field_exists = false;
		$res = $DB->request([
			'FROM' => 'information_schema.columns',
			'WHERE' => [
				'table_schema' => $DB->request("SELECT DATABASE() AS dbname")[0]['dbname'],
				'table_name'   => 'glpi_itilfollowups',
				'column_name'  => 'followuptypes_id'
			]
		]);
		if ($res && count($res)) {
			$field_exists = true;
		}
		if (!$field_exists) {
			   $DB->request("ALTER TABLE `glpi_itilfollowups` ADD COLUMN `followuptypes_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `sourceof_items_id`;");
		}
	}
// [INICIO] [CRI] - JMZ18G - 06/11/2020 Añadir actiontime al detalle de la tarea
	$field_exists = false;
	$res = $DB->request([
		'FROM' => 'information_schema.columns',
		'WHERE' => [
			'table_schema' => $DB->request("SELECT DATABASE() AS dbname")[0]['dbname'],
			'table_name'   => 'glpi_plugin_procedimientos_tareas',
			'column_name'  => 'actiontime'
		]
	]);
	   if (!$res || !count($res)) {
		   $DB->request("ALTER TABLE `glpi_plugin_procedimientos_tareas` ADD COLUMN `actiontime` BIGINT UNSIGNED NULL DEFAULT 0 AFTER `tasktemplates_id`;");
	   }
// [FIN] [CRI] - JMZ18G - 06/11/2020 Añadir actiontime al detalle de la tarea

// [INICIO] [CRI] - JMZ18G - 06/05/2022 Añadir accion Eliminar Técnicos

$query = "SELECT * FROM glpi_plugin_procedimientos_tipoaccions where uuid = 'c0dff0d6-9e4abb40-5a61e7e35e2256.00000009';";
	   $result = $DB->request($query);
			   if (class_exists('Plugin') && (Plugin::isPluginInstalled('formcreator') || Plugin::isPluginActivated('formcreator'))) {
				   $DB->request("UPDATE glpi_plugin_formcreator_targettickets AS a LEFT join glpi_plugin_procedimientos_procedimientos_forms b on  a.id = b.plugin_formcreator_targettickets_id and b.plugin_formcreator_targettickets_id IS NOT NULL SET a.plugin_procedimientos_procedimientos_id = IF(b.plugin_procedimientos_procedimientos_id IS NOT NULL, b.plugin_procedimientos_procedimientos_id, 0)");
			   }
		}
	// *******************************************************************************************
	//  [FINAL] [CRI] JMZ18G ASOCIAR AL PLUGIN EL DESTINO DEL TICKET DE FORMCREATOR 
	// *******************************************************************************************

	// [INICIO] [CRI] JMZ18G MIGRACIÓN GLPI 9.5.7 - 1 columnas utilizan el tipo de campo de fecha y hora en desuso.
	// GLPI 11: areTimezonesAvailable() is no longer available - TIMESTAMP is now standard
	// if ($DB->TableExists("glpi_plugin_procedimientos_procedimientos")){
	//
	//	if ($DB->areTimezonesAvailable()) {
	//
	//		procedimientos_notMigratedDatetime();
	//
	// 	}
	//
	// }
	// [FINAL] [CRI] JMZ18G MIGRACIÓN GLPI 9.5.7 - 1 columnas utilizan el tipo de campo de fecha y hora en desuso.

   PluginProcedimientosProfile::initProfile();
   PluginProcedimientosProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);	
   
	return true;
// Uninstall process for plugin : need to return true if succeeded
function plugin_procedimientos_uninstall() {
	/*global $DB;
	  Toolbox::logInFile("procedimientos", "Plugin Uninstallation\n");
	
    if ($DB->TableExists("glpi_plugin_procedimientos_procedimientos")){ 
	
		$fichero_install = GLPI_ROOT . '/plugins/procedimientos/sql/uninstall.sql';
		if (file_exists($fichero_install)){
			Session::addMessageAfterRedirect("Ejecutando fichero <strong><font color='#b14522'>UNINSTALL.sql</font></strong>",true);
			$DB->runFile($fichero_install);
			Session::addMessageAfterRedirect("<br>Scripts ejecutado<br>",true);
		} else {
			Session::addMessageAfterRedirect("No existe el fichero ".$fichero_install,true);
		} 		

	}*/
	
		return true;
	}


function plugin_procedimientos_postinit() {   
   return true;
}

function plugin_procedimientos_getAddSearchOptions($itemtype) {
   global $LANG;

   $sopt = array();
   
   //echo $itemtype;
 
   if ($itemtype == 'PluginProcedimientosAccion') {
	 	
		$sopt['tareas'] = 'Detalles tarea';
		
		$sopt[1105]['table']     = 'glpi_taskcategories';
        $sopt[1105]['field']     = 'name';		 
        $sopt[1105]['name']      = 'Tipo Tarea SD';	
		$sopt[1105]['datatype']      = 'dropdown';
		$sopt[1105]['massiveaction'] = false;
		$sopt[1105]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_tareas',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));
		/*$sopt['escalados'] = 'Detalles escalado';
		
		$sopt[1106]['table']     = 'glpi_groups';
        $sopt[1106]['field']     = 'completename';
        $sopt[1106]['itemlink']     = 'groups_id_asignado';
	    $sopt[1106]['condition']     = 'is_assign';	
        $sopt[1106]['name']      = 'Grupo asignado';	
		$sopt[1106]['datatype']      = 'dropdown';
		$sopt[1106]['massiveaction'] = false;
		$sopt[1106]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_escalados',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));
		$sopt[1107]['table']     = 'glpi_users';
        $sopt[1107]['field']     = 'completename';
        $sopt[1107]['itemlink']     = 'users_id_asignado';
        $sopt[1107]['name']      = 'T&eacute;cnico asignado';	
		$sopt[1107]['datatype']      = 'dropdown';
		$sopt[1107]['massiveaction'] = false;
		$sopt[1107]['right']  = 'own_ticket'; // Sólo usuarios técnicos
		$sopt[1107]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_escalados',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));																  
		$sopt[1108]['table']     = 'glpi_groups';
        $sopt[1108]['field']     = 'completename';
        $sopt[1108]['itemlink']     = 'groups_id_observ';
	    $sopt[1108]['condition']     = 'is_assign';	
        $sopt[1108]['name']      = 'Grupo observador';	
		$sopt[1108]['datatype']      = 'dropdown';
		$sopt[1108]['massiveaction'] = false;
		$sopt[1108]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_escalados',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));
		$sopt[1109]['table']     = 'glpi_users';
        $sopt[1109]['field']     = 'completename';
        $sopt[1109]['itemlink']     = 'users_id_observ';
        $sopt[1109]['name']      = 'Usuario observador';	
		$sopt[1109]['datatype']      = 'dropdown';
		$sopt[1109]['massiveaction'] = false;
		$sopt[1109]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_escalados',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));		*/															  
		$sopt['seguimientos'] = 'Detalles seguimiento';
		
		$sopt[1110]['table']     = 'glpi_plugin_procedimientos_seguimientos';
        $sopt[1110]['field']     = 'content';
        $sopt[1110]['name']      = 'Descripci&oacute;n';	
		$sopt[1110]['datatype']      = 'text';
		$sopt[1110]['massiveaction'] = false;
		$sopt[1110]['joinparams']    = array('jointype' => 'child');

		$sopt[1111]['table']     = 'glpi_plugin_procedimientos_seguimientos';
        $sopt[1111]['field']     = 'is_private';
        $sopt[1111]['name']      = 'Es privado';	
		$sopt[1111]['datatype']      = 'bool';
		$sopt[1111]['massiveaction'] = false;
		$sopt[1111]['joinparams']    = array('jointype' => 'child');
		
		$sopt[1112]['table']     = 'glpi_requesttypes';
        $sopt[1112]['field']     = 'name';
        $sopt[1112]['name']      = 'Origen del seguimiento';	
		$sopt[1112]['datatype']  = 'dropdown';
		$sopt[1112]['massiveaction'] = false;
		$sopt[1112]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_seguimientos',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));		
		$sopt[1113]['table']     = 'glpi_followuptypes';
        $sopt[1113]['field']     = 'name';
        $sopt[1113]['name']      = 'Tipo de seguimiento';	
		$sopt[1113]['datatype']  = 'dropdown';
		$sopt[1113]['massiveaction'] = false;
		$sopt[1113]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_seguimientos',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));

		$sopt['modificar'] = 'Detalles Modificar Ticket';
		
		$sopt[1114]['table']     = 'glpi_requesttypes';
        $sopt[1114]['field']     = 'name';
        $sopt[1114]['name']      = 'Origen del ticket';	
		$sopt[1114]['datatype']  = 'dropdown';
		$sopt[1114]['massiveaction'] = false;
		$sopt[1114]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_updatetickets',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
														 'condition' => '')));																  

		$sopt[1115]['table']     = 'glpi_itilcategories';
        $sopt[1115]['field']     = 'name';
        $sopt[1115]['name']      = 'Categor&iacute;a del ticket';	
		$sopt[1115]['datatype']  = 'dropdown';
		$sopt[1115]['massiveaction'] = false;
		$sopt[1115]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_updatetickets',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
														 'condition' => '')));	 
		$sopt[1116]['table']     = 'glpi_itilcategories';
        $sopt[1116]['field']     = 'name';
        $sopt[1116]['name']      = 'Estado del ticket';	
		$sopt[1116]['datatype']  = 'dropdown';
		$sopt[1116]['massiveaction'] = false;
		$sopt[1116]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_updatetickets',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
														 'condition' => '')));	 														 
	}
	if ($itemtype == 'PluginProcedimientosProcedimiento') {
	    $sopt['visibilidad'] = 'Visibilidad';
		
		$sopt[1116]['table']     = 'glpi_groups';
        $sopt[1116]['field']     = 'completename';
	    $sopt[1116]['condition']     = 'is_assign';	
        $sopt[1116]['name']      = 'Grupo';	
        $sopt[1116]['forcegroupby']    = true;	
		$sopt[1116]["splititems"] = true;			
		$sopt[1116]['datatype']      = 'dropdown';
		$sopt[1116]['massiveaction'] = false;
		$sopt[1116]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_groups',
                                                 'joinparams'
                                                         => array('jointype'  => 'child', 'condition' => '')));	

		$sopt[1117]['table']     = 'glpi_ticketrecurrents';
        $sopt[1117]['field']     = 'name';
        $sopt[1117]['name']      = 'Ticket recurrente';	
		$sopt[1117]['datatype']      = 'dropdown';
        $sopt[1117]['forcegroupby']    = true;	
      //  $sopt[1117]['usehaving']    = true;	
		$sopt[1117]["splititems"] = true;		
		$sopt[1117]['massiveaction'] = false;
		$sopt[1117]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_ticketrecurrents',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
														 'condition' => '')));	 														  	
 
 		$sopt[1118]['table']     = 'glpi_plugin_formcreator_forms';
        $sopt[1118]['field']     = 'name';
        $sopt[1118]['name']      = 'Pedido de cat&aacute;logo';	
		$sopt[1118]['datatype']      = 'dropdown';
        $sopt[1118]['forcegroupby']    = true;	
		$sopt[1118]["splititems"] = true;					
		$sopt[1118]['massiveaction'] = false;
		$sopt[1118]['joinparams']    = array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_forms',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));	 
	    $sopt['elementos'] = 'Elementos';
		$sopt[1120]['table']      = 'glpi_plugin_procedimientos_accions';
		$sopt[1120]['name']       =  __('Accion/es', 'Accion/es');
		$sopt[1120]['field']      = 'name';
        $sopt[1120]['datatype']   = 'itemlink';
		$sopt[1120]['linkfield']   = 'items_id';
		$sopt[1120]['forcegroupby'  ] = true;
		$sopt[1120]['massiveaction']  = false;
        $sopt[1120]['joinparams']     =  array('beforejoin' => array('table' => 'glpi_plugin_procedimientos_procedimientos_items',
																	 'joinparams' => array('jointype' => 'child', 
																	                       'condition' => "AND `NEWTABLE`.`itemtype`= 'PluginProcedimientosAccion'")));
		$sopt[1121]['table']      = 'glpi_plugin_procedimientos_condicions';
		$sopt[1121]['name']       =  __('Condicion/es', 'Condicion/es');
		$sopt[1121]['field']      = 'name';
        $sopt[1121]['datatype']   = 'itemlink';
		$sopt[1121]['linkfield']   = 'items_id';
		$sopt[1121]['forcegroupby']    = true;
		$sopt[1121]['massiveaction']  = false;
        $sopt[1121]['joinparams']     =  array('beforejoin' => array('table' => 'glpi_plugin_procedimientos_procedimientos_items',
																	 'joinparams' => array('jointype' => 'child', 
																	                       'condition' => "AND `NEWTABLE`.`itemtype`= 'PluginProcedimientosCondicion'")));
// [INICIO] JDMZ18G INFORGES  RELACIONADA CONSIGO MISMA Y CON OTRA TABLA
		$sopt[1119]['table']      = 'glpi_plugin_procedimientos_procedimientos';
		$sopt[1119]['name']       =  __('Procedimiento anidado', 'Procedimiento anidado');
		$sopt[1119]['field']      = "name";
        $sopt[1119]['datatype']   = 'itemlink';
		$sopt[1119]['itemlink']   = 'items_id';
		$sopt[1119]['massiveaction']  = false;
		$sopt[1119]['usehaving']       = true;
		$sopt[1119]['forcegroupby']    = true;
        $sopt[1119]['itemlink_type']  = 'PluginProcedimientosProcedimiento';
        $sopt[1119]['joinparams']     =  array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_items',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => "AND `NEWTABLE`.`itemtype`= 'PluginProcedimientosProcedimiento'")));	
// [FIN] JDMZ18G INFORGES																  
																						   
	}
    if (($itemtype == 'Ticket') && (Session::haveRight('plugin_procedimientos', READ))) {	
		$sopt['ptrabajo'] = 'Procedimientos de Trabajo';
			
		$sopt[1319]['table']      = 'glpi_plugin_procedimientos_procedimientos';
		$sopt[1319]['name']       =  __('Procedimiento relacionado', 'Procedimiento relacionado');
		$sopt[1319]['field']      = "name";
        $sopt[1319]['datatype']   = 'itemlink';
		$sopt[1319]['itemlink']   = 'plugin_procedimientos_procedimientos_id';
		$sopt[1319]['massiveaction']  = false;
        $sopt[1319]['itemlink_type']  = 'PluginProcedimientosProcedimiento';
        $sopt[1319]['joinparams']     =  array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_tickets',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));	
																  
		$sopt[1320]['table']      = 'glpi_plugin_procedimientos_procedimientos';
		$sopt[1320]['name']       =  __('Proc. Activo', 'Proc. Activo');
		$sopt[1320]['field']      = "active";
        $sopt[1320]['datatype']   = 'bool';
		$sopt[1320]['itemlink']   = 'plugin_procedimientos_procedimientos_id';
		$sopt[1320]['massiveaction']  = false;
        $sopt[1320]['itemlink_type']  = 'PluginProcedimientosProcedimiento';
        $sopt[1320]['joinparams']     =  array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_tickets',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));
																  
		$sopt[1321]['table']      = 'glpi_plugin_procedimientos_procedimientos';
		$sopt[1321]['name']       =  __('Proc. Borrado', 'Proc. Borrado');
		$sopt[1321]['field']      = "is_deleted";
        $sopt[1321]['datatype']   = 'bool';
		$sopt[1321]['itemlink']   = 'plugin_procedimientos_procedimientos_id';
		$sopt[1321]['massiveaction']  = false;
        $sopt[1321]['itemlink_type']  = 'PluginProcedimientosProcedimiento';
        $sopt[1321]['joinparams']     =  array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_tickets',
                                                 'joinparams'
                                                         => array('jointype'  => 'child', 'condition' => '')));	
																
	} 
    if (($itemtype == 'TicketRecurrent') && (Session::haveRight('plugin_procedimientos', READ))) {	
		$sopt['ptrabajo'] = 'P.Trabajo';
			
		$sopt[1323]['table']      = 'glpi_plugin_procedimientos_procedimientos';
		$sopt[1323]['name']       =  __('Procedimiento', 'Procedimiento');
		$sopt[1323]['field']      = "name";
        $sopt[1323]['datatype']   = 'itemlink';
		$sopt[1323]['itemlink']   = 'plugin_procedimientos_procedimientos_id';
		$sopt[1323]['massiveaction']  = false;
		$sopt[1323]['usehaving']       = true;
		$sopt[1323]['forcegroupby']    = true;
        $sopt[1323]['itemlink_type']  = 'PluginProcedimientosProcedimiento';
        $sopt[1323]['joinparams']     =  array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_ticketrecurrents',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));		
  	}
    if (($itemtype == 'PluginFormcreatorForm') && (Session::haveRight('plugin_procedimientos', READ))) {	
		$sopt['ptrabajo'] = 'P.Trabajo';
			
		$sopt[1324]['table']      = 'glpi_plugin_procedimientos_procedimientos';
		$sopt[1324]['name']       =  __('Procedimiento', 'Procedimiento');
		$sopt[1324]['field']      = "name";
        $sopt[1324]['datatype']   = 'itemlink';
		$sopt[1324]['itemlink']   = 'plugin_procedimientos_procedimientos_id';
		$sopt[1324]['massiveaction']  = false;
		$sopt[1324]['usehaving']       = true;
		$sopt[1324]['forcegroupby']    = true;
        $sopt[1324]['itemlink_type']  = 'PluginProcedimientosProcedimiento';
        $sopt[1324]['joinparams']     =  array('beforejoin' =>
                                           array('table' => 'glpi_plugin_procedimientos_procedimientos_forms',
                                                 'joinparams'
                                                         => array('jointype'  => 'child',
                                                                  'condition' => '')));		
  	}	
	return $sopt;
}


/***********************************************************************************************************************************
Función que se ejecuta cuando actualizamos una validación en un ticket. 
**************************************************************************************************************************************/
function plugin_procedimientos_update_Validation($item) {
	global $DB;
	$id = $item->getField('id');
	$state = $item->getField('status');
	$tickets_id = $item->getField('tickets_id');
	$__msg = "Validación de Ticket con ID " . $id . " modifica su estado (" . $state . ") en el ticket con ID " . $tickets_id . "\n";
	if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
		Toolbox::logInFile('procedimientos', $__msg);
	} else {
		error_log('[procedimientos] ' . $__msg);
	}

	if ($state > 2){ // Estado "Concedido o Rechazado"		
		$procedimientos_id = get_procedimiento_principal($tickets_id); 
		if (isset($procedimientos_id)){ // Si existe un procedimiento ejecutándose para ese ticket.			
			$select = "SELECT id from `glpi_plugin_procedimientos_procedimientos_tickets`
					   WHERE tickets_id=".$tickets_id." and itemtype='PluginProcedimientosAccion' and instancia_id=".$id." and state=2;";					   
			   $result_select = $DB->request($select);
			   $__msg = "Select: " . $select . "\n";
			   if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
				   Toolbox::logInFile('procedimientos', $__msg);
			   } else {
				   error_log('[procedimientos] ' . $__msg);
			   }
			   // Log the select query for debugging
    Toolbox::logInFile("procedimientos", "Select: " . $select . "\n");
			   if ($result_select && count($result_select)) {
				   $row = $result_select[0];
				   if (isset($row['id'])){
					   $update = "UPDATE `glpi_plugin_procedimientos_procedimientos_tickets` SET `state`=1 
							  WHERE id=".$row['id'].";";
					   $DB->request($update);
					   ejecutar_Procedimiento($tickets_id);            
				   }
			   }
		}
		return true;
	}
		
}

/***********************************************************************************************************************************
Función que se ejecuta antes de actualizar una tarea en un ticket. 
CONTROLAMOS LA PRE-ACTUALIZACIÓN DE UNA TAREA PARA AÑADIR COMO TECNICO DE LA TAREA AL USUARIO QUE LA CIERRA Y SU GRUPO POR DEFECTO Y PARA AÑADIR AL USUARIO QUE LA MODIFICA EN CASO DE SER CERRADA CON CHEKBOX	
**************************************************************************************************************************************/
function plugin_procedimientos_pre_update_TicketTask($item) {

		$users_id = Session::getLoginUserID();

	   if ((isset($item->input["state"])) 
			and ($item->input["state"] == 6)
			and ($item->input["state"] <> $item->fields["state"])
			and (((empty($item->fields["users_id_tech"])) and (empty($item->input["users_id_tech"])))
			 or ((!empty($item->fields["users_id_tech"])) and (empty($item->input["users_id_tech"]))))
			) { 

				if ((!empty($item->fields["users_id_tech"]))
					and (empty($item->input["users_id_tech"]))) {
			
					if (isset($item->input["check"])) {
						$item->input["users_id_tech"] = $item->fields["users_id_tech"];
						unset($item->input["check"]);
					} else {	
						$item->input["users_id_tech"] = $users_id;	
					}	
					
				} else {
					$item->input["users_id_tech"] = $users_id;
				}
	   }

	   if (!isset($item->input["users_id_editor"])) { 
			$item->input["users_id_editor"] = $users_id;
	   }	   

		 if ((isset($item->input["state"])) 
		and ($item->input["state"] == 6)) {
		
		 $user = new User;
		 $user->getFromDB($item->input["users_id_tech"]);
		 $groups_id = $user->getField('groups_id');
 
		 if ((($groups_id <> $item->fields["groups_id_tech"]) ||
		 ((isset($item->input["groups_id_tech"])) and ($item->input["groups_id_tech"]<>$groups_id)))
		 and ($groups_id>0))  {		
			 
				if ((!isset($item->input["status"])) and (isset($item->input["_status"]))) {
					$item->input["status"] = $item->input["_status"];
				}
			
				if (empty($item->fields["groups_id_tech"])) {
				/*if ((!empty($item->fields["groups_id_tech"]))
					 and (empty($item->input["groups_id_tech"]))) {*/
			 
					 if (isset($item->input["check"])) {
						 $item->input["groups_id_tech"] = $groups_id;
						 unset($item->input["check"]);
					 } else {	
						 $item->input["groups_id_tech"] = $groups_id;	
					 }	
					 
				 } else {
					// $item->input["groups_id_tech"] = $groups_id;
				 }
 
		 }
			 
		}

		aviso_grupo($item);

	return $item;
}

function plugin_procedimientos_pre_add_TicketTask($item) {

	$users_id = Session::getLoginUserID();

	 if ((isset($item->input["state"])) 
		and ($item->input["state"] == 6)      
		and (((empty($item->fields["users_id_tech"])) and (empty($item->input["users_id_tech"])))
		 or ((!empty($item->fields["users_id_tech"])) and (empty($item->input["users_id_tech"]))))
		) { 

			if ((!empty($item->fields["users_id_tech"]))
				and (empty($item->input["users_id_tech"]))) {
		
				if (isset($item->input["check"])) {
					$item->input["users_id_tech"] = $item->fields["users_id_tech"];
					unset($item->input["check"]);
				} else {	
					$item->input["users_id_tech"] = $users_id;	
				}	
				
			} else {
				$item->input["users_id_tech"] = $users_id;
			}
	 }

	 if (!isset($item->input["users_id_editor"])) { 
		$item->input["users_id_editor"] = $users_id;
	 }	   

	 if ((isset($item->input["state"])) 
		and ($item->input["state"] == 6)) {
	 
		$user = new User;
		$user->getFromDB($item->input["users_id_tech"]);
		$groups_id = $user->getField('groups_id');

		if (($groups_id <> $item->input["groups_id_tech"] ) and ($groups_id>0)) {

				if (empty($item->fields["groups_id_tech"])) {	

				/*if ((!empty($item->fields["groups_id_tech"]))
					and (empty($item->input["groups_id_tech"]))) {*/
			
					if (isset($item->input["check"])) {
						$item->input["groups_id_tech"] = $item->fields["groups_id_tech"];
						unset($item->input["check"]);
					} else {	
						$item->input["groups_id_tech"] = $groups_id;	
					}	
					
				} else {
				 //	$item->input["groups_id_tech"] = $groups_id;
				}

		}
			
	 }

	 aviso_grupo($item);

	 return $item;
}

/***********************************************************************************************************************************
Función que se ejecuta cuando actualizamos una tarea en un ticket. 
**************************************************************************************************************************************/
function plugin_procedimientos_update_TicketTask($item) {
    global $DB;
    $id = $item->getField('id');
    $state = $item->getField('state');
    $tickets_id = $item->getField('tickets_id');
    Toolbox::logInFile("procedimientos", "Tarea de Ticket con ID $id modifica su estado ($state) en el ticket con ID $tickets_id\n");
    if ($state == 2) { // Estado "Hecho"
        $procedimientos_id = get_procedimiento_principal($tickets_id);
        if (isset($procedimientos_id)) { // Si existe un procedimiento ejecutándose para ese ticket.
            $select = "SELECT id FROM glpi_plugin_procedimientos_procedimientos_tickets WHERE tickets_id=$tickets_id AND itemtype='PluginProcedimientosAccion' AND instancia_id=$id AND state=2;";
            $result_select = $DB->request($select);
            Toolbox::logInFile("procedimientos", "Select: $select\n");
            if ($result_select && count($result_select)) {
                $row = $result_select[0];
                if (isset($row['id'])) {
                    $update = "UPDATE glpi_plugin_procedimientos_procedimientos_tickets SET state=1 WHERE id=" . $row['id'] . ";";
                    $DB->request($update);
                    ejecutar_Procedimiento($tickets_id);
                }
            }
        }
	}
	return true;
}

/***********************************************************************************************************************************
Función que encuentra el id del procedimiento asociado en la descripción de un destino FORMCREATOR
**************************************************************************************************************************************/

function plugin_procedimientos_destination($description) {
	$content = explode("[Procedimiento de trabajo asociado", $description);
	if (count($content) > 1) {
		return intval(preg_replace("/[^0-9]/", "", $content[1]));
	} else {
		return 0;
	}
}

/***********************************************************************************************************************************
Función que se ejecuta cuando creamos un ticket. 
**************************************************************************************************************************************/
function plugin_procedimientos_add_Ticket($item) {
	global $DB;

	$tickets_id = $item->fields['id'] ?? null;
	$entities_id = $item->fields['entities_id'] ?? 0;
	$pedido = false;

	// Modern GLPI: check for FormCreator tables and POSTed form info
	if ($DB->tableExists('glpi_plugin_formcreator_forms') && $DB->tableExists('glpi_plugin_formcreator_forms_items')) {
		if (isset($_POST['plugin_formcreator_forms_id'], $_POST['plugin_formcreator_targettickets_id'])) {
			$plugin_formcreator_forms_id = $_POST['plugin_formcreator_forms_id'];
			$plugin_formcreator_targettickets_id = $_POST['plugin_formcreator_targettickets_id'];

			$query = [
				'FROM'   => 'glpi_plugin_procedimientos_procedimientos_forms',
				'INNER JOIN' => [
					'glpi_plugin_procedimientos_procedimientos' => [
						'ON' => [
							'glpi_plugin_procedimientos_procedimientos_forms' => 'plugin_procedimientos_procedimientos_id',
							'glpi_plugin_procedimientos_procedimientos' => 'id'
						]
					]
				],
				'WHERE'  => [
					'glpi_plugin_procedimientos_procedimientos.is_deleted' => 0,
					'glpi_plugin_procedimientos_procedimientos.active' => 1,
					'glpi_plugin_procedimientos_procedimientos.entities_id' => $entities_id,
					'glpi_plugin_procedimientos_procedimientos_forms.plugin_formcreator_forms_id' => $plugin_formcreator_forms_id,
					'glpi_plugin_procedimientos_procedimientos_forms.plugin_formcreator_targettickets_id' => $plugin_formcreator_targettickets_id
				],
				'FIELDS' => ['glpi_plugin_procedimientos_procedimientos_forms.plugin_procedimientos_procedimientos_id']
			];
			$result = $DB->request($query);
			$row = ($result && count($result)) ? $result[0] : null;
			if (isset($row['plugin_procedimientos_procedimientos_id'])) {
				$procedimientos_id = $row['plugin_procedimientos_procedimientos_id'];
				// Remove previous procedure links for this ticket
				$DB->request([
					'DELETE' => 'glpi_plugin_procedimientos_procedimientos_tickets',
					'WHERE' => ['tickets_id' => $tickets_id]
				]);
				instancia_procedimiento($procedimientos_id, $tickets_id);
				ejecutar_Procedimiento($tickets_id);
				$pedido = true;
			} else {
				$procedimientos_id = plugin_procedimientos_destination($item->fields['content'] ?? '');
				if ($procedimientos_id > 0) {
					$procedure = new PluginProcedimientosProcedimiento();
					$params = [
						'id' => $procedimientos_id,
						'active' => 1,
						'entities_id' => $entities_id,
						'is_deleted' => 0
					];
					$procedimiento = $procedure->find($params);
					if (!empty($procedimiento)) {
						instancia_procedimiento($procedimientos_id, $tickets_id);
						ejecutar_Procedimiento($tickets_id);
						$pedido = true;
					}
				}
			}
		} else {
			$procedimientos_id = plugin_procedimientos_destination($item->fields['content'] ?? '');
			if ($procedimientos_id > 0) {
				$procedure = new PluginProcedimientosProcedimiento();
				$params = [
					'id' => $procedimientos_id,
					'active' => 1,
					'entities_id' => $entities_id,
					'is_deleted' => 0
				];
				$procedimiento = $procedure->find($params);
				if (!empty($procedimiento)) {
					instancia_procedimiento($procedimientos_id, $tickets_id);
					ejecutar_Procedimiento($tickets_id);
					$pedido = true;
				}
			}
		}
	}

	// If not handled by FormCreator, check for recurrent ticket
	if ($pedido === false) {
		$origen = $item->fields['requesttypes_id'] ?? null;
		if ($origen == 8) // Only if origin is glpi (8) = recurrent ticket
		{
			$entity = $item->fields['entities_id'] ?? 0;
			$query_tt_names = "SELECT name, value, glpi_ticketrecurrents.tickettemplates_id, glpi_ticketrecurrents.id as ticketrecurrents_id
				FROM glpi_ticketrecurrents
				JOIN glpi_tickettemplatepredefinedfields on (glpi_ticketrecurrents.tickettemplates_id = glpi_tickettemplatepredefinedfields.tickettemplates_id)
				WHERE glpi_tickettemplatepredefinedfields.num=1 and entities_id=$entity and is_active=1";
			$result_tt = $DB->request($query_tt_names);
			if ($result_tt && count($result_tt) > 0) {
				$encontrado = false;
				$name_ticket = $item->fields['name'] ?? '';
				foreach ($result_tt as $row_tt) {
					if ($encontrado) break;
					$date_ticket = $item->fields['date'] ?? '';
					$date = strtotime($date_ticket);
					$dia = "[" . date("d", $date) . "]";
					$mes = "[" . date("m", $date) . "]";
					$year = "[" . date("y", $date) . "]";
					$caso_especial = date("d", $date) . "/" . date("m", $date);
					$nombre_plantilla = str_replace("dd/mm-dd/mm]", $caso_especial, $row_tt['value']);
					$nombre_plantilla = str_replace("[dd]", $dia, $nombre_plantilla);
					$nombre_plantilla = str_replace("[mm]", $mes, $nombre_plantilla);
					$nombre_plantilla = str_replace("[aaaa]", $year, $nombre_plantilla);
					$pos = strpos($name_ticket, $nombre_plantilla);
					if (($name_ticket == $row_tt['name']) || ($pos !== false)) {
						$encontrado = true;
						if (isset($row_tt['ticketrecurrents_id'])) {
							$query2 = "SELECT plugin_procedimientos_procedimientos_id
								FROM glpi_plugin_procedimientos_procedimientos_ticketrecurrents
								INNER JOIN glpi_plugin_procedimientos_procedimientos on
								(glpi_plugin_procedimientos_procedimientos_ticketrecurrents.plugin_procedimientos_procedimientos_id = glpi_plugin_procedimientos_procedimientos.id)
								where ticketrecurrents_id=" . $row_tt['ticketrecurrents_id'] . " and glpi_plugin_procedimientos_procedimientos.is_deleted=0 and
								glpi_plugin_procedimientos_procedimientos.active=1;";
							$result2 = $DB->request($query2);
							$row2 = ($result2 && count($result2)) ? $result2[0] : null;
							if (isset($row2['plugin_procedimientos_procedimientos_id'])) {
								$procedimientos_id = $row2['plugin_procedimientos_procedimientos_id'];
								$DB->request([
									'DELETE' => 'glpi_plugin_procedimientos_procedimientos_tickets',
									'WHERE' => ['tickets_id' => $tickets_id]
								]);
								instancia_procedimiento($procedimientos_id, $tickets_id);
								ejecutar_Procedimiento($tickets_id);
							}
						}
					}
				}
			}
		}
	}
	return true;
}
/***********************************************************************************************************************************
Función que se ejecuta cuando actualizamos un ticket. 
Comprueba si ha cambiado el pedido de catálogo.
**************************************************************************************************************************************/

function plugin_procedimientos_update_Ticket($item) {
	global $DB;

$tickets_id = $item->getField('id');	

if ((isset($_POST["actualizarPedido"]))
				&&($_POST["actualizarPedido"]=='Actualizar')
				&&(isset($_POST["plugin_formcreator_forms_id"]))
				&&(isset($_POST["plugin_formcreator_targettickets_ids"]))){
		// Buscamos id pedido de catalogo del ticket (si lo hay)
		Toolbox::logInFile("procedimientos", " update_Ticket_POST : ".print_r($_POST, TRUE) ."\r\n\r\n"); 
		// Modernized: direct DB query for the relation
		$plugin_formcreator_forms_id = $_POST['plugin_formcreator_forms_id'];
		$plugin_formcreator_targettickets_id = $_POST['plugin_formcreator_targettickets_id'];
		$query = [
			'FROM'   => 'glpi_plugin_procedimientos_procedimientos_forms',
			'INNER JOIN' => [
				'glpi_plugin_procedimientos_procedimientos' => [
					'ON' => [
						'glpi_plugin_procedimientos_procedimientos_forms' => 'plugin_procedimientos_procedimientos_id',
						'glpi_plugin_procedimientos_procedimientos' => 'id'
					]
				]
			],
			'WHERE'  => [
				'glpi_plugin_procedimientos_procedimientos.is_deleted' => 0,
				'glpi_plugin_procedimientos_procedimientos.active' => 1,
				'glpi_plugin_procedimientos_procedimientos.entities_id' => $item->fields['entities_id'] ?? 0,
				'glpi_plugin_procedimientos_procedimientos_forms.plugin_formcreator_forms_id' => $plugin_formcreator_forms_id,
				'glpi_plugin_procedimientos_procedimientos_forms.plugin_formcreator_targettickets_id' => $plugin_formcreator_targettickets_id
			],
			'FIELDS' => ['glpi_plugin_procedimientos_procedimientos_forms.plugin_procedimientos_procedimientos_id']
		];
		$result = $DB->request($query);
		$row = ($result && count($result)) ? $result[0] : null;
		if (isset($row['plugin_procedimientos_procedimientos_id'])) {
			$procedimientos_id = $row['plugin_procedimientos_procedimientos_id'];
			$select_proc = "SELECT plugin_procedimientos_procedimientos_id FROM glpi_plugin_procedimientos_procedimientos_tickets WHERE tickets_id = $tickets_id ORDER BY id;";
			$result_proc = $DB->request($select_proc);
			if ($result_proc && count($result_proc) > 0) {
				$proc_actual = $result_proc[0];
				$proc_actual_ID = $proc_actual['plugin_procedimientos_procedimientos_id'];
				if ($procedimientos_id != $proc_actual_ID) {
					$DB->request([ 'DELETE' => 'glpi_plugin_procedimientos_procedimientos_tickets', 'WHERE' => ['tickets_id' => $tickets_id] ]);
					instancia_procedimiento($procedimientos_id, $tickets_id);
					ejecutar_Procedimiento($tickets_id);
					$pedido = true;
				}
			} else {
				instancia_procedimiento($procedimientos_id, $tickets_id);
				ejecutar_Procedimiento($tickets_id);
			}
		} else {
			$DB->request([ 'DELETE' => 'glpi_plugin_procedimientos_procedimientos_tickets', 'WHERE' => ['tickets_id' => $tickets_id] ]);
		}
		}
	}
	return true;

/*function plugin_procedimientos_update_Ticket($item) {
    global $DB;

	$tickets_id = $item->getField('id');	

	if ((isset($_POST["actualizarPedido"]))&&($_POST["actualizarPedido"]=='Actualizar')&&(isset($_POST["peticion_id"]))){
			// Buscamos id pedido de catalogo del ticket (si lo hay)
			Toolbox::logInFile("procedimientos", " update_Ticket_POST : ".print_r($_POST, TRUE) ."\r\n\r\n"); 
			if ($DB->TableExists("glpi_plugin_formcreator_forms") && $DB->TableExists("glpi_plugin_formcreator_forms_items")) {
					// Buscamos si dicho pedido de catálogo está en algún procedimiento de trabajo. si es así lo mostramos
					// CAMINO1
					$query = "SELECT 
						`glpi_plugin_procedimientos_procedimientos`.`id` as id_proc, 
						`glpi_plugin_procedimientos_procedimientos`.`name` as nombre 
						FROM `glpi_plugin_formcreator_forms` inner join `glpi_plugin_formcreator_forms_items`
						on `glpi_plugin_formcreator_forms`.`id`=`glpi_plugin_formcreator_forms_items`.`plugin_formcreator_forms_id`
						inner join `glpi_plugin_procedimientos_procedimientos_forms`
						on `glpi_plugin_procedimientos_procedimientos_forms`.`plugin_formcreator_forms_id`= `glpi_plugin_formcreator_forms_items`.`plugin_formcreator_forms_id`
						inner join `glpi_plugin_procedimientos_procedimientos`
						on `glpi_plugin_procedimientos_procedimientos_forms`.`plugin_procedimientos_procedimientos_id`=`glpi_plugin_procedimientos_procedimientos`.`id`
						where `glpi_plugin_formcreator_forms_items`.`itemtype`='Ticket' and `glpi_plugin_formcreator_forms`.`is_active`=1
						and `glpi_plugin_procedimientos_procedimientos`.`is_deleted`=0  and `glpi_plugin_formcreator_forms_items`.`items_id`='".$tickets_id."';";
	
					$result = $DB->request($query);
					$row = ($result && count($result)) ? $result[0] : null;
					if (isset($row['id_proc'])) {
						$procedimientos_id = $row['id_proc'];
						$select_proc = "SELECT plugin_procedimientos_procedimientos_id 
										FROM glpi_plugin_procedimientos_procedimientos_tickets
										WHERE tickets_id = $tickets_id
										ORDER BY id;";
						$result_proc = $DB->request($select_proc);
						if ($result_proc && count($result_proc) > 0) {
							$proc_actual = $result_proc[0];
							$proc_actual_ID = $proc_actual['plugin_procedimientos_procedimientos_id'];
							if ($procedimientos_id != $proc_actual_ID) {
								// Borramos de los elementos de posibles anteriores procedimientos asociados al ticket correspondiente
								$query = "DELETE FROM glpi_plugin_procedimientos_procedimientos_tickets WHERE tickets_id = $tickets_id";
								$DB->request($query);
								// Instanciamos y ejecutamos procedimiento correspondiente.
								instancia_procedimiento($procedimientos_id, $tickets_id);
								ejecutar_Procedimiento($tickets_id);
								$pedido = true;
							}
						} else {
							instancia_procedimiento($procedimientos_id, $tickets_id);
							ejecutar_Procedimiento($tickets_id);
						}
					} else { // emb97m - INFORGES - No hay procedimiento para el pedido de catálogo.
						// Borramos de los elementos de posibles anteriores procedimientos asociados al ticket correspondiente
						$query_proc = "DELETE FROM glpi_plugin_procedimientos_procedimientos_tickets WHERE tickets_id = $tickets_id";
						$DB->request($query_proc);
					}
			}
	}
	return true;
}*/


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_procedimientos_MassiveActions($type) {
	switch ($type) {
		case 'PluginProcedimientosProcedimiento' :
			if (Session::haveRight("plugin_procedimientos",UPDATE)) {             
				return array(
					'PluginProcedimientosProcedimiento'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_procedimientos_renumerar' => 'Renumerar lineas',
					'PluginProcedimientosProcedimiento'.MassiveAction::CLASS_ACTION_SEPARATOR.'Export' => _sx('button', 'Export')
				);
			}
			break;
	}
	return array();
}
	

// [INICIO] [CRI] JMZ18G ASOCIAR AL PLUGIN EL DESTINO DEL TICKET DE FORMCREATOR 
// Captura del evento modificar el destino de un formulario de formcreator.
function plugin_procedimientos_update_TargetTicket($item) {
	global $DB;
	$params = [
		"plugin_formcreator_targettickets_id" => $item->getField('id'),
		"plugin_formcreator_forms_id" => $item->getField('plugin_formcreator_forms_id'),
		"plugin_procedimientos_procedimientos_id" => $item->getField('plugin_procedimientos_procedimientos_id'),
	];
	Toolbox::logInFile("procedimientos", "El destino ".$params['plugin_formcreator_targettickets_id'] ." del pedido de catálogo ".$params['plugin_formcreator_forms_id']." modifica su procedimiento al ID (".$params['plugin_procedimientos_procedimientos_id'].") \n"); 
	// ...existing code from previous misplaced body goes here...
// Captura del evento eliminar el relación entre pedido de catálogo y procedimiento.
function plugin_procedimientos_delete_RelationForm($item) {
		global $DB;
		$sql = "UPDATE `glpi_plugin_formcreator_targettickets` SET `plugin_procedimientos_procedimientos_id` = '0' WHERE (`id` = " . $item->fields["plugin_formcreator_targettickets_id"] . ")";
		$DB->request($sql);

    // Legacy FormCreator objects removed. Use only available fields for message.
    $params = [
        "header"  => sprintf(__("<H3>Detalles de la relación eliminada:</H3>","procedimiento")),
        "message" => sprintf(__("<strong>Form:</strong> <br><br><font color = '#7c0068'>%s</font><br><br><strong>Form Answer:</strong> <br><br><font color = '#7c0068'>%s</font><br>","procedimiento"), $item->fields['forms_id'] ?? '', $item->fields['formanswers_id'] ?? ''),
        "footer"  => ""
    ];
    Session::addMessageAfterRedirect(PluginProcedimientosProcedimiento_Form::plugin_procedimientos_get_message($params, "d"), false, 1); // 1 = INFO
	}
	
   // Automatic migration: ensure plugin_procedimientos_tipoaccions_id is unsigned (must run after install.sql)
   try {
      $DB->request("ALTER TABLE glpi_plugin_procedimientos_accions MODIFY COLUMN plugin_procedimientos_tipoaccions_id tinyint(1) unsigned NOT NULL DEFAULT '0';");
   } catch (Exception $e) {
      if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
         Toolbox::logInFile('procedimientos', 'Migration warning: ' . $e->getMessage());
      } else {
         error_log('[procedimientos] Migration warning: ' . $e->getMessage());
      }
	}
}
