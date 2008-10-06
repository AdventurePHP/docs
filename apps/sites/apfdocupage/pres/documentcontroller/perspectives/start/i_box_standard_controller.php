<?php
   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::start
   *  @class i_box_standard_controller
   *
   *  Implements the standard document controller for the info boxes.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 06.10.2008<br />
   */
   class i_box_standard_controller extends baseController
   {

      function i_box_standard_controller(){
      }


      /**
      *  @public
      *
      *  Displays the language dependent template. Naming convention: "Content_[de|en]".
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 06.10.2008<br />
      */
      function transformContent(){

         // get standard template
         $Template__Content = &$this->__getTemplate('Content_'.$this->__Language);

         // display template
         $Template__Content->transformOnPlace();

       // end function
      }

    // end class
   }
?>