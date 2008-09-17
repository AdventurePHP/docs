<?php
   /**
   *  @package sites::apfdocupage::pres
   *  @class mainnavi_controller
   *
   *  Implements the document controller for the quicknavi view.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 31.08.2008<br />
   */
   class mainnavi_controller extends baseController
   {

      function mainnavi_controller(){
      }


      /**
      *  @public
      *
      *  Implements the transformContent() method. Displays the mainnavi content.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 31.08.2008<br />
      */
      function transformContent(){

         // get the template reference
         $Template_MainNavi = &$this->__getTemplate('MainNavi_'.$this->__Language);

         // display template
         $Template_MainNavi->transformOnPlace();

       // end function
      }

    // end class
   }
?>