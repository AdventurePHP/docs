<?php
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres
   *  @class quicknavi_controller
   *
   *  Implements the document controller for the quicknavi view.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 30.08.2008<br />
   */
   class quicknavi_controller extends baseController
   {

      function quicknavi_controller(){
      }


      /**
      *  @public
      *
      *  Implements the transformContent() method. Displays the quicknavi content.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 30.08.2008<br />
      *  Version 0.2, 17.09.2008 (Changed the functionality to fit the new model structure)<br />
      */
      function transformContent(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // include the quicknavicontent of the model's quicknavi file in the current object
         $this->__Content .= file_get_contents($Model->getAttribute('content.filepath').'/quicknavi/'.$Model->getAttribute('page.quicknavifilename'));

       // end function
      }

    // end class
   }
?>