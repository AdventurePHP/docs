<?php
   import('core::database','connectionManager');


   /**
   *  @package sites::apfdocupage::data
   *  @class StatMapper
   *
   *  Implements the data layer for the web statistics.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 21.12.2005<br />
   *  Version 0.2, 22.12.2005<br />
   *  Version 0.3, 08.03.2006<br />
   *  Version 0.4, 14.10.2008 (Adapted to the apfdocupage and the new technologies)<br />
   */
   class StatMapper extends coreObject
   {


      /**
      *  @private
      *  Connection key.
      */
      var $__ConnectionKey = 'Stat';


      function StatMapper(){
      }


      /**
      *  @public
      *
      *  Initializes the mapper.
      *
      *  @param string $ConnectionKey the desired database connection key
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 14.10.2008<br />
      */
      function init($ConnectionKey){
         $this->__ConnectionKey = $ConnectionKey;
       // end function
      }


      /**
      *  @public
      *
      *  Creates a statistic entry.
      *
      *  @param string $PageName name of the requested page
      *  @param string $PageLang language of the page
      *  @param string $RequestURI complete request url
      *  @param string $Day number of the day
      *  @param string $Month number of the month
      *  @param string $Year number of the year
      *  @param string $Hour number of the hour
      *  @param string $Minute number of the minute
      *  @param string $Second number of the second
      *  @param string $UserName user name if applicable
      *  @param string $SessionID session id
      *  @param string $Browser broswer
      *  @param string $ClientLanguage language of the client
      *  @param string $OS operating system
      *  @param string $IPAddress client ip address
      *  @param string $DNSAddress client dns name
      *  @param string $Referer referer
      *  @param string $UserAgent user agent
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 10.04.2004<br />
      *  Version 0.2, 23.10.2004<br />
      *  Version 0.3, 19.01.2005<br />
      *  Version 0.4, 05.04.2005<br />
      *  Version 0.5, 22.12.2005<br />
      *  Version 0.6, 23.02.2006 (Anlegen der Tabelle wird nicht mehr geprüft)<br />
      *  Version 0.7, 05.06.2006 (MySQLHandler wird nun singleton instanziert)<br />
      *  Version 0.8, 14.10.2008 (Adapted to the apfdocupage and the new technologies)<br />
      */
      function createStatEntry($PageName,$PageLang,$RequestURI,$Day,$Month,$Year,$Hour,$Minute,$Second,$UserName,$SessionID,$Browser,$ClientLanguage,$OS,$IPAddress,$DNSAddress,$Referer,$UserAgent){

         // get database connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection($this->__ConnectionKey);

         // create entry
         $insert = 'INSERT INTO statistics
                      (
                       PageName,
                       PageLanguage,
                       RequestURI,
                       Day,
                       Month,
                       Year,
                       Hour,
                       Minute,
                       Second,
                       UserName,
                       SessionID,
                       Browser,
                       ClientLanguage,
                       OS,
                       IPAddress,
                       DNSAddress,
                       Referer,
                       UserAgent
                      )
                      VALUES
                      (
                       \''.$PageName.'\',
                       \''.$PageLang.'\',
                       \''.$RequestURI.'\',
                       \''.$Day.'\',
                       \''.$Month.'\',
                       \''.$Year.'\',
                       \''.$Hour.'\',
                       \''.$Minute.'\',
                       \''.$Second.'\',
                       \''.$UserName.'\',
                       \''.$SessionID.'\',
                       \''.$Browser.'\',
                       \''.$ClientLanguage.'\',
                       \''.$OS.'\',
                       \''.$IPAddress.'\',
                       \''.$DNSAddress.'\',
                       \''.$Referer.'\',
                       \''.$UserAgent.'\'
                      );';
         $SQL->executeTextStatement($insert);

       // end function
      }

    // end class
   }
?>