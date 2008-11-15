<?php
   import('3rdparty::statistics::data','StatMapper');
   import('core::session','sessionManager');


   /**
   *  @package 3rdparty::statistics::biz
   *  @class StatManager
   *
   *  Implements the statistic business layer.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 21.12.2005<br />
   *  Version 0.2, 12.11.2008 (Combined different classes to one business class)<br />
   */
   class StatManager extends coreObject
   {

      function StatManager(){
      }


      /**
      *  @public
      *
      *  Returns the statistic data for the given period.
      *
      *  @param string $period desired period
      *  @return array $statData list of stat entries for the desired period
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 12.11.2008<br />
      */
      function readStatistic($period = 'overview'){

         // invoke benchmarker
         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('getStatData('.$period.')');

         // Statistik-Daten laden
         $sM = &$this->__getServiceObject('3rdparty::statistics::data','StatMapper');

         switch($period){

            case 'year':
               $list = $sM->getStatData4Year();
               break;
            case 'month':
               $list = $sM->getStatData4Month();
               break;
            case 'day':
               $list = $sM->getStatData4Day();
               break;
            case 'hour':
               $list = $sM->getStatData4Hour();
               break;
            default:
               $list = $sM->getStatData4Overview();
               break;

          // end switch
         }

         // calculate average and sum
         $list = $this->__calculateAverageAndSum($list);

         // stop benchmarker
         $T->stop('getStatData('.$period.')');

         // return items
         return $list;

       // end function
      }


      /**
      *  @public
      *
      *  Business layer method to create a stat entry.
      *
      *  @param string $PageName name of the requested page
      *  @param string $PageLang current language
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2005<br />
      *  Version 0.2, 14.10.2008 (Adapted to the apfdocupage and the new technologies)<br />
      */
      function writeStatistic($PageName,$PageLang){

         // gather IP/DNS
         $DNSIP = $this->getHostInfo();
         $IPAddress = $DNSIP['IP'];
         $DNSName = $DNSIP['DNS'];

         // create data layer component
         $wSM = &$this->__getAndInitServiceObject('sites::apfdocupage::data','StatMapper','Stat');

         // create session manager
         $Session = new sessionManager('Stat');

         // create stat entry
         $wSM->createStatEntry(
                               $PageName,
                               $PageLang,
                               $_SERVER['REQUEST_URI'],
                               date('d'),
                               date('m'),
                               date('Y'),
                               date('H'),
                               date('i'),
                               date('s'),
                               $this->getUserName(),
                               $Session->getSessionID(),
                               $this->getBrowser(),
                               $this->getLanguage(),
                               $this->getOS(),
                               $IPAddress,
                               $DNSName,
                               $this->getReferrer(),
                               $_SERVER['HTTP_USER_AGENT']
                              );

       // end function
      }


      /**
      *  @public
      *
      *  Gathers the user name of the current HTTP session.
      *
      *  @return string $UserName the http user name or '*'
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2005<br />
      */
      function getUserName(){

          if(empty($_SERVER['REMOTE_USER']) || $_SERVER['REMOTE_USER'] == '' || $_SERVER['REMOTE_USER'] == ' '){
            $UserName = '*';
          // end if
         }
         else{
            $UserName = $_SERVER['REMOTE_USER'];
          // end else
         }

         return $UserName;

       // end function
      }


      /**
      *  @public
      *
      *  Gathers the HTTP referer.
      *
      *  @return string $Referer the referer or '*'
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2005<br />
      */
      function getReferrer(){

         if(isset($_SERVER['HTTP_REFERER'])){
            $Referer = $_SERVER['HTTP_REFERER'];
          // end if
         }
         else{
            $Referer = (string)'*';
          // end else
         }

         return $Referer;

       // end function
      }


      /**
      *  @public
      *
      *  Gathers the client language.
      *
      *  @return string $Lang client language or '*'
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2005<br />
      */
      function getLanguage(){

         if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $Lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
          // end if
         }
         else{
            $Lang = '*';
          // end else
         }

         return $Lang;

       // end function
      }


      /**
      *  @public
      *
      *  Gathers client ip and dns name.
      *
      *  return array $Return associative array with the client ip and dns name
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.03.2005<br />
      */
      function getHostInfo(){

         $DNSIP = trim($_SERVER['REMOTE_ADDR']);

         if(ereg("[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}",$DNSIP)){
            $Return['DNS'] = gethostbyaddr($DNSIP);
            $Return['IP'] = $DNSIP;
          // end if
         }
         else{
            $Return['DNS'] = $DNSIP;
            $Return['IP'] = gethostbyname($DNSIP);
          // end if
         }

         return $Return;

       // end function
      }


      /**
      *  @public
      *
      *  Gathers the OS of the client machine.
      *
      *  @return string $OS the client machine os or '*'
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2005<br />
      *  Version 0.2, 14.10.2008 (Corrected some table entries)<br />
      */
      function getOS(){

         $OSTable = array(
                          'Windows 98' => 'Windows 98',
                          'Win98' => 'Windows 98',
                          'Windows NT 3' => 'Windows NT',
                          'Windows NT 4.0' => 'Windows NT SP6a',
                          'Windows NT 5.0' => 'Windows 2000',
                          'Windows NT 5.1' => 'Windows XP',
                          'Windows NT 5.2' => 'Windows XP SP2',
                          'Windows NT 5.3' => 'Windows XP SP3',
                          'Mac' => 'Macintosh',
                          'Linux' => 'Linux',
                          'Googlebot' => 'Google-Crawler-Server'
                         );

         foreach($OSTable as $Key => $Value){
            if(substr_count($_SERVER['HTTP_USER_AGENT'],$Key) > 0){
               $OS = $Value;
             // end if
            }
          // end foreach
         }

         if(empty($OS) || $OS == ' '){
            $OS = '*';
          // end if
         }

         return $OS;

       // end function
      }


      /**
      *  @public
      *
      *  Gathers the client browser type.
      *
      *  @return string $Browser the corresponding browser string or the content of the HTTP_USER_AGENT directive
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2005<br />
      *  Version 0.2, 14.10.2008 (Corrected some table entries)<br />
      */
      function getBrowser(){

         $BrowserTable = array(
                               'Konqueror' => '[BROWSER] Konqueror',
                               'Konqueror/3.4' => '[BROWSER] Konqueror 3.4',
                               'Konqueror/3.3' => '[BROWSER] Konqueror 3.3',
                               'MSIE 5' => '[BROWSER] Internet Explorer 5',
                               'MSIE 5.5' => '[BROWSER] Internet Explorer 5.5',
                               'MSIE 6' => '[BROWSER] Internet Explorer 6',
                               'MSIE 3' => '[BROWSER] Internet Explorer 3',
                               'MSIE 4' => '[BROWSER] Internet Explorer 4',
                               'Opera' => '[BROWSER] Opera',
                               'Firefox' => '[BROWSER] Firefox',
                               'iCab' => '[BROWSER] iCab'
                              );

         //
         //   This select can be used to analyze spider and bot access:
         //   SELECT UserAgent, COUNT(UserAgent) AS Anzahl FROM statistiken_live WHERE NOT INSTR(UserAgent,'Mozilla') GROUP BY UserAgent
         //

         $BotTable = array(
                           'Yahoo! Slurp' => '[BOT] Yahoo!-Spider Browseransicht',
                           'appie 1.1 (www.walhello.com)' => '[BOT] walhello.com',
                           'contype' => '[BOT] ConType Spider',
                           'curl/7.9.2' => '[BOT] Linux Spider',
                           'findlinks/0.87' => '[BOT] Wortschatzspider Uni Leipzig',
                           'getRAX' => '[BOT] getRAX Spider',
                           'Gigabot/2.0' => '[BOT] Gibagot',
                           'Googlebot/2.1' => '[BOT] Google.de',
                           'HeinrichderMiragoRobot' => '[BOT] MiragoRobot',
                           'ia_archiver' => '[BOT] Alexa Websearch',
                           'iCab' => '[BOT] Macintosh',
                           'Jetbot/1.0' => '[BOT] JetBot',
                           'larbin-mb' => '[BOT] Larbin Webspider',
                           'libwww-perl' => '[BOT] libperl www-client',
                           'LinkWalker' => '[BOT] LinkWalker',
                           'lwp-trivial/1.36' => '[BOT] LWP-Trivial Web Crawler',
                           'MJ12bot/v0.9.0' => '[BOT] majestic12.co.uk',
                           'msnbot/0.3' => '[BOT] MSNBot',
                           'msnbot/1.0' => '[BOT] MSNBot',
                           'psbot/0.1' => '[BOT] PicSearch Crawler',
                           'Seekbot/1.0' => '[BOT] seekbot.net',
                           'suchbaer.de' => '[BOT] suchbaer.de',
                           'SVN/1.1.0' => '[BOT] SVN/1.1.0',
                           'TurnitinBot/2.0' => '[BOT] turnitin.com',
                           'Wildsoft Surfer' => '[BOT] Wildsoft Surfer / dlman Crawler',
                           'WMP/1.0' => '[BOT] webmasterplan.de CrawlTool',
                           'Zao-Crawler' => '[BOT] kototoi.org/zao'
                          );

         $Browser = (string)'';

         foreach($BrowserTable as $Key => $Value){
            if(substr_count($_SERVER['HTTP_USER_AGENT'],$Key) > 0){
               $Browser = $Value;
             // end if
            }
          // end foreach
         }
         foreach($BotTable as $Key => $Value){
            if(substr_count($_SERVER['HTTP_USER_AGENT'],$Key) > 0){
               $Browser = $Value;
             // end if
            }
          // end foreach
         }

         if(empty($Browser) || $Browser == ' '){
            $Browser = $_SERVER['HTTP_USER_AGENT'];
          // end if
         }

         return $Browser;

       // end function
      }


      /**
      *  @private
      *
      *  Calculates the average and sums of certain stat items.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 05.06.2006<br />
      */
      function __calculateAverageAndSum($statList){

         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('__calculateAverageAndSum');

         for($i = 0; $i < count($statList); $i++){

            if(get_class($statList[$i]) == strtolower('linkTabellenStatSektion')){

               $Eintraege = $statList[$i]->zeigeEintraege();

               $Summe = (int)0;
               $Anzahl = (int)0;

               for($j = 0; $j < count($Eintraege); $j++){

                  $Summe = $Summe + intval($Eintraege[$j]->zeigeWert());
                  $Anzahl++;

                // end for
               }

               $statList[$i]->setzeSumme($Summe);
               $statList[$i]->setzeDurchschnitt(intval($Summe / $Anzahl));

             // end if
            }

          // end for
         }

         $T->stop('__calculateAverageAndSum');

         return $statList;

       // end function
      }

    // end class
   }
?>