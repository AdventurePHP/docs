<?php
   import('sites::apf::pres::controller::release','release_base_controller');

   /**
    * @package sites::apf::pres::controller::release
    * @class current_release_controller
    *
    * Displays the current release (stable) package.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.12.2009<br />
    */
   class current_release_controller extends release_base_controller {

      public function current_release_controller(){
         parent::release_base_controller();
      }

      public function transformContent(){

         // initialize output buffer
         $bufferReleases = (string)'';

         // read releases
         $releases = $this->getAllReleases();

         // display only stable releases
         if(count($releases) > 0){
            for($i = 0; $i < count($releases); $i++){
               if($this->isStableRelease($releases[$i])){
                  $bufferReleases .= $this->displayRelease($releases[$i]);
                  break;
               }
            }
         }

         // display content
         $this->setContentPlaceHolder($bufferReleases);
         
       // end function
      }

    // end class
   }
?>