<?php
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content::voting','voting_base_controller');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content::voting
   *  @class voting_v1_controller
   *
   *  Documentcontroller für das Anzeigen des Rahmens des Voting-Moduls.<br />
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 17.05.2008<br />
   */
   class voting_v1_controller extends voting_base_controller
   {

      function voting_v1_controller(){
      }


      /**
      *  @public
      *
      *  Displays the voting area.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      */
      function transformContent(){
         $Template__Text = &$this->__getTemplate('Text_'.$this->__Language);
         $this->setPlaceHolder('Content',$Template__Text->transformTemplate());
       // end function
      }

    // end function
   }
?>