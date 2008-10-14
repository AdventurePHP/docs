<?php
   import('sites::apfdocupage::biz','APFModel');
   import('sites::apfdocupage::biz','StatManager');


   /**
   *  @package sites::apfdocupage::biz::actions::stat
   *  @class StatAction
   *
   *  Represents the front controller action to log the acces statistics.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 14.10.2008<br />
   */
   class StatAction extends AbstractFrontcontrollerAction
   {

      /**
      *  @private
      *  Define this action as a post transform action.
      */
      var $__Type = 'posttransform';


      function StatAction(){
      }


      /**
      *  @public
      *
      *  Implements the abstract run method. Logs the page access.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 14.10.2008<br />
      */
      function run(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // get page language and name
         $PageLang = $Model->getAttribute('page.language');
         $PageName = $Model->getAttribute('page.title');
         if($PageName === null){
            if($PageLang == 'de'){
               $PageName = 'Startseite';
             // end if
            }
            else{
               $PageName = 'Home';
             // end else
            }
          // end if
         }

         // write statistic entry
         $SM = &$this->__getServiceObject('sites::apfdocupage::biz','StatManager');
         $SM->writeStatistic($PageName,$PageLang);

       // end function
      }

    // end class
   }
?>