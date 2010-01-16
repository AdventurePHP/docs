<?php
   import('tools::http','HeaderManager');
   import('sites::apf::pres::http','HttpCacheManager');

   /**
    * @package sites::apf::biz::actions::jscss
    * @class JsCssAction
    *
    * Delivers compressed versions of js and css files.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 15.12.2009<br />
    * Version 0.2, 23.12.2009 (Introduced js file delivery)<br />
    */
   class JsCssAction extends AbstractFrontcontrollerAction {

      public function JsCssAction(){
      }

      /**
       * @public
       *
       * Streams the desired js or css file.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 15.12.2009<br />
       */
      public function run(){
         $fileName = $this->__Input->getFileName();
         if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') > 0 || substr_count($_SERVER['HTTP_ACCEPT_ENCODING'],'deflate') > 0)){
            ob_start('ob_gzhandler');
         }
         else {
            ob_start();
         }
         if($this->__Input->isCssFileRequested()){
            HttpCacheManager::sendCssCacheHeaders();
         }
         else {
            HttpCacheManager::sendJsCacheHeaders();
         }
         echo file_get_contents($fileName);
         ob_end_flush();
         exit(0);
      }
      
    // end class
   }
?>