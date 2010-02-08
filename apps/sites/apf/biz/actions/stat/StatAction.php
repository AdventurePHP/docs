<?php
   import('sites::apf::biz','APFModel');
   import('3rdparty::statistics::biz','StatManager');

   /**
    * @package sites::apf::biz::actions::stat
    * @class StatAction
    *
    * Represents the front controller action to log the acces statistics.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 14.10.2008<br />
    */
   class StatAction extends AbstractFrontcontrollerAction {

      private static $REQUEST_URL_INDICATOR = '/url/';

      public function StatAction() {
      }

      /**
       *  @public
       *
       *  Implements the abstract run method. Logs the page access.
       *
       *  @author Christian Achatz
       *  @version
       *  Version 0.1, 14.10.2008<br />
       *  Version 0.2, 15.12.2008 (Introduced new StatManager)<br />
       *  Version 0.3, 08.02.2010 (Switched to introduce a tracking pixel to avoid missing hits)<br />
       */
      function run() {

         // gather input values
         $pageLang = $this->__Input->getLanguage();
         $pageName = $this->__Input->getTitle();
         $pageId = $this->__Input->getPageId();
         $_SERVER['REQUEST_URI'] = $this->getRequestUrl(); // fake request url

         // write statistic entry
         $sM = &$this->__getServiceObject('3rdparty::statistics::biz','StatManager');
         $sM->writeStatistic($pageId.' - '.$pageName,$pageLang);

         // deliver non-cachable image
         $expiresDate = date('r',0);
         $lastModified = date('r',time());
         HeaderManager::send('Content-Type: image/gif',true);
         HeaderManager::send('Cache-Control: no-cache,no-store,must-revalidate',true);
         HeaderManager::send('Pragma: no-cache',true);
         HeaderManager::send('Expires: '.$expiresDate,true);
         HeaderManager::send('Last-Modified: '.$lastModified,true);
         printf('%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%',71,73,70,56,57,97,1,0,1,0,128,255,0,192,192,192,0,0,0,33,249,4,1,0,0,0,0,44,0,0,0,0,1,0,1,0,0,2,2,68,1,0,59);

         exit(0);

       // end function
      }

      /**
       * Evaluates and returnes the request url in a special way, because the
       * tracking pixel has a different request url.
       *
       * @return string The request url.
       *
       * @author Christian Achatz
       * @version
       * Version 0.2, 08.02.2010<br />
       */
      private function getRequestUrl(){
         $start = strpos($_SERVER['REQUEST_URI'],self::$REQUEST_URL_INDICATOR);
         $length = strlen(self::$REQUEST_URL_INDICATOR);
         return urldecode(urldecode(substr($_SERVER['REQUEST_URI'],$start + $length)));
      }

    // end class
   }
?>