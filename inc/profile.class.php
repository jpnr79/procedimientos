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

class PluginProcedimientosProfile extends Profile {

   static $rightname = "profile";

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile') {
            return PluginProcedimientosProcedimiento::getTypeName(2);
      }
      return '';
   }


   static function DisplayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, 
                                    array('plugin_procedimientos' => 0));
         $prof->showForm($ID);
      }
      return true;
   }
   
   static function createFirstAccess($ID) {
      //85
      self::addDefaultProfileInfos($ID,
                                    array('plugin_procedimientos' => 127), true);
   }
   
    /**
    * @param $profile
   **/
  static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (countElementsInTable('glpi_profilerights',
                                            ['profiles_id' => $profiles_id,
                                             'name'        => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
								   
                  if (!countElementsInTable('glpi_profilerights',
                                            ['profiles_id' => $profiles_id,
                                             'name'        => $right])) {									   
									   
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }


   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();	 
         $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')));

   	  }
       
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights($all = false) {
      $rights = array(
          array('itemtype'  => 'PluginProcedimientosProcedimiento',
                'label'     => _n('Procedimiento', 'Procedimientos', 2, 'procedimientos'),
                'field'     => 'plugin_procedimientos'
          ),
      );
      
      return $rights;
   }
   
   
   /**
    * Init profiles
    *
    **/
    
   static function translateARight($old_right) {
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
   }
      
   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable('glpi_profilerights',
                                            ['name' => $data['field']]) == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }
      foreach ($DB->request([
         'FROM' => 'glpi_profilerights',
         'WHERE' => [
            'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
            'name' => ['LIKE', '%plugin_procedimientos%']
         ]
      ]) as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   
  static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }   
}
?>