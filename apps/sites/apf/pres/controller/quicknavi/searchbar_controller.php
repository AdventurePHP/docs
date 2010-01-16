<?php
   class searchbar_controller extends baseController {
      public function transformContent(){

         if($this->__Language === 'de'){
             $this->setPlaceHolder('searchUrl','/Seite/044-Suche');
         }
         else {
            $this->setPlaceHolder('searchUrl','/Page/044-Search');
         }

      }

   }
?>