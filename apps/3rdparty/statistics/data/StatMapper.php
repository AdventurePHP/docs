<?php
   import('core::database','connectionManager');
   import('3rdparty::statistics::data','ReportingStatMapper');


   /**
   *  @package 3rdparty::statistics::data
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
      *  Version 0.6, 23.02.2006 (Doesn't create table, if it does not exist)<br />
      *  Version 0.7, 05.06.2006 (Database layer is created singlton now)<br />
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


      /**
      *  @public
      *
      *  Implementiert die um Caching erweiterte __getStatData4Overview() Methode.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Overview(){

         $rSM = &$this->__getAndInitServiceObject('3rdparty::statistics::data','ReportingStatMapper',$this->__ConnectionKey);
         return $rSM->testReportingStatManager();
/*
         $oCM = &$this->__getStatCacheManager();

         if($oCM->cacheFileExists() == true){
            return $oCM->readFromCache();
          // end if
         }
         else{
            $List = $this->__getStatData4Overview();
            $oCM->writeToCache($List);
            return $List;
          // end else
         }
*/
       // end function
      }


      /**
      *  @public
      *
      *  Implementiert die um Caching erweiterte __getStatData4Year() Methode.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Year(){
         return array();
         $oCM = &$this->__getStatCacheManager();

         if($oCM->cacheFileExists() == true){
            return $oCM->readFromCache();
          // end if
         }
         else{
            $List = $this->__getStatData4Year();
            $oCM->writeToCache($List);
            return $List;
          // end else
         }

       // end function
      }


      /**
      *  @public
      *
      *  Implementiert die um Caching erweiterte __getStatData4Month() Methode.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Month(){
         return array();
         $oCM = &$this->__getStatCacheManager();

         if($oCM->cacheFileExists() == true){
            return $oCM->readFromCache();
          // end if
         }
         else{
            $List = $this->__getStatData4Month();
            $oCM->writeToCache($List);
            return $List;
          // end else
         }

       // end function
      }


      /**
      *  @public
      *
      *  Implementiert die um Caching erweiterte __getStatData4Day() Methode.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Day(){
         return array();
         $oCM = &$this->__getStatCacheManager();

         if($oCM->cacheFileExists() == true){
            return $oCM->readFromCache();
          // end if
         }
         else{
            $List = $this->__getStatData4Day();
            $oCM->writeToCache($List);
            return $List;
          // end else
         }

       // end function
      }


      /**
      *  @public
      *
      *  Implementiert die um Caching erweiterte __getStatData4Hour() Methode.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Hour(){

         return array();
         $oCM = &$this->__getStatCacheManager();

         if($oCM->cacheFileExists() == true){
            return $oCM->readFromCache();
          // end if
         }
         else{
            $List = $this->__getStatData4Hour();
            $oCM->writeToCache($List);
            return $List;
          // end else
         }

       // end function
      }

    // end class
   }
?>