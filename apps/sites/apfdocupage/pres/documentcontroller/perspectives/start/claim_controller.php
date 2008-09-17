<?php
   class claim_controller extends baseController
   {

      function claim_controller(){
      }


      function transformContent(){
         $Template__Claim = &$this->__getTemplate('Claim_'.$this->__Language);
         $Template__Claim->transformOnPlace();
       // end function
      }

    // end class
   }
?>