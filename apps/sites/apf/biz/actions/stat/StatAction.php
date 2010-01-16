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

      /**
       * @private
       * Define this action as a post transform action.
       */
      var $__Type = 'posttransform';

      function StatAction() {
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
       */
      function run() {

         // get model
         $model = &Singleton::getInstance('APFModel');

         // get page language and name
         $pageLang = $model->getLanguage();
         $pageName = $model->getTitle();

         // write statistic entry
         $sM = &$this->__getServiceObject('3rdparty::statistics::biz','StatManager');
         $sM->writeStatistic($pageName,$pageLang);

       // end function
      }

    // end class
   }
?>