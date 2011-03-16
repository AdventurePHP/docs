<?php
   import('tools::request','RequestHandler');
   import('tools::http','HeaderManager');

   /**
    * @package sites::apf::biz::actions::changelang
    * @class ChangeLangAction
    *
    * Represents the front controller action to change the site's language.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 15.12.2009<br />
    */
   class ChangeLangAction extends AbstractFrontcontrollerAction {

      private static $LANG = 'lang';
      private static $PAGE_ID = 'pageid';

      public function ChangeLangAction(){
      }

      /**
       * @public
       *
       * Changes the language by redirecting to the appropriate url.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 15.12.2009<br />
       */
      public function run(){

         $targetLang = RequestHandler::getValue(self::$LANG);
         $targetPageId = RequestHandler::getValue(self::$PAGE_ID);

         $urlMan = &$this->getServiceObject('sites::apf::biz','UrlManager');
         $forwardUrl = $urlMan->generateLink($targetPageId,$targetLang);

         HeaderManager::forward($forwardUrl);
         
       // end function
      }
      
    // end class
   }
?>