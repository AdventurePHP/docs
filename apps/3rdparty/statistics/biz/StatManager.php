<?php
import('core::session', 'SessionManager');

/**
 * @package 3rdparty::statistics::biz
 * @class StatManager
 *
 * Implements the statistic business layer.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 21.12.2005<br />
 * Version 0.2, 12.11.2008 (Combined different classes to one business class)<br />
 */
class StatManager extends APFObject {

   /**
    * @public
    *
    * Returns the statistic data for the given period.
    *
    * @param string $period desired period
    * @param string $year desired year or null
    * @param string $month desired month or null
    * @param string $day desired day or null
    * @param string $hour desired hour or null
    * @return array $statSections list of the desired stat sections
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 12.11.2008<br />
    */
   public function readStatistic($period = 'overview', $year = null, $month = null, $day = null, $hour = null) {

      $T = &Singleton::getInstance('BenchmarkTimer');
      /* @var $T BenchmarkTimer */
      $T->start('getStatData(' . $period . ')');

      // Statistik-Daten laden
      $sM = &$this->getAndInitServiceObject('3rdparty::statistics::data', 'ReportingStatMapper', 'Stat');
      /* @var $sM ReportingStatMapper */
      switch ($period) {
         case 'year':
            $list = $sM->getStatData4Year($year);
            break;
         case 'month':
            $list = $sM->getStatData4Month($year, $month);
            break;
         case 'day':
            $list = $sM->getStatData4Day($year, $month, $day);
            break;
         case 'hour':
            $list = $sM->getStatData4Hour($year, $month, $day, $hour);
            break;
         default:
            $list = $sM->getStatData4Overview();
            break;
      }

      // calculate average and sum
      $list = $this->calculateAverageAndSum($list);

      $T->stop('getStatData(' . $period . ')');

      return $list;
   }

   /**
    * @public
    *
    * Business layer method to create a stat entry.
    *
    * @param string $PageName name of the requested page
    * @param string $PageLang current language
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2005<br />
    * Version 0.2, 14.10.2008 (Adapted to the apfdocupage and the new technologies)<br />
    */
   public function writeStatistic($PageName, $PageLang) {

      // gather IP/DNS
      $DNSIP = $this->getHostInfo();
      $IPAddress = $DNSIP['IP'];
      $DNSName = $DNSIP['DNS'];

      // create data layer component
      $wSM = &$this->getAndInitServiceObject('3rdparty::statistics::data', 'StatMapper', 'Stat');
      /* @var $wSM StatMapper */

      // create session manager
      $Session = new SessionManager('Stat');

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
   }

   /**
    * @public
    *
    * Gathers the user name of the current HTTP session.
    *
    * @return string The http user name or '*'.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2005<br />
    */
   public function getUserName() {

      if (empty($_SERVER['REMOTE_USER']) || $_SERVER['REMOTE_USER'] == '' || $_SERVER['REMOTE_USER'] == ' ') {
         $userName = '*';
      } else {
         $userName = $_SERVER['REMOTE_USER'];
      }

      return $userName;

   }

   /**
    * @public
    *
    * Gathers the HTTP referer.
    *
    * @return string $Referer the referer or '*'
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 22.12.2005<br />
    */
   public function getReferrer() {
      return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '*';
   }

   /**
    * @public
    *
    *  Gathers the client language.
    *
    * @return string $Lang client language or '*'
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 22.12.2005<br />
    */
   public function getLanguage() {
      return (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            ? $_SERVER['HTTP_ACCEPT_LANGUAGE']
            : '*';
   }

   /**
    * @public
    *
    * Gathers client ip and dns name.
    *
    * @return array Associative array with the client ip and dns name.
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 22.03.2005<br />
    */
   public function getHostInfo() {

      $DNSIP = trim($_SERVER['REMOTE_ADDR']);

      if (preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" .
                     "(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $DNSIP)
      ) {
         $return['DNS'] = gethostbyaddr($DNSIP);
         $return['IP'] = $DNSIP;
      } else {
         $return['DNS'] = $DNSIP;
         $return['IP'] = gethostbyname($DNSIP);
      }

      return $return;
   }

   /**
    * @public
    *
    * Gathers the OS of the client machine.
    *
    * @return string $OS the client machine os or '*'
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2005<br />
    * Version 0.2, 14.10.2008 (Corrected some table entries)<br />
    */
   public function getOS() {

      $osTable = array(
         'Windows 98' => 'Windows 98',
         'Win98' => 'Windows 98',
         'Windows NT' => 'Windows NT',
         'Windows NT 4.0' => 'Windows NT SP6a',
         'Windows NT 5.0' => 'Windows 2000',
         'Windows NT 5.1' => 'Windows XP',
         'Windows NT 5.2' => 'Windows XP SP2',
         'Windows NT 5.3' => 'Windows XP SP3',
         'Windows NT 6.0' => 'Windows Vista',
         'Windows NT 6.1' => 'Windows 7',
         'Mac OS X' => 'Mac OS X',
         'Mac' => 'Macintosh',
         'Ubuntu' => 'Ubuntu Linux',
         'SUSE' => 'SUSE Linux',
         'Debian' => 'Debian Linux',
         'Fedora' => 'Fedora Linux',
         'Mandriva' => 'Mandriva Linux',
         'Linux' => 'Linux'
      );

      foreach ($osTable as $key => $value) {
         if (substr_count($_SERVER['HTTP_USER_AGENT'], $key) > 0) {
            $os = $value;
            break;
         }
      }

      if (empty($os) || $os == ' ') {
         $os = '*';
      }

      return $os;

   }

   /**
    * @public
    *
    *  Gathers the client browser type.
    *
    * @return string $Browser the corresponding browser string or the content of the HTTP_USER_AGENT directive
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 22.12.2005<br />
    *  Version 0.2, 14.10.2008 (Corrected some table entries)<br />
    */
   public function getBrowser() {

      $browserTable = array(
         'AppleWebKit' => '[BROWSER] Safari',
         'Konqueror' => '[BROWSER] Konqueror',
         'Konqueror/3.4' => '[BROWSER] Konqueror 3.4',
         'Konqueror/3.3' => '[BROWSER] Konqueror 3.3',
         'MSIE 9' => '[BROWSER] Internet Explorer 9',
         'MSIE 8' => '[BROWSER] Internet Explorer 8',
         'MSIE 7' => '[BROWSER] Internet Explorer 7',
         'MSIE 5' => '[BROWSER] Internet Explorer 5',
         'MSIE 5.5' => '[BROWSER] Internet Explorer 5.5',
         'MSIE 6' => '[BROWSER] Internet Explorer 6',
         'MSIE 3' => '[BROWSER] Internet Explorer 3',
         'MSIE 4' => '[BROWSER] Internet Explorer 4',
         'MSIE' => '[BROWSER] Internet Explorer',
         'Opera/11' => '[BROWSER] Opera 11',
         'Opera/10' => '[BROWSER] Opera 10',
         'Opera/9' => '[BROWSER] Opera 9',
         'Opera/8' => '[BROWSER] Opera 8',
         'Opera' => '[BROWSER] Opera',
         'Firefox/8' => '[BROWSER] Firefox 8',
         'Firefox/7' => '[BROWSER] Firefox 7',
         'Firefox/6' => '[BROWSER] Firefox 6',
         'Firefox/5' => '[BROWSER] Firefox 5',
         'Firefox/4' => '[BROWSER] Firefox 4',
         'Firefox/3' => '[BROWSER] Firefox 3',
         'Firefox/2' => '[BROWSER] Firefox 2',
         'Firefox' => '[BROWSER] Firefox',
         'iCab' => '[BROWSER] iCab',
         'w3m/' => '[BROWSER] w3m',
         'Safari' => '[BROWSER] Safari'
      );

      //
      //   This select can be used to analyze spider and bot access:
      //   SELECT UserAgent, COUNT(UserAgent) AS count FROM statistics WHERE NOT INSTR(UserAgent,'Mozilla') GROUP BY UserAgent
      //

      $botTable = array(
         'Yahoo! Slurp' => '[BOT] Yahoo! spider',
         'appie 1.1 (www.walhello.com)' => '[BOT] walhello.com',
         'contype' => '[BOT] ConType spider',
         'findlinks/0.87' => '[BOT] Wordspider Uni Leipzig',
         'getRAX' => '[BOT] getRAX Spider',
         'Gigabot/2.0' => '[BOT] Gibagot',
         'Googlebot/2.1' => '[BOT] Google.de',
         'HeinrichderMiragoRobot' => '[BOT] MiragoRobot',
         'ia_archiver' => '[BOT] Alexa websearch',
         'Jetbot/1.0' => '[BOT] JetBot',
         'larbin-mb' => '[BOT] Larbin webspider',
         'libwww-perl' => '[BOT] libperl www-client',
         'LinkWalker' => '[BOT] LinkWalker',
         'lwp-trivial/1.36' => '[BOT] LWP-Trivial web crawler',
         'MJ12bot/v0.9.0' => '[BOT] majestic12.co.uk',
         'msnbot/0.3' => '[BOT] MSN bot 0.3',
         'msnbot/1.0' => '[BOT] MSN bot 1.0',
         'psbot/0.1' => '[BOT] PicSearch crawler',
         'Seekbot/1.0' => '[BOT] seekbot.net',
         'suchbaer.de' => '[BOT] suchbaer.de',
         'TurnitinBot/2.0' => '[BOT] turnitin.com',
         'Wildsoft Surfer' => '[BOT] Wildsoft surfer / dlman crawler',
         'WMP/1.0' => '[BOT] webmasterplan.de CrawlTool',
         'Zao-Crawler' => '[BOT] kototoi.org/zao',
         'ia_archiver' => '[BOT] Alexa crawler',
         'Baiduspider' => '[BOT] baidu.com spider',
         'Twiceler-0.9' => '[BOT] Twicler Spider',
         'msnbot-media/1.0' => '[BOT] MSN media bot',
         'msnbot-media/1.1' => '[BOT] MSN media bot',
         'msnbot/1.1' => '[BOT] MSN bot 1.1',
         'Yandex' => '[BOT] Yandex spider',
         'vBSEO' => '[BOT] vBSEO link checker',
         'freshmeat.net URI validator' => '[BOT] freshmeat link checker'
      );

      $browser = '';

      foreach ($browserTable as $Key => $Value) {
         if (substr_count($_SERVER['HTTP_USER_AGENT'], $Key) > 0) {
            $browser = $Value;
            break;
         }
      }
      foreach ($botTable as $Key => $Value) {
         if (substr_count($_SERVER['HTTP_USER_AGENT'], $Key) > 0) {
            $browser = $Value;
            break;
         }
      }

      if (empty($browser) || $browser == ' ') {
         $browser = $_SERVER['HTTP_USER_AGENT'];
      }

      return $browser;
   }

   /**
    * @private
    *
    * Calculates the average and sums of certain stat items.
    *
    * @param BaseStatSection[] $statList List of stat sections.
    * @return BaseStatSection[] Manipulated list of stat sections.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 05.06.2006<br />
    * Version 0.2, 16.11.2008 (Adapted to the new domain objects)<br />
    */
   private function calculateAverageAndSum($statList) {

      for ($i = 0; $i < count($statList); $i++) {

         if (get_class($statList[$i]) == 'LinkTableStatSection') {

            $entries = $statList[$i]->getAttribute('Entries');

            $sum = (int)0;
            $count = (int)0;

            for ($j = 0; $j < count($entries); $j++) {

               $sum = $sum + intval($entries[$j]->getAttribute('Value'));
               $count++;

            }

            $statList[$i]->setAttribute('Sum', $sum);
            if ($count > 0) {
               $statList[$i]->setAttribute('Average', intval($sum / $count));
            } else {
               $statList[$i]->setAttribute('Average', 0);
            }

         }

      }

      return $statList;

   }

}

?>