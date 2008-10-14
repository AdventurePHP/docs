<?php
   import('sites::apfdocupage::biz','APFModel');
   import('core::session','sessionManager');
   import('3rdparty::webstat::biz','');


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

         // get current URL
         $RequestURI = $_SERVER['REQUEST_URI'];

         // create session and gather session id
         $Session = new sessionManager('WebStat');
         $SessionID = $Session->getSessionID();

         // gather referer
         $Referrer = $wSM->getReferrer();

   $wSM->writeStatistic($StatParam['Seite'],
                        $StatParam['Benutzer'],
                        $StatParam['RequestURI'],
                        $StatParam['SessionID'],
                        $StatParam['Referrer']
                       );
         // insert into statistic database
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection('Stat');

         $insert = '';

       // end function
      }

    // end class
   }
?>