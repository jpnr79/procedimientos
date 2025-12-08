<?php
/*
   ----------------------------------------------------------
   Plugin Procedimientos 2.2.1
   GLPI 0.85.5
  
   Autor: Elena Martínez Ballesta.
   Fecha: Septiembre 2016v

   ----------------------------------------------------------
 */


// Init the hooks of the plugins -Needed
function plugin_init_procedimientos() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   
   // CSRF compliance : All accions must be done via POST and forms closed by Html::closeForm();
   $PLUGIN_HOOKS['csrf_compliant']['procedimientos'] = true;

   // Configure current profile ...
   $PLUGIN_HOOKS['change_profile']['procedimientos'] = array('PluginProcedimientosProfile','changeprofile');
   
   $PLUGIN_HOOKS['config_page']['procedimientos'] = 'front/config.php';
	
   $Plugin = new Plugin();
   if ($Plugin->isActivated('procedimientos')) {
	  // Registro de clases
		Plugin::registerClass('PluginProcedimientosProfile',	// Perfil
			array('addtabon' => array('Profile')));
		
		Plugin::registerClass('PluginProcedimientosAccion'); // Acciones
		Plugin::registerClass('PluginProcedimientosLink'); // Enlaces
		Plugin::registerClass('PluginProcedimientosAcciondetalle'); // Pestaña detalles de la acción
		Plugin::registerClass('PluginProcedimientosProcedimiento');// Procedimientos
		Plugin::registerClass('PluginProcedimientosProcedimiento_Ticket', array('addtabon' => array('Ticket'))); // Pestaña en Ticket 
     	Plugin::registerClass('PluginProcedimientosCondicion'); // Condiciones
		Plugin::registerClass('PluginProcedimientosSalto'); // Saltos
		Plugin::registerClass('PluginProcedimientosTipoaccion'); // Tipos de acciones
		Plugin::registerClass('PluginProcedimientosMarcador'); // Marcador
		Plugin::registerClass('PluginProcedimientosProcedimientos_Items'); // Elementos de Procedimiento
		array_push($CFG_GLPI["document_types"], 'PluginProcedimientosAccion');
		array_push($CFG_GLPI["document_types"], 'PluginProcedimientosProcedimiento');
		
		if (Session::haveRight("plugin_procedimientos",READ)) {
			$PLUGIN_HOOKS['menu_toadd']['procedimientos'] = array('config' => 'PluginProcedimientosConfig');
		}
		// Clase PluginProcedimientosProcedimientos
		$PLUGIN_HOOKS['submenu_entry']['procedimientos']['options']['procedimiento'] = array(
			'title' => __('Clases', 'procedimiento'),
			'page'  =>'/plugins/procedimientos/front/procedimientos.php',
			'links' => array(
				'search' => '/plugins/procedimientos/front/procedimientos.php',
				'add'    =>'/plugins/procedimientos/front/procedimientos.form.php'
		)); 
		// Clase PluginProcedimientosAccion
		$PLUGIN_HOOKS['submenu_entry']['procedimientos']['options']['accion'] = array(
			'title' => __('Clases', 'accion'),
			'page'  =>'/plugins/procedimientos/front/accion.php',
			'links' => array(
				'search' => '/plugins/procedimientos/front/accion.php',
				'add'    =>'/plugins/procedimientos/front/accion.form.php'
		)); 
		// Clase PluginProcedimientosLink
		$PLUGIN_HOOKS['submenu_entry']['procedimientos']['options']['link'] = array(
			'title' => __('Clases', 'link'),
			'page'  =>'/plugins/procedimientos/front/link.php',
			'links' => array(
				'search' => '/plugins/procedimientos/front/link.php',
				'add'    =>'/plugins/procedimientos/front/link.form.php'
		)); 		
			
													
		// Captura el evento crear un ticket para comprobar si debe aplicar procedimiento automáticamente.
		$PLUGIN_HOOKS['item_add']['procedimientos'] = array('Ticket'=>'plugin_procedimientos_add_Ticket');		

   // *******************************************************************************************
   //  [INICIO] [CRI] JMZ18G ASOCIAR AL PLUGIN EL DESTINO DEL TICKET DE FORMCREATOR 
   // *******************************************************************************************    
   $plugin = new Plugin();  
   if($plugin->isInstalled('formcreator') || $plugin->isActivated('formcreator')) {

   // Captura el evento de modificar una tarea en un ticket para comprobar su estado.
   // Captura el evento modificar un ticket para comprobar si debe aplicar procedimiento automáticamente.
   // Captura del evento modificar estado de validación de un ticket.
   // Captura del evento modificar el destino de un formulario de formcreator.
   $PLUGIN_HOOKS['item_update']['procedimientos'] = array('TicketTask'=>'plugin_procedimientos_update_TicketTask',
                                                          //'Ticket'=>'plugin_procedimientos_update_Ticket', // [CRI] JMZ18G Este proceso se ha migrado a formcreator form.class.php función updateTicketFromForm()
                                                          'TicketValidation'=>'plugin_procedimientos_update_Validation',
                                                          'PluginFormcreatorTargetTicket'=>'plugin_procedimientos_update_TargetTicket'
                                                         );            
   // Captura del evento modificar el relación entre pedido de catálogo y procedimiento.
   // CONTROLAMOS LA PRE-ACTUALIZACIÓN DE UNA TAREA PARA AÑADIR COMO TECNICO DE LA TAREA AL USUARIO QUE LA CIERRA Y PARA AÑADIR AL USUARIOS QUE LA MODIFICA EN CASO DE SER CERRADA CON CHEKBOX														
   $PLUGIN_HOOKS['pre_item_update']['procedimientos'] = [PluginProcedimientosProcedimiento_Form::class => 'plugin_procedimientos_update_RelationForm',
                                                         'TicketTask'                                  => 'plugin_procedimientos_pre_update_TicketTask',
                                                         'ProblemTask'                                 => 'plugin_procedimientos_pre_update_TicketTask',
                                                         'ChangeTask'                                  => 'plugin_procedimientos_pre_update_TicketTask']; 
                                                      

   $PLUGIN_HOOKS['pre_item_add']['procedimientos']    = ['TicketTask'                                  => 'plugin_procedimientos_pre_add_TicketTask',
                                                         'ProblemTask'                                 => 'plugin_procedimientos_pre_add_TicketTask',
                                                         'ChangeTask'                                  => 'plugin_procedimientos_pre_add_TicketTask'];                                                          
   

   // Captura del evento eliminar un destino de un formulario de formcreator.    
   // Captura del evento eliminar el relación entre pedido de catálogo y procedimiento. 
   $PLUGIN_HOOKS['pre_item_purge']['procedimientos'] = [PluginFormcreatorTargetTicket::class => 'plugin_procedimientos_delete_TargetTicket',
                                                        PluginProcedimientosProcedimiento_Form::class => 'plugin_procedimientos_delete_RelationForm'];   
      
   } else {

	// CONTROLAMOS LA PRE-ACTUALIZACIÓN DE UNA TAREA PARA AÑADIR COMO TECNICO DE LA TAREA AL USUARIO QUE LA CIERRA Y PARA AÑADIR AL USUARIOS QUE LA MODIFICA EN CASO DE SER CERRADA CON CHEKBOX														
	$PLUGIN_HOOKS['pre_item_update']['procedimientos'] = array('TicketTask'=>				'plugin_procedimientos_pre_update_TicketTask');
			

   // Captura el evento de modificar una tarea en un ticket para comprobar su estado.
   // Captura el evento modificar un ticket para comprobar si debe aplicar procedimiento automáticamente.
   // Captura del evento modificar estado de validación de un ticket.
   $PLUGIN_HOOKS['item_update']['procedimientos'] = array('TicketTask'=>'plugin_procedimientos_update_TicketTask',
                                                          'Ticket'=>'plugin_procedimientos_update_Ticket',
                                                          'TicketValidation'=>'plugin_procedimientos_update_Validation');	         

   }

// *******************************************************************************************
//  [FINAL] [CRI] JMZ18G ASOCIAR AL PLUGIN EL DESTINO DEL TICKET DE FORMCREATOR 
// *******************************************************************************************      

		// Acciones masivas
		$PLUGIN_HOOKS['use_massive_action']['procedimientos'] = 1;
		
		$PLUGIN_HOOKS['add_css']['procedimientos'][]           = 'css/procedimientos.css';
		
		$PLUGIN_HOOKS['add_javascript']['procedimientos'][] = "js/redips-drag-min.js";
		$PLUGIN_HOOKS['add_javascript']['procedimientos'][] = 'js/drag-procedimientos-row.js';
	}
   return $PLUGIN_HOOKS;
}


// Get the name and the version of the plugin
function plugin_version_procedimientos() {

   return array('name'          => _n('Procedimientos' , 'Procedimientos' ,2, 'Procedimientos'),
                'version'        => '4.2.0',  //  [CRI] JMZ18G [CRI] - 06/05/2022 Añadir accion Eliminar Técnicos A LA 4.1.4
                'license'        => 'AGPL3',
                'author'         => '<a href="http://www.carm.es">CARM</a>',
                'homepage'       => 'http://www.carm.es',
                'requirements'   => ['glpi' => ['min' => '11.0', 'max' => '12.0']]);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_procedimientos_check_prerequisites() {

   // GLPI must be at least 9.1.3
   if (version_compare(GLPI_VERSION,'9.1.3','lt')) {
      echo "This plugin requires GLPI >= 9.1.3";
      return false;
   } else {
			return true;
   }
   return false;
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_procedimientos_check_config($verbose=false) {
   if (true) {
      // Always true ...
      return true;
   }

   if ($verbose) {
      _e('Installed / not configured', 'procedimientos');
   }
   return false;
}


/**
 * Generate unique id for form based on server name, glpi directory and basetime
 **/
function plugin_procedimientos_getUuid() {

   //encode uname -a, ex Linux localhost 2.4.21-0.13mdk #1 Fri Mar 14 15:08:06 EST 2003 i686
   $serverSubSha1 = substr(sha1(php_uname('a')), 0, 8);
   // encode script current dir, ex : /var/www/glpi_X
   $dirSubSha1    = substr(sha1(__FILE__), 0, 8);

   return uniqid("$serverSubSha1-$dirSubSha1-", true);
}

/**
 * Retrieve an item from the database
 *
 * @param $item instance of CommonDBTM object
 * @param $field field of object's table to search in
 * @param $value value to search in provided field
 *
 * @return true if succeed else false
**/
function plugin_porcedimientos_getFromDBByField(CommonDBTM $item, $field = "", $value = "") {
   global $DB;

   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value) == 0
           || $value === 0)) {
      return false;
   }

   $field = $DB->escape($field);
   $value = $DB->escape($value);
									
      //[INICIO] CRI - JMZ18G getFromDBByQuery es una función obsoleta.
        $found =  $item->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               $item::getTable().".".$field => $value              
            ]
         ],
         'ORDER' => ['id DESC'],
         'LIMIT' => 1
      ]);
	 //[FINAL] CRI - JMZ18G getFromDBByQuery es una función obsoleta.

									
 /*  $found = $item->getFromDBByQuery("WHERE `".$item::getTable()."`.`$field` = '"
                                    .$value."' LIMIT 1");*/									

   if ($found) {
      return $item->getID();
   } else {
      return false;
   }
}

/**
 * Retrieve an item from the database with double condition
 *
 * @param $item instance of CommonDBTM object
 * @param $field field of object's table to search in
 * @param $value value to search in provided field
 *
 * @return true if succeed else false
**/
function plugin_porcedimientos_getFromDBByField_2(CommonDBTM $item, $field_1 = "", $value_1 = "", $field_2 = "", $value_2 = "" ) {
   global $DB;

   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value_1) == 0
           || $value_1 === 0)) {
      return false;
   }
   
   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value_2) == 0
           || $value_2 === 0)) {
      return false;
   }   

   $field_1 = $DB->escape($field_1);
   $value_1 = $DB->escape($value_1);
   $field_2 = $DB->escape($field_2);
   $value_2 = $DB->escape($value_2);
   
   
      //[INICIO] CRI - JMZ18G getFromDBByQuery es una función obsoleta.
        $found =  $item->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               $item::getTable().".".$field_1 => $value_1,
			   $item::getTable().".".$field_2 => $value_2 			   
            ]
         ],
         'ORDER' => ['id DESC'],
         'LIMIT' => 1
      ]);
	 //[FINAL] CRI - JMZ18G getFromDBByQuery es una función obsoleta.
   
   
 /*  $found = $item->getFromDBByQuery("WHERE `".$item::getTable()."`.`$field_1` = '"
                                    .$value_1."' and `".$item::getTable()."`.`$field_2` = '"
                                    .$value_2."' LIMIT 1");*/
									
   if ($found) {
      return $item->getID();
   } else {
      return false;
   }
}


/**
 * Retrieve an item from the database with triple condition
 *
 * @param $item instance of CommonDBTM object
 * @param $field field of object's table to search in
 * @param $value value to search in provided field
 *
 * @return true if succeed else false
**/
function plugin_porcedimientos_getFromDBByField_3(CommonDBTM $item, $field_1 = "", $value_1 = "", $field_2 = "", $value_2 = "", $field_3 = "", $value_3 = "" ) {
   global $DB;

   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value_1) == 0
           || $value_1 === 0)) {
      return false;
   }
   
   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value_2) == 0
           || $value_2 === 0)) {
      return false;
   }  

   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value_3) == 0
           || $value_3 === 0)) {
      return false;
   }   

   $field_1 = $DB->escape($field_1);
   $value_1 = $DB->escape($value_1);
   $field_2 = $DB->escape($field_2);
   $value_2 = $DB->escape($value_2);
   $field_3 = $DB->escape($field_3);
   $value_3 = $DB->escape($value_3);


   //[INICIO] CRI - JMZ18G getFromDBByQuery es una función obsoleta.
        $found =  $item->getFromDBByRequest([
         'WHERE' => [
            'AND' => [
               $item::getTable().".".$field_1 => $value_1,
			   $item::getTable().".".$field_2 => $value_2,
			   $item::getTable().".".$field_3 => $value_3   			   
            ]
         ],
         'ORDER' => ['id DESC'],
         'LIMIT' => 1
      ]);
	 //[FINAL] CRI - JMZ18G getFromDBByQuery es una función obsoleta.
   
   
  /* $found = $item->getFromDBByQuery("WHERE `".$item::getTable()."`.`$field_1` = '"
                                    .$value_1."' and `".$item::getTable()."`.`$field_2` = '"
                                    .$value_2."' and `".$item:*/
	

   if ($found) {
      return $item->getID();
   } else {
      return false;
   }
}

//[INICIO] [CRI] [JMZ18G] FUNCIÓN QUE COMPRUEBA QUE EL TENCNICO DE LA TAREA DE UN TICKET PERTENECE AL GRUPO ASIGNADO EN LA TAREA

function aviso_grupo($item) {

global $CFG_GLPI;

if (((isset($item->input["users_id_tech"])) or (isset($item->fields["users_id_tech"])))
   and 
   ((isset($item->input["groups_id_tech"])) or (isset($item->fields["groups_id_tech"])))) {

$user_task = ((isset($item->input["users_id_tech"])) ? $item->input["users_id_tech"] : $item->fields["users_id_tech"] );   

$group_task = ((isset($item->input["groups_id_tech"])) ? $item->input["groups_id_tech"] : $item->fields["groups_id_tech"] );

if (($group_task>0) and ($user_task>0)) {

   $groups = Group_User::getUserGroups($user_task);

   $is_group   = false;
   $name_group = "";

   foreach ($groups as $group => $group_item){

      if($group_task == $group_item['id']){

         $is_group   = true;
         $name_group = $group_item['name'];

         break;
      }

   }

   if (!$is_group) {

      
      $text= 'La tarea está asignada a un usuario ('
      . getUserName($user_task, 1).
      ") que no pertenece al grupo ".Dropdown::getDropdownName('glpi_groups',
      $group_task);

      $tabla='<table border="0">
      <tr>
      <td align="right"><img style="vertical-align:middle;" alt="" src="'.$_SESSION["glpiroot"].'/plugins/avisos/imagenes/system-attention-icon.png"></td>
      <td class="left">
      <font color="red" SIZE="4"> ADVERTENCIA </font>	
      </td>
      </tr>		
      <tr>				
      <td colspan="2" class="center">
      --------------------------------------------------------------------<br>
      <strong>'.$text.'</strong>
      --------------------------------------------------------------------<br>
      </td>
      </tr>				  
   </table>';   

   Session::addMessageAfterRedirect(__($tabla, 'procedimientos'),true, WARNING, false);						
      
   }

}

}

}

//[FINAL] [CRI] [JMZ18G] FUNCIÓN QUE COMPRUEBA QUE EL TENCNICO DE LA TAREA DE UN TICKET PERTENECE AL GRUPO ASIGNADO EN LA TAREA

?>
