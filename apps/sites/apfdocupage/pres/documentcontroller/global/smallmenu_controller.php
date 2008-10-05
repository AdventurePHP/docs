<?php
   /**
   *  @package sites::apfdocupage::pres::documentcontroller::global
   *  @class smallmenu_controller
   *
   *  Implements the document controller for the small navi area.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 31.08.2008<br />
   */
   class smallmenu_controller extends baseController
   {

      function smallmenu_controller(){
      }


      /**
      *  @public
      *
      *  Displays the small menu in language dependent manner.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 31.08.2008<br />
      *  Version 0.2, 05.10.2008 (Fixed reference bug in template adressing)<br />
      */
      function transformContent(){

         // get language dependent menu
         $Template_SmallMenu = &$this->__getTemplate('SmallMenu_'.$this->__Language);

         // display it
         $Template_SmallMenu->transformOnPlace();

       // end function
      }

    // end class
   }
?>