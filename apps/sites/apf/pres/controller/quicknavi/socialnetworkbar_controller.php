<?php
   /**
    * @package sites::apf::pres::controller::quicknavi
    * @class socialnetworkbar_controller
    *
    * Fills the dynamic content of the social network bar.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.12.2009<br />
    */
   class socialnetworkbar_controller extends baseController {

      public function transformContent(){
         $reg = &Singleton::getInstance('Registry');
         $currentUrl = $reg->retrieve('apf::core','CurrentRequestURL');
         $this->setPlaceHolder('currentUrl',$currentUrl);
      }

    // end class
   }
?>