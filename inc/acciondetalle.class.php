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

// Class of the defined type
class PluginProcedimientosAcciondetalle extends CommonDBTM {
   
   public $dohistory=true;
   static protected $notable=true;
   static $rightname = "plugin_procedimientos";
   
   // Permisos
   public static function canCreate() {

      return (Session::haveRight(self::$rightname, CREATE));
   }

   public static function canView() {

      return (Session::haveRight(self::$rightname, READ));
   }

   public function canViewItem() {

      return (Session::haveRight(self::$rightname, READ));
   }

   public function canCreateItem() {
     return (Session::haveRight(self::$rightname, CREATE));
   }

   public function canUpdateItem() {

      return (Session::haveRight(self::$rightname, UPDATE));
   }

   public function canPurgeItem() {

      return (Session::haveRight(self::$rightname, PURGE));
   }

   public static function canUpdate() {
     return (Session::haveRight(self::$rightname, UPDATE));
   }

  public static function canPurge(): bool {
      return (Session::haveRight(self::$rightname, PURGE));
   }      
  
   static function getTypeName($nb=0) {
		return _n('Detalles','Detalles',$nb, 'detalles');
   }    

   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      switch ($item->getType()) {
         case 'PluginProcedimientosAccion' :
            self::showForAccion($item);
      }
      return true;
   }

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $CFG_GLPI;

         switch ($item->getType()) {
            case 'PluginProcedimientosAccion' :
							// [INICIO] [CRI] - JMZ18G - 06/05/2022 Añadir accion Eliminar Técnicos
							if ($item->fields['plugin_procedimientos_tipoaccions_id']<6){ // La accion eliminar técnicos carece de la pestaña detalle.
								return _n('Detalles', 'Detalles', Session::getPluralNumber());
							}
              // [FINAL] [CRI] - JMZ18G - 06/05/2022 Añadir accion Eliminar Técnicos
         }
      return '';
   }
   
 // Pestaña "Detalles" en una acción
  static function showForAccion(PluginProcedimientosAccion $accion) {
      global $DB, $CFG_GLPI;

    $instID = $accion->fields['id'];
	$entities_id = $accion->fields['entities_id'];
	$tipo = $accion->fields['plugin_procedimientos_tipoaccions_id'];

    $rand   = mt_rand();
    echo "<form name='acciondetalle_form$rand' id='acciondetalle_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL("PluginProcedimientosAccion")."'>"; 
      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";

	if(isset($tipo)){
 		echo "<input type='hidden' name='accion_id' value='$instID'>";
		echo "<input type='hidden' name='tipoaccion' value='$tipo'>";     
		
		switch ($tipo){      
			case 1: // Tipo Tarea
				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th>Detalles de la tarea</th>";
				echo "</tr>";									

$select= "SELECT * from `glpi_plugin_procedimientos_tareas` where `plugin_procedimientos_accions_id`= $instID";	
						 
						// echo $select;
				$result = $DB->query($select);
					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
          //$row = $DB->fetch_array($result);
						$row = $DB->fetchAssoc($result);
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	
				$taskcategories_id = $row['taskcategories_id'];			

				// Plantilla de tarea.
				echo "<tr class='tab_bg_1'>";
				echo "<td style='width:100px'>"._n('Task template', 'Task templates', 1)."</td><td>";
				
				          echo "<div class='fa-label'>
            <i class='fas fa-bezier-curve fa-fw'
               title='"._n('Task template', 'Task templates', 1)."'></i>";
			   
				TaskTemplate::dropdown(array('value'     => $row["tasktemplates_id"],
                                     'entity'    => $entities_id));	   
			   
            echo "</div>";
				

				echo "</td>";
				echo "</tr>";	
				
				echo "<tr>"
                       . "<td width='10%'>".__('Category').": </td>"
                       . "<td width='90%'>";
					   
          echo "<div class='fa-label'>
            <i class='fas fa-filter fa-fw'
               title='".__('Category')."'></i>";
			   
						Dropdown::show('TaskCategory', array('name' =>'taskcategories_id',
					       'value'  => $taskcategories_id));		   
			   
            echo "</div>";	
					      					   
				echo "</td>"."</tr> ";
				   						
				// Estado tarea
				echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Status')."</td><td>";
				if (!isset($row["state"])){
					$row["state"] = 1;
				}
				
          echo "<div class='fa-label'>
            <i class='fas fa-calendar-check fa-fw'
               title='".__('Status')."'></i>";
			   
		Planning::dropdownState("state", $row["state"]);		   
			   
            echo "</div>";				
				
				
				echo "</td></tr>\n";
									
				// Es privado.
				echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Private')."</td>";
				echo "<td>";
				if (!isset($row["is_private"])){
					$row["is_private"] = 1;					
				}
				
         echo "<div class='fa-label'>
            <i class='fas fa-lock fa-fw' title='".__('Private')."'></i>";
         $rand = mt_rand();
         echo "<span class='switch pager_controls'>
            <label for='is_privateswitch$rand' title='".__('Private')."'>
               <input type='hidden' name='is_private' value='0'>
                              <input type='checkbox' id='is_privateswitch$rand' name='is_private' value='1'".
                     ($row["is_private"]
                        ? "checked='checked'"
                        : "")."
               >
               <span class='lever'></span>
            </label>
         </span>";
         echo "</div>";					
				
				
				echo "</td>";
				echo "</tr>";
				
				// [INICIO] [CRI] - JMZ18G - 06/11/2020 Añadir actiontime al detalle de la tarea
				// Duración de la tarea
				echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Duration')."</td><td>";
				
          echo "<div class='fa-label'>
            <i class='fas fa-clock fa-fw'
               title='".__('Duration')."'></i>";
			   
            $toadd = [];
            for ($i=9; $i<=100; $i++) {
               $toadd[] = $i*HOUR_TIMESTAMP;
            }
            Dropdown::showTimeStamp(
               "actiontime", [
                  'min'             => 0,
                  'max'             => 8*HOUR_TIMESTAMP,
                  'value'           => $row["actiontime"],
                  'addfirstminutes' => true,
                  'inhours'         => true,
                  'toadd'           => $toadd
               ]
            );			   
			   
            echo "</div>";				
				
				
				echo "</td></tr>\n";
				// [FINAL] [CRI] - JMZ18G - 06/11/2020 Añadir actiontime al detalle de la tarea				
				
								// INFORGES - emb97m - 12/03/2018  - Nuevos campos de tareas 9.1.6				
				echo "<tr class='tab_bg_1'>";
			    echo "<td>".__('By')."</td>";
				echo "<td colspan='2'>";
				if (isset($row["users_id_tech"])) { $users_id_tech=$row["users_id_tech"]; } else { $users_id_tech=0; }
				// Técnico tarea				
				
				$rand_user          = mt_rand();
				$params             = array('name'   => "users_id_tech",
                                  'value'  => (($instID > -1)
                                                ?$users_id_tech
                                                :Session::getLoginUserID()),
                                  'right'  => "own_ticket",
                                  'rand'   => $rand_user,
                                  'entity' => $entities_id,
                                  'width'  => '');

          echo "<div class='fa-label'>
            <i class='fas fa-user fa-fw'
               title='"._n('User', 'Users', 1)."'></i>";
			   
		User::dropdown($params);		   
			   
            echo "</div>";	
				
				// Grupo técnico tarea.
				
				$rand_group = mt_rand();
				if (isset($row["groups_id_tech"])) { $groups_id_tech=$row["groups_id_tech"]; } else { $groups_id_tech=0; }
				$params     = array('name'      => "groups_id_tech",
                          'value'     => (($instID > -1)
                                          ?$groups_id_tech
                                          :Dropdown::EMPTY_VALUE),                          
						  'condition' => ['is_task' => 1],
                          'rand'      => $rand_group,
                          'entity'    => $entities_id
						  );
						  
          echo "<div class='fa-label'>
            <i class='fas fa-users fa-fw'
               title='"._n('Group', 'Groups', 1)."'></i>";
			   
		Group::dropdown($params);	   
			   
            echo "</div>";						  
						  
				
				echo "<td></tr>";
				
				
				// Documento
				echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Categoria / Documento')."</td>";
				echo "<td colspan='3'>";				
				if (isset($row['documents_id'])) {	$documents_id = $row['documents_id']; } else  { $documents_id = 0; }
				if (isset($row['documentcategories_id'])) {	$documentcategories_id = $row['documentcategories_id']; } else { $documentcategories_id = 0;  }
				
				if (isset($row['categoria'])) {	$categoria = $row['categoria']; } else  { $categoria = ""; }
				if (isset($row['documento'])) {	$documento = $row['documento']; } else { $documento = "";  }	
				if (isset($row['id'])) {	$id = $row['id']; } else { $id = "";  }					

			 $used  = array($documents_id, $documentcategories_id);
	
						   
          echo "<div class='fa-label'>
            <i class='fas fa-folder-plus fa-fw'
               title='".__('Categoria / Documento')."'></i>";
			   
$rand=Document::dropdown(array('entity' => $entities_id , 'used'   => $used));	   
			   
            echo "</div>";	
	
	         	
			 
				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th>Documentos Vinculados:</th>";
				echo "</tr>";				
			 
			 				$select= "SELECT b.name as documento, c.name as categoria, a.* from `glpi_plugin_procedimientos_documents` a
				left join `glpi_documents` b on a.documents_id= b.id
				left join `glpi_documentcategories` c on a.documentcategories_id= c.id
				where a.`items_id`= $instID and a.itemtype='PluginProcedimientosTarea'";
				//echo $select;
				
				   $result = $DB->query($select);
   $number = $DB->numrows($result);

   if ($number) {
	   
	   			echo "<tr class='tab_bg_1'>";
				echo "<td colspan='10'>";
				echo "<table border='0' width='100%'>";
	   
      while ($data = $DB->fetchassoc($result)) {
         $document_id = $data['id'];
         $categoria = $data['categoria'];
		 $documento = $data['documento'];

				echo "<tr id='fila_".$document_id."' >";
				echo "<td style='background-color: #d2e3f7'>".__('Category')."</td>";
				echo "<td style='background-color: #f9f9f9' width='30%'><span id='categoria_".$document_id."'>".$categoria."</span></td>";
				echo "<td style='background-color: #d2e3f7' width='5%'>".__('Document').":</td>";
				echo "<td style='background-color: #f9f9f9' width='65%'><span id='documento_".$document_id."'>".$documento."</span></td>";				
				echo "<td style='background-color: #fbf4f4' id='".$document_id."' class='borrar' width='2%'><a src='#'><img style='cursor: pointer' src='".$CFG_GLPI["root_doc"]."/plugins/procedimientos/pics/equis.png'></a></td>";				 				
				echo "</tr>";
      }
	  			echo "</table>";
				echo "</td>";	
				echo "</tr>";	

				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th></th>";
				echo "</tr>";

				echo "<tr>";
				echo "<td width='10'>&nbsp;</td>";
				echo "<td></td>";
				echo "</tr>";
				
				}			 																		
									 									 
				//echo Html::file();	

		/*		echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th>Detalles del escalado</th>";
				echo "</tr>";	*/			
				
				// FIN INFORGES grupo y usuario de tarea.
				
echo '<div style="display:none; background-color:#f9f9f9;" id="dialog-confirm" title="DOCUMENTOS RELACIONADOS">
  <p>
  <img style="position:absolute; top:10%;" src="'.$CFG_GLPI["root_doc"].'/pics/warning.png">
  <span style="position:absolute; margin-left:45px; top:15%;"><strong>Vas a eliminar la siguiente vinculación:</strong></span> 
  <br><br> <hr><br>
  <font color="blue"><strong><span id="documento">'.$documento.'</span></strong></font>
  <br><br> <hr><br>
  <strong>¿Estas Seguro?</strong>
  </p>
</div>';

// [INICIO] [CRI] - JMZ18G - 06/11/2020 SCRIPT HABILITA Ó DESHABILITA Y VACIA LOS CAMPOS SI LA TAREA TIENE PLANTILLA ASOCIADA	
   echo Html::scriptBlock('
	  
			var x;
			x=$(document);
			x.ready(inicializar);

			function inicializar(){				
				muestrame(valor());
			}

			function valor(){
				var val=$("select[name=tasktemplates_id]").val();
				var disable = false;
				if (val>0) { disable = true; } else { disable = false; }
				return disable;
			}
			
			function muestrame(valor){
				
				$("select[name=taskcategories_id]").prop( "disabled", valor );
				$("select[name=users_id_tech]").prop( "disabled", valor );
				$("select[name=actiontime]").prop( "disabled", valor );
				$("select[name=groups_id_tech]").prop( "disabled", valor );
				$("select[name=state]").prop( "disabled", valor );
				$("input[name=is_private]").prop( "disabled", valor );
				
				if (valor) { 
				$("select[name=taskcategories_id]").trigger("setValue", 0);
				$("select[name=users_id_tech]").trigger("setValue", 0);
				$("select[name=actiontime]").trigger("setValue", 0);
				$("select[name=groups_id_tech]").trigger("setValue", 0);
				$("select[name=state]").trigger("setValue", 0);
                $("input[name=is_private]").prop("checked", false);
				}
			
			}				
					  
			$("select[name=tasktemplates_id]").on("change", function(e){ 
				muestrame(valor());
			});						  
      ');			
// [FINAL] [CRI] - JMZ18G - 06/11/2020 SCRIPT HABILITA Ó DESHABILITA Y VACIA LOS CAMPOS SI LA TAREA TIENE PLANTILLA ASOCIADA	
							
				echo "<script>$('.borrar').on('click', function(e){ 
				var id=$(this).attr('id');
				
 $( '#documento' ).text($( '#documento_'+id ).text());
 
    $( '#dialog-confirm' ).dialog({
      resizable: false,
      height: 'auto',
      width: 400,
      modal: true,
      buttons: {
        'ELIMINAR': function() {
			
$.post('". $CFG_GLPI["root_doc"]."/plugins/procedimientos/ajax/documentos.php', { id:id, valid_id:'".$_SESSION['valid_id']."' }, function(respuesta){    

		  $( '#dialog-confirm' ).dialog( 'close' );
		  $('#categoria_'+id).fadeOut('slow');
		  $('#documento_'+id).fadeOut('slow');
		  $('#fila_'+id).fadeOut('slow');		 
});	
        },
        CANCELAR: function() {
          $( this ).dialog( 'close' );
        }
      }
    });				
});</script>";	
 
				break;
        
			case 2: // Tipo Escalado
				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th>Detalles del escalado</th>";
				echo "</tr>";
				
				$select= "SELECT * from `glpi_plugin_procedimientos_escalados` where
						 `plugin_procedimientos_accions_id`= $instID";
				$result = $DB->query($select);
					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
          //$row = $DB->fetch_array($result_select);
						$row = $DB->fetchAssoc($result);
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	
				$groups_id_asignado = $row['groups_id_asignado'];		            
				$groups_id_observ = $row['groups_id_observ'];
				$suppliers_id = $row['suppliers_id'];
				$users_id_asignado = $row['users_id_asignado'];	
				$users_id_observ = $row['users_id_observ'];	
				echo "<tr>"
                       . "<td width='15%'>Grupo Asignado: </td>"
                       . "<td>";
					   
          echo "<div class='fa-label'>
            <i class='fas fa-users fa-fw'
               title='"._n('Grupo Asignado', 'Grupos Asignados', 1)."'></i>";
			   
		 Dropdown::show('Group', array('name'=>'groups_id_asignado', 'condition' => ['is_assign' => 1], 'value'  => $groups_id_asignado));
			   
            echo "</div>";					                          
                        echo "</td>"
                   . "</tr> "
                   . "<tr> "
                       . "<td width='15%'>Usuario Asignado: </td>"
                       . "<td >";
					   
          echo "<div class='fa-label'>
            <i class='fas fa-user fa-fw'
               title='"._n('Usuario Asignado', 'Usuarios Asignados', 1)."'></i>";
			   
                              User::dropdown(array('name'   => 'users_id_asignado',
                           'right'  => 'all',
                           'entity' => -1, 'value'  => $users_id_asignado));		   
			   
            echo "</div>";						   
       
                   echo "</tr>"
                   . "<tr> "
                       . "<td width='15%'>Grupo Observador: </td>"    
                       . "<td>";
					   
          echo "<div class='fa-label'>
            <i class='fas fa-users fa-fw'
               title='"._n('Grupo Observador', 'Grupos Observadores', 1)."'></i>";
			   
		  Dropdown::show(getItemTypeForTable('glpi_groups'), array('name'=>'groups_id_observ', 'condition' => ['is_assign' => 1], 'value'  => $groups_id_observ));
			   
            echo "</div>";						                        
                        
                       echo"</td> "
                   . "</tr> "
                   . "<tr> "
                       . "<td width='15%'>Usuario Observador: </td>"
                       . "<td>";
					   
          echo "<div class='fa-label'>
            <i class='fas fa-user fa-fw'
               title='"._n('Usuario Observador', 'Usuarios Observadores', 1)."'></i>";
			   
                        User::dropdown(array('name'   => 'users_id_observ',
                           'right'  => 'all',
                           'entity' => -1, 'value'  => $users_id_observ));	   
			   
            echo "</div>";						   
			
                   echo "</tr>";				   
                   echo "</tr>"
                   . "<tr> "
                       . "<td width='15%'>Proveedor asignado: </td>"    
                       . "<td>";
					   
          echo "<div class='fa-label'>
            <i class='fas fa-suitcase fa-fw'
               title='"._n('Grupo Asignado', 'Grupos Asignados', 1)."'></i>";
			   
		  Dropdown::show(getItemTypeForTable('glpi_suppliers'), array('name'=>'suppliers_id', 'condition' => ['is_deleted' => 0], 'value'  => $suppliers_id));
			   
            echo "</div>";						                         
                      
                       echo"</td> "
                   . "</tr> ";
				   break;
				
			case 3: // Tipo Modificación ticket
				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th>Detalles de la modificación del ticket</th>";
				echo "</tr>";
				
				$select= "SELECT * from `glpi_plugin_procedimientos_updatetickets` where
						 `plugin_procedimientos_accions_id`= $instID";
				$result = $DB->query($select);
					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
          //$row = $DB->fetch_array($result);
						$row = $DB->fetchAssoc($result);
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	
				$requesttypes_id = $row['requesttypes_id'];	
				$status = $row['status'];	
				$itilcategories_id = $row['itilcategories_id'];
				$slts_ttr_id = $row['slts_ttr_id'];
				$type = $row['type'];
				$solutiontemplates_id = $row['solutiontemplates_id'];
				
				echo "<tr>";
				echo "<td colspan='2' class='center'><strong><br> 
					Compara el valor de los campos definidos aqui con los del ticket y si son distintos, los actualiza con el valor establecido.<br> 
					Si el campo no está definido (valor '-----') no lo compara ni actualiza.</strong><br><br>";
				echo "</td></tr>\n";
				
				echo "<tr>";
				echo "<td width='15%'>".__('Status')."</td><td>";				
				$estados = array(0 =>'-----', 1 =>'Nuevo', 2 =>'En curso(Asignada)', 3 =>'En curso(Planificada)', 4=>'En espera', 5 =>'Resuelto', 6=>'Cerrado'); 											
				
			echo "<div class='fa-label'>
            <i class='fas fa-calendar-check fa-fw'
               title='".__('Status')."'></i>";
			   
		Dropdown::showFromArray('status', $estados, array('value'   => $status, 'emptylabel'   => '-----'));
			   
            echo "</div>";	
										
				echo "</td></tr>\n";

				echo "<tr>";
				echo "<td width='15%'>".__('Request source')."</td><td>";
				
         echo "<div class='fa-label'>
            <i class='fas fa-inbox fa-fw'
               title='".__('Request source')."'></i>";
				RequestType::dropdown(array('name'=> 'requesttypes_id', 'value' => $requesttypes_id));
         echo "</div>";					
				
				
				echo "</td></tr>";		 
		 
				echo "<tr>";
				echo "<td width='15%'>".__('Category')."</td><td>";
				
          echo "<div class='fa-label'>
            <i class='fas fa-filter fa-fw'
               title='".__('Category')."'></i>";
			   
				Dropdown::show('ItilCategory', array('name' =>'itilcategories_id',
					       'value'  => $itilcategories_id));
			   
            echo "</div>";	
			

				echo "</td></tr>";					
				
				echo "<tr>";
				echo "<td width='15%'>".__('Type')."</td><td>";
				$types = array(0 =>'-----', 1 =>'Incidencia', 2 =>'Solicitud', 201 =>'Consulta', 202=>'Tarea', 203 =>'Queja', 204=>'Alerta', 205=>'Excepción', 206=>'Sugerencia/Felicitación'); 											
				
		 echo "<div class='fa-label'>
            <i class='fas fa-bookmark fa-fw'
               title='".__('Type')."'></i>";
			   
		 Dropdown::showFromArray('type', $types, array('value'   => $type, 'emptylabel'   => '-----'));			   
			   
            echo "</div>";
				
								
				echo "</td></tr>";	 	

				echo "<tr>";
				echo "<td width='15%'>".__('ANS resoluci&oacute;n')."</td><td>";
				
				          echo "<div class='fa-label'>
            <i class='fas fa-hourglass-end fa-fw'
               title='".__('Asignar plantilla de soluci&oacute;n')."'></i>";
			   
				Dropdown::show('SLM', array('name'=>'slts_ttr_id', 'value'  => $slts_ttr_id)); // jmz18g SLT NO EXISTE EN ESTA VERSIÓN 23/05/2019
				//Dropdown::show('SLT', array('name'=>'slts_ttr_id', 'value'  => $slts_ttr_id, 'condition' => ['type' => 0]));
			   
            echo "</div>";					
				
				
				echo "</td></tr>";	
				
				echo "<tr>";
				echo "<td width='15%'>".__('Asignar plantilla de soluci&oacute;n')."</td><td>";

				          echo "<div class='fa-label'>
            <i class='fas fa-bezier-curve fa-fw'
               title='".__('Asignar plantilla de soluci&oacute;n')."'></i>";
			   
				Dropdown::show('SolutionTemplate', array('name'=>'solutiontemplates_id', 'value'  => $solutiontemplates_id));   
			   
            echo "</div>";				
				
				
				echo "</td></tr>";	
				break;   
				
 			case 4: // Tipo Seguimiento
				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th colspan='3'>Detalles del seguimientos</th>";
				echo "</tr>";
										 
						$select= "SELECT * from `glpi_plugin_procedimientos_seguimientos` where `plugin_procedimientos_accions_id`= $instID";						 
						 
				$result = $DB->query($select);
					// [INICIO] [CRI] [JMZ18G] fetch_array deprecated function	
          //$row = $DB->fetch_array($result);
						$row = $DB->fetchAssoc($result);
					// [FINAL] [CRI] [JMZ18G] fetch_array deprecated function	
				$content = $row['content'];
				if (!isset($row["is_private"])){
					$row["is_private"] = 1;
				}
				$is_private = $row['is_private'];
				$requesttypes_id = $row['requesttypes_id'];
				$followuptypes_id = $row['followuptypes_id'];
				
				echo "<tr>";
				echo "<td rowspan='4' class='middle right'>".__('Description')."</td>";
				echo "<td class='center middle' rowspan='4'>";
				echo "<textarea name='content' cols='70' rows='6'>".$content."</textarea>";
				echo "</td></tr>\n";

				echo "<tr>";
				echo "<td>".__('Source of followup').":</td><td>";

         echo "<div class='fa-label'>
            <i class='fas fa-inbox fa-fw'
               title='".__('Source of followup')."'></i>";
				RequestType::dropdown(array('value' => $requesttypes_id ));
         echo "</div>";											
				echo "</td></tr>\n";

				echo "<tr>";
				echo "<td>".__('Followup type').":</td><td>";
				
	          echo "<div class='fa-label'>
            <i class='fas fa-bookmark fa-fw'
               title='".__('Followup type')."'></i>";
			   
		 Dropdown::show('FollowupType', array('value' => $followuptypes_id));			   
			   
            echo "</div>";											
				echo "</td></tr>\n";		 
		 
				echo "<tr>";
				echo "<td>".__('Private').":</td><td>";
				
         echo "<div class='fa-label'>
            <i class='fas fa-lock fa-fw' title='".__('Private')."'></i>";
         $rand = mt_rand();
         echo "<span class='switch pager_controls'>
            <label for='is_privateswitch$rand' title='".__('Private')."'>
               <input type='hidden' name='is_private' value='0'>
                              <input type='checkbox' id='is_privateswitch$rand' name='is_private' value='1'".
                     ($is_private
                        ? "checked='checked'"
                        : "")."
               >
               <span class='lever'></span>
            </label>
         </span>";
         echo "</div>";											
				echo "</td></tr>";	
				
				// Documento
				echo "<tr class='tab_bg_1'>";
				echo "<td>".__('Categoria / Documento')."</td>";
				echo "<td colspan='3'>";				
				if (isset($row['documents_id'])) {	$documents_id = $row['documents_id']; } else  { $documents_id = 0; }
				if (isset($row['documentcategories_id'])) {	$documentcategories_id = $row['documentcategories_id']; } else { $documentcategories_id = 0;  }
				
				if (isset($row['categoria'])) {	$categoria = $row['categoria']; } else  { $categoria = ""; }
				if (isset($row['documento'])) {	$documento = $row['documento']; } else { $documento = "";  }				
				if (isset($row['id'])) {	$id = $row['id']; } else { $id = "";  }				

			 $used  = array($documents_id, $documentcategories_id);
	
	         $rand=Document::dropdown(array('entity' => $entities_id , 'used'   => $used));	
			 
			 				echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th colspan='3'>Documentos Vinculados</th>";
				echo "</tr>";
				
				
				$select= "SELECT b.name as documento, c.name as categoria, a.* from `glpi_plugin_procedimientos_documents` a
				left join `glpi_documents` b on a.documents_id= b.id
				left join `glpi_documentcategories` c on a.documentcategories_id= c.id
				where a.`items_id`= $instID and a.itemtype='PluginProcedimientosSeguimiento'";
				//echo $select;
				
				   $result = $DB->query($select);
   $number = $DB->numrows($result);

   $values = array();
   if ($number) {
	   
	   			echo "<tr class='tab_bg_1'>";
				echo "<td colspan='10'>";
				echo "<table border='0' width='100%'>";
	   
      while ($data = $DB->fetchassoc($result)) {
         $document_id = $data['id'];
         $categoria = $data['categoria'];
		 $documento = $data['documento'];

				echo "<tr id='fila_".$document_id."' >";
				echo "<td style='background-color: #d2e3f7'>".__('Category')."</td>";
				echo "<td style='background-color: #f9f9f9' width='30%'><span id='categoria_".$document_id."'>".$categoria."</span></td>";
				echo "<td style='background-color: #d2e3f7' width='5%'>".__('Document').":</td>";
				echo "<td style='background-color: #f9f9f9' width='65%'><span id='documento_".$document_id."'>".$documento."</span></td>";				
				echo "<td style='background-color: #fbf4f4' id='".$document_id."' class='borrar' width='2%'><a src='#'><img style='cursor: pointer' src='".$CFG_GLPI["root_doc"]."/plugins/procedimientos/pics/equis.png'></a></td>";				 				
				echo "</tr>";
      }
	  			echo "</table>";
				echo "</td>";	
				echo "</tr>";	

			 	echo "<tr>";
				echo "<th width='10'>&nbsp;</th>";
				echo "<th colspan='3'></th>";
				echo "</tr>";	
				
				}
				
	

echo '<div style="display:none; background-color:#f9f9f9;" id="dialog-confirm" title="DOCUMENTOS RELACIONADOS">
  <p>
  <img style="position:absolute; top:10%;" src="'.$CFG_GLPI["root_doc"].'/pics/warning.png">
  <span style="position:absolute; margin-left:45px; top:15%;"><strong>Vas a eliminar la siguiente vinculación:</strong></span> 
  <br><br> <hr><br>
  <font color="blue"><strong><span id="documento">'.$documento.'</span></strong></font>
  <br><br> <hr><br>
  <strong>¿Estas Seguro?</strong>
  </p>
</div>';			
				
				echo "<script>$('.borrar').on('click', function(e){ 
				var id=$(this).attr('id');
				
 $( '#documento' ).text($( '#documento_'+id ).text());
 
    $( '#dialog-confirm' ).dialog({
      resizable: false,
      height: 'auto',
      width: 400,
      modal: true,
      buttons: {
        'ELIMINAR': function() {
			
$.post('". $CFG_GLPI["root_doc"]."/plugins/procedimientos/ajax/documentos.php', { id:id, valid_id:'".$_SESSION['valid_id']."' }, function(respuesta){    

		  $( '#dialog-confirm' ).dialog( 'close' );
		  $('#categoria_'+id).fadeOut('slow');
		  $('#documento_'+id).fadeOut('slow');
		  $('#fila_'+id).fadeOut('slow');		 
});	
        },
        CANCELAR: function() {
          $( this ).dialog( 'close' );
        }
      }
    });				
});</script>";					
				
				break; 
				
 			case 5: // Tipo Validación
				echo "<tr>";
				echo "<th width='20%'>&nbsp;</th>";
				echo "<th colspan='3'>Detalles de la validaci&oacute;n</th>";
				echo "</tr>";
				$entity = $_SESSION["glpiactive_entity"];
				$select= "SELECT * from `glpi_plugin_procedimientos_validacions` where
						 `plugin_procedimientos_accions_id`= $instID";	
				$comment_submission = null;
						   
				echo "<tr>";
				echo "<td>".__('Usuario de validaci&oacute;n')."</td><td>";	

				$users_id_validate  = array();				
				foreach ($DB->request($select) as $data) {
					array_push($users_id_validate, $data['users_id_validate']);
				}
				
				if ((isset($data['groups_id']))&&($data['groups_id']>0)){
					//$validatortype = 'group';
					$groups_id = $data['groups_id'];	
				} else {	
				//	$validatortype = 'user';
					$groups_id = 0;					
				}

				if ((isset($data['validador']))&&(!empty($data['validador']))){				
					$validador = $data['validador'];	
				} else {					
					$validador = "";					
				}				

				if (isset($data['comment_submission'])) {
					$comment_submission = $data['comment_submission'];
				}
				$params  = array(
                            'entity'             => $entity ,
                            'right'              => 'validate_request',
							'groups_id' 		 => $groups_id,
                            'users_id_validate'  => $users_id_validate,
							'validador'  => $validador);
				//CommonITILValidation::dropdownValidator($params);
				dropdownValidator($params);
				echo "</td></tr>\n";	
				echo "<tr>";
				echo "<td rowspan='4'>".__('Comentarios')."</td>";
				echo "<td rowspan='4'>";
				echo "<textarea name='comment_submission' cols='70' rows='6'>".$comment_submission."</textarea>";
				echo "</td></tr>\n";
				//}
				break;		
		}    
	}   	  
	if (Session::haveRight('plugin_procedimientos',UPDATE)) {
			echo "<tr class='tab_bg_1'>";	
			echo "<td colspan='4' align = 'center'>";
			echo "<input type='submit' name='actualizarDetalle' value='Actualizar' class='submit'>";	
			echo "</td></tr>";	  
	}

    echo "</table>";
	Html::closeForm();
    echo "</div>";
	//echo "</form>"; INFORGES - 24/11/2017

   }
}
?>