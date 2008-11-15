<?php
   import('tools::datetime','dateTimeManager');
   import('core::database','connectionManager');
   import('3rdparty::statistics::biz','SimpleStatSection');
   import('3rdparty::statistics::biz','TableStatSection');
   import('3rdparty::statistics::biz','LinkTableStatSection');
   import('3rdparty::statistics::biz','StatEntry');


   /**
   *  @package 3rdparty::statistics::data
   *  @module ReportingStatMapper
   *
   *  Implementiert den Statistik-Tool-Mapper.<br />
   *
   *  @author Christian Schäfer
   *  @version
   *  Version 0.1, 04.06.2006<br />
   *  Version 0.2, 05.06.2006<br />
   */
   class ReportingStatMapper extends coreObject
   {

      /**
      *  @private
      *  Connection key.
      */
      var $__ConnectionKey = 'Stat';


      /**
      *  @private
      *  Container für lokal verwendete Variablen.
      */
      var $_LOCALS;


      /**
      *  @private
      *  Maximale Breite des Ausgabe-Bereichs.
      */
      var $__MaxBreite = 420;


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


      function ReportingStatMapper(){
         //$this->_LOCALS = variablenHandler::registerLocal(array('pagepart','Jahr','Monat','Tag','Stunde'));
       // end function
      }


      /**
      *  first try to implement a generic data selector
      */
      function __genericGetStatData($attribute,$where = null,$group = null,$order = null,$limit = null){

         // get database connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection($this->__ConnectionKey);

         // initialize return list
         $statSections = array();

         // create array with all available periode values (years, months, ...
         $select_period = 'SELECT '.$attribute.' FROM statistics ';

         if($where !== null){
            $select_period .= 'WHERE '.$where.' ';
          // end if
         }

         if($group !== null){
            $select_period .= 'GROUP BY '.$group.' ';
          // end if
         }

         if($order !== null){
            $select_period .= 'ORDER BY '.$order.' ';
          // end if
         }

         if($limit !== null){
            $select_period .= 'LIMIT '.$limit.' ';
          // end if
         }

         $select_period = $select_period.';';
         $result_period = $SQL->executeTextStatement($select_period);

         $available_period_values = array();
         while($data_period = $SQL->fetchData($result_period)){
            $available_period_values[] = $data_period[$attribute];
          // end while
         }


         // 1. select count of visited pages per available period values
         $sect = new LinkTableStatSection();
         $sect->setAttribute('Title','Anzahl besuchte Seiten');

         $select_pages = 'SELECT '.$attribute.', COUNT('.$attribute.') AS count FROM statistics ';

         if($group !== null){
            $select_pages .= 'GROUP BY '.$group.' ';
          // end if
         }

         if($order !== null){
            $select_pages .= 'ORDER BY '.$order.' ';
          // end if
         }

         $select_pages = $select_pages.';';
         $result_pages = $SQL->executeTextStatement($select_pages);

         $pagesPerPeriod = array();
         $offset = 0;
         $max = 0;
         $entries = array();
         while($data_pages = $SQL->fetchData($result_pages)){

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$data_pages[$attribute]);
            $entry->setAttribute('Value',$data_pages['count']);
            $entry->setAttribute('Link','./?pagepart=year&'.$attribute.'='.$data_pages[$attribute]);

            $pagesPerPeriod[$offset][$attribute] = $data_pages[$attribute];
            $pagesPerPeriod[$offset]['pages'] = $data_pages['count'];
            $offset++;

            $max = max($data_pages['count'],$max);
            $entries[] = $entry;

          // end while
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->__calculateDivisor($max));
         $statSections[] = $sect;


         // 2. select count of visitors per available period values
         $sect = new LinkTableStatSection();
         $sect->setAttribute('Title','Anzahl Besucher');

         // Maximalzahl der Besuche ermitteln
         $visitorsPerPeriod = array();
         $max = 0;
         $entries = array();
         for($i = 0; $i < count($available_period_values); $i++){

            $select_max = 'SELECT SessionID, '.$attribute.' FROM statistics
                           WHERE
                             '.$attribute.' = \''.$available_period_values[$i].'\'
                           GROUP BY SessionID
                           ORDER BY '.$attribute.' DESC;';
            $result_max =  $SQL->executeTextStatement($select_max);
            $visitor_count = $SQL->getNumRows($result_max);

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$available_period_values[$i]);
            $entry->setAttribute('Value',$visitor_count);
            $entry->setAttribute('Link','./?pagepart='.$attribute.'&'.$attribute.'='.$available_period_values[$i]);

            $pagesPerVisitor[$i][$attribute] = $available_period_values[$i];
            $pagesPerVisitor[$i]['count'] = round($pagesPerPeriod[$i]['pages'] / $visitor_count,0);

            $max = max($max,$pagesPerVisitor[$i]['count']);
            $entries[] = $entry;

          // end for
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->__calculateDivisor($max));
         $statSections[] = $sect;


         // 3. select / create pages per visitor (from the first two lists)
         $sect = new LinkTableStatSection();
         $sect->setAttribute('Title','Anzahl Seiten/Besucher');

         $max = 0;
         $entries = array();
         for($i = 0; $i < count($pagesPerVisitor); $i++){

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$pagesPerVisitor[$i][$attribute]);
            $entry->setAttribute('Value',$pagesPerVisitor[$i]['count']);
            $entry->setAttribute('Link','./?pagepart='.$attribute.'&'.$attribute.'='.$pagesPerVisitor[$i][$attribute]);

            $max = max($max,$pagesPerVisitor[$i]['count']);
            $entries[] = $entry;
          // end for
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->__calculateDivisor($max));
         $statSections[] = $sect;


         // 4. select TOP 10 requested sites / media files
         $sect = new SimpleStatSection();
         $sect->setAttribute('Title','Aufgerufene Seiten / Medien (TOP 10)');

         $select_pages = 'SELECT PageName, COUNT(PageName) AS count FROM statistics
                          GROUP BY PageName
                          ORDER BY count DESC, PageName ASC
                          LIMIT 10;';
         $result_pages = $SQL->executeTextStatement($select_pages);

         $max = 0;
         $entries = array();

         while($data_pages = $SQL->fetchData($result_pages)){

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$data_pages['PageName']);
            $entry->setAttribute('Value',$data_pages['count']);

            $max = max($max,$data_pages['count']);
            $entries[] = $entry;

          // end while
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->__calculateDivisor($max));
         $statSections[] = $sect;



         // 5. select TOP 10 bots

         // 6. select TOP 10 browsers

         // 7. select TOP 10 languages

         // 8. select TOP 10 OSes

         // 9. select TOP 10 referer

         // 10. select TOP 10 IP addresses

         // 11. select TOP 10 DNS addresses

         // 12. select TOP 10 users


         // return generated sections
         return $statSections;

       // end function
      }


      function testReportingStatManager(){
         return $this->__genericGetStatData(
                                     'Year',
                                     null,
                                     'Year',
                                     'Year DESC'
                                    );
/*
         $this->__genericGetStatData(
                                     'Month',
                                     'Year = \'2008\'',
                                     'Month',
                                     'Month DESC'
                                    );
*/
       // end function
      }



      /**
      *  Funktion __getStatData4Overview() [private/nonstatic]<br />
      *  Erzeugt den Statistik-Baum für die Übersicht.<br />
      *  <br />
      *  Christian Schäfer<br />
      *  Version 0.1, 05.06.2006<br />
      */
      function __getStatData4Overview(){

         $SQL = &$this->__getServiceObject('core::database','MySQLHandler');
         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('getStatData4Overview');

         $Sektionen = array();

         // Array mit vorkommenden Jahren zusammenstellen
         $select_jahre = "SELECT Jahr FROM statistiken
                          GROUP BY Jahr
                          ORDER BY Jahr DESC;";
         $result_jahre = $SQL->executeTextStatement($select_jahre);

         while($data_jahre = $SQL->fetchData($result_jahre)){
            $Jahre[] = $data_jahre['Jahr'];
          // end while
         }


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl besuchte Seiten');

         $select_seiten = "SELECT Jahr, COUNT(Jahr) AS Anzahl FROM statistiken
                           GROUP BY Jahr
                           ORDER BY Jahr DESC;";
         $result_seiten = $SQL->executeTextStatement($select_seiten);

         $SeitenProJahr = array();
         $Offset = 0;
         $MaxAnzahl = 0;

         while($data_seiten = $SQL->fetchData($result_seiten)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_seiten['Jahr']);
            $E->setzeWert($data_seiten['Anzahl']);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=jahr&Jahr='.$data_seiten['Jahr']);
            $S->setzeEintrag($E);

            // Seiten / Monat für Berechnung in Array schreiben
            $SeitenProJahr[$Offset]['Jahr'] = $data_seiten['Jahr'];
            $SeitenProJahr[$Offset]['Seiten'] = $data_seiten['Anzahl'];
            $Offset++;

            $MaxAnzahl = max($data_seiten['Anzahl'],$MaxAnzahl);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxAnzahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Besucher');

         // Maximalzahl der Besuche ermitteln
         $BesucheProJahr = array();
         $MaxZahl = 0;

         for($i = 0; $i < count($Jahre); $i++){

            $select_max = "SELECT SessionID, Jahr FROM statistiken
                           WHERE
                             Jahr = '".$Jahre[$i]."'
                           GROUP BY SessionID
                           ORDER BY Jahr DESC;";
            $result_max =  $SQL->executeTextStatement($select_max);
            $Besucher = $SQL->getNumRows($result_max);

            $E = new statEintrag();
            $E->setzeAnzeigeText($Jahre[$i]);
            $E->setzeWert($Besucher);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=jahr&Jahr='.$Jahre[$i]);
            $S->setzeEintrag($E);

            $SeitenProBesucher[$i]['Jahr'] = $Jahre[$i];
            $SeitenProBesucher[$i]['Anzahl'] = round($SeitenProJahr[$i]['Seiten'] / $Besucher,0);

            // Maximalwert ermitteln
            $MaxZahl = max($MaxZahl,$SeitenProBesucher[$i]['Anzahl']);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Seiten/Besucher');

         $MaxZahl = 0;

         for($i = 0; $i < count($SeitenProBesucher); $i++){

            $E = new statEintrag();
            $E->setzeAnzeigeText($SeitenProBesucher[$i]['Jahr']);
            $E->setzeWert($SeitenProBesucher[$i]['Anzahl']);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=jahr&Jahr='.$SeitenProBesucher[$i]['Jahr']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$SeitenProBesucher[$i]['Anzahl']);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Aufgerufene Seiten / Medien (TOP 10)');

         $select_medien = "SELECT Name, COUNT(Name) AS Anzahl FROM statistiken
                           GROUP BY Name
                           ORDER BY Anzahl DESC, Name ASC
                           LIMIT 10;";
         $result_medien = $SQL->executeTextStatement($select_medien);

         $MaxZahl = 0;

         while($data_medien = $SQL->fetchData($result_medien)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_medien['Name']);
            $E->setzeWert($data_medien['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_medien['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Suchmaschinen / Spider / Crawler (TOP 10)');

         // Anzahl der Anfragen ermitteln
         $select_spider = "SELECT REPLACE(Browser,'[BOT] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                           WHERE INSTR(Browser,'[BOT]')
                           GROUP BY Browser
                           ORDER BY Anzahl DESC, Name ASC
                           LIMIT 10;";
         $result_spider = $SQL->executeTextStatement($select_spider);

         $MaxZahl = 0;

         while($data_spider = $SQL->fetchData($result_spider)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_spider['Browser']);
            $E->setzeWert($data_spider['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_spider['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Browser (TOP 10)');

         // Browser ermitteln
         $select_browser = "SELECT REPLACE(Browser,'[BROWSER] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                            WHERE INSTR(Browser,'[BROWSER]')
                            GROUP BY Browser
                            ORDER BY Anzahl DESC, Browser ASC
                            LIMIT 10;";
         $result_browser = $SQL->executeTextStatement($select_browser);

         $MaxZahl = 0;

         while($data_browser = $SQL->fetchData($result_browser)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_browser['Browser']);
            $E->setzeWert($data_browser['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_browser['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Sprache (TOP 10)');

         $select_sprachen = "SELECT Sprache, COUNT(Sprache) AS Anzahl FROM statistiken
                             GROUP BY Sprache
                             ORDER BY Anzahl DESC, Sprache ASC
                             LIMIT 10;";
         $result_sprachen = $SQL->executeTextStatement($select_sprachen);

         $MaxZahl = 0;

         while($data_sprachen = $SQL->fetchData($result_sprachen)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_sprachen['Sprache']);
            $E->setzeWert($data_sprachen['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_sprachen['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Betriebssysteme (TOP 10)');

         $select_betriebssystem = "SELECT Betriebssystem, COUNT(Betriebssystem) AS Anzahl FROM statistiken
                                   GROUP BY Betriebssystem
                                   ORDER BY Anzahl DESC, Betriebssystem ASC
                                   LIMIT 10;";
         $result_betriebssystem = $SQL->executeTextStatement($select_betriebssystem);

         $MaxZahl = 0;

         while($data_betriebssystem = $SQL->fetchData($result_betriebssystem)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_betriebssystem['Betriebssystem']);
            $E->setzeWert($data_betriebssystem['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_betriebssystem['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Herkunft (TOP 10)');

         $select_herkunft = "SELECT Herkunft, COUNT(Herkunft) AS Anzahl FROM statistiken
                             GROUP BY Herkunft
                             ORDER BY Anzahl DESC, Herkunft ASC
                             LIMIT 10;";
         $result_herkunft = $SQL->executeTextStatement($select_herkunft);

         $MaxZahl = 0;

         while($data_herkunft = $SQL->fetchData($result_herkunft)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_herkunft['Herkunft']);
            $E->setzeWert($data_herkunft['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_herkunft['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('IP-Adressen (bzw. Rechner) (TOP 10)');

         $select_ip = "SELECT IPAdresse, COUNT(IPAdresse) AS Anzahl FROM statistiken
                       GROUP BY IPAdresse
                       ORDER BY Anzahl DESC, IPAdresse ASC
                       LIMIT 10;";
         $result_ip = $SQL->executeTextStatement($select_ip);

         $MaxZahl = 0;

         while($data_ip = $SQL->fetchData($result_ip)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_ip['IPAdresse']);
            $E->setzeWert($data_ip['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_ip['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('DNS-Namen (bzw. Rechner) (TOP 10)');

         $select_dns = "SELECT DNSAdresse, COUNT(DNSAdresse) AS Anzahl FROM statistiken
                        GROUP BY DNSAdresse
                        ORDER BY Anzahl DESC, DNSAdresse ASC
                        LIMIT 10;";
         $result_dns = $SQL->executeTextStatement($select_dns);

         $MaxZahl = 0;

         while($data_dns = $SQL->fetchData($result_dns)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_dns['DNSAdresse']);
            $E->setzeWert($data_dns['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_dns['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Benutzer (TOP 10)');

         $select_benutzer = "SELECT BenutzerName, COUNT(BenutzerName) AS Anzahl FROM statistiken
                             GROUP BY BenutzerName
                             ORDER BY Anzahl DESC, BenutzerName ASC
                             LIMIT 10;";
         $result_benutzer = $SQL->executeTextStatement($select_benutzer);

         $MaxZahl = 0;

         while($data_benutzer = $SQL->fetchData($result_benutzer)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_benutzer['BenutzerName']);
            $E->setzeWert($data_benutzer['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_benutzer['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;

         $T->stop('getStatData4Overview');

         // Sektions-Baum zurückgeben
         return $Sektionen;

       // end function
      }


      /**
      *  Funktion __getStatData4Year() [private/nonstatic]<br />
      *  Erzeugt den Statistik-Baum für die Jahres-Ansicht.<br />
      *  <br />
      *  Christian Schäfer<br />
      *  Version 0.1, 04.06.2006<br />
      */
      function getStatData4Year(){

         $SQL = &$this->__getServiceObject('core::database','MySQLHandler');
         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('getStatData4Year');

         $Sektionen = array();

         // Array mit vorkommenden Jahren zusammenstellen
         $select_monate = "SELECT Monat FROM statistiken
                           WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                           GROUP BY Monat
                           ORDER BY Monat DESC;";
         $result_monate = $SQL->executeTextStatement($select_monate);

         while($data_monate = $SQL->fetchData($result_monate)){
            $Monate[] = $data_monate['Monat'];
          // end while
         }


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl besuchte Seiten');

         $select_seiten = "SELECT Monat, COUNT(Monat) AS Anzahl FROM statistiken
                           WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                           GROUP BY Monat
                           ORDER BY Monat DESC;";
         $result_seiten =  $SQL->executeTextStatement($select_seiten);

         $SeitenProMonat = array();
         $Offset = 0;
         $MaxAnzahl = 0;

         while($data_seiten = $SQL->fetchData($result_seiten)){

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::showMonthLabel($data_seiten['Monat']));
            $E->setzeWert($data_seiten['Anzahl']);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=monat&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$data_seiten['Monat']);
            $S->setzeEintrag($E);

            // Seiten / Monat für Berechnung in Array schreiben
            $SeitenProMonat[$Offset]['Monat'] = $data_seiten['Monat'];
            $SeitenProMonat[$Offset]['Seiten'] = $data_seiten['Anzahl'];
            $Offset++;

            $MaxAnzahl = max($data_seiten['Anzahl'],$MaxAnzahl);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxAnzahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Besucher');

         // Maximalzahl der Besuche ermitteln
         $BesucheProMonat = array();
         $MaxZahl = 0;

         for($i = 0; $i < count($Monate); $i++){

            $select_max = "SELECT SessionID, Monat FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$Monate[$i]."'
                           GROUP BY SessionID
                           ORDER BY Monat DESC;";
            $result_max =  $SQL->executeTextStatement($select_max);
            $Besucher = $SQL->getNumRows($result_max);

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::showMonthLabel($Monate[$i]));
            $E->setzeWert($Besucher);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=monat&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$Monate[$i]);
            $S->setzeEintrag($E);

            $SeitenProBesucher[$i]['Monat'] = $Monate[$i];
            $SeitenProBesucher[$i]['Anzahl'] = round($SeitenProMonat[$i]['Seiten'] / $Besucher,0);

            // Maximalwert ermitteln
            $MaxZahl = max($MaxZahl,$SeitenProBesucher[$i]['Anzahl']);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Seiten/Besucher');

         $MaxZahl = 0;

         for($i = 0; $i < count($SeitenProBesucher); $i++){

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::showMonthLabel($SeitenProBesucher[$i]['Monat']));
            $E->setzeWert($SeitenProBesucher[$i]['Anzahl']);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=monat&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$SeitenProBesucher[$i]['Monat']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$SeitenProBesucher[$i]['Anzahl']);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Aufgerufene Seiten / Medien (TOP 10)');

         $select_medien = "SELECT Name, COUNT(Name) AS Anzahl FROM statistiken
                           WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                           GROUP BY Name
                           ORDER BY Anzahl DESC, Name ASC
                           LIMIT 10;";
         $result_medien = $SQL->executeTextStatement($select_medien);

         $MaxZahl = 0;

         while($data_medien = $SQL->fetchData($result_medien)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_medien['Name']);
            $E->setzeWert($data_medien['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_medien['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Suchmaschinen / Spider / Crawler (TOP 10)');

         // Anzahl der Anfragen ermitteln
         $select_spider = "SELECT REPLACE(Browser,'[BOT] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             INSTR(Browser,'[BOT]')
                           GROUP BY Browser
                           ORDER BY Anzahl DESC, Name ASC
                           LIMIT 10;";
         $result_spider = $SQL->executeTextStatement($select_spider);

         $MaxZahl = 0;

         while($data_spider = $SQL->fetchData($result_spider)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_spider['Browser']);
            $E->setzeWert($data_spider['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_spider['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Browser (TOP 10)');

         // Browser ermitteln
         $select_browser = "SELECT REPLACE(Browser,'[BROWSER] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              INSTR(Browser,'[BROWSER]')
                            GROUP BY Browser
                            ORDER BY Anzahl DESC, Browser ASC
                            LIMIT 10;";
         $result_browser = $SQL->executeTextStatement($select_browser);

         $MaxZahl = 0;

         while($data_browser = $SQL->fetchData($result_browser)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_browser['Browser']);
            $E->setzeWert($data_browser['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_browser['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Sprache (TOP 10)');

         $select_sprachen = "SELECT Sprache, COUNT(Sprache) AS Anzahl FROM statistiken
                             WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                             GROUP BY Sprache
                             ORDER BY Anzahl DESC, Sprache ASC
                             LIMIT 10;";
         $result_sprachen = $SQL->executeTextStatement($select_sprachen);

         $MaxZahl = 0;

         while($data_sprachen = $SQL->fetchData($result_sprachen)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_sprachen['Sprache']);
            $E->setzeWert($data_sprachen['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_sprachen['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Betriebssysteme (TOP 10)');

         $select_betriebssystem = "SELECT Betriebssystem, COUNT(Betriebssystem) AS Anzahl FROM statistiken
                                   WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                                   GROUP BY Betriebssystem
                                   ORDER BY Anzahl DESC, Betriebssystem ASC
                                   LIMIT 10;";
         $result_betriebssystem = $SQL->executeTextStatement($select_betriebssystem);

         $MaxZahl = 0;

         while($data_betriebssystem = $SQL->fetchData($result_betriebssystem)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_betriebssystem['Betriebssystem']);
            $E->setzeWert($data_betriebssystem['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_betriebssystem['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Herkunft (TOP 10)');

         $select_herkunft = "SELECT Herkunft, COUNT(Herkunft) AS Anzahl FROM statistiken
                             WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                             GROUP BY Herkunft
                             ORDER BY Anzahl DESC, Herkunft ASC
                             LIMIT 10;";
         $result_herkunft = $SQL->executeTextStatement($select_herkunft);

         $MaxZahl = 0;

         while($data_herkunft = $SQL->fetchData($result_herkunft)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_herkunft['Herkunft']);
            $E->setzeWert($data_herkunft['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_herkunft['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('IP-Adressen (bzw. Rechner) (TOP 10)');

         $select_ip = "SELECT IPAdresse, COUNT(IPAdresse) AS Anzahl FROM statistiken
                       WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                       GROUP BY IPAdresse
                       ORDER BY Anzahl DESC, IPAdresse ASC
                       LIMIT 10;";
         $result_ip = $SQL->executeTextStatement($select_ip);

         $MaxZahl = 0;

         while($data_ip = $SQL->fetchData($result_ip)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_ip['IPAdresse']);
            $E->setzeWert($data_ip['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_ip['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('DNS-Namen (bzw. Rechner) (TOP 10)');

         $select_dns = "SELECT DNSAdresse, COUNT(DNSAdresse) AS Anzahl FROM statistiken
                        WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                        GROUP BY DNSAdresse
                        ORDER BY Anzahl DESC, DNSAdresse ASC
                        LIMIT 10;";
         $result_dns = $SQL->executeTextStatement($select_dns);

         $MaxZahl = 0;

         while($data_dns = $SQL->fetchData($result_dns)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_dns['DNSAdresse']);
            $E->setzeWert($data_dns['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_dns['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Benutzer (TOP 10)');

         $select_benutzer = "SELECT BenutzerName, COUNT(BenutzerName) AS Anzahl FROM statistiken
                             WHERE Jahr = '".$this->_LOCALS['Jahr']."'
                             GROUP BY BenutzerName
                             ORDER BY Anzahl DESC, BenutzerName ASC
                             LIMIT 10;";
         $result_benutzer = $SQL->executeTextStatement($select_benutzer);

         $MaxZahl = 0;

         while($data_benutzer = $SQL->fetchData($result_benutzer)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_benutzer['BenutzerName']);
            $E->setzeWert($data_benutzer['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_benutzer['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;

         $T->stop('getStatData4Year');

         // Sektions-Baum zurückgeben
         return $Sektionen;

       // end function
      }


      /**
      *  Funktion __getStatData4Month() [private/nonstatic]<br />
      *  Erzeugt den Statistik-Baum für die Monats-Ansicht.<br />
      *  <br />
      *  Christian Schäfer<br />
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Month(){

         $SQL = &$this->__getServiceObject('core::database','MySQLHandler');
         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('getStatData4Month');

         $Sektionen = array();

         // Array mit vorkommenden Tagen zusammenstellen
         $select_tage = "SELECT Tag FROM statistiken
                         WHERE
                           Jahr = '".$this->_LOCALS['Jahr']."'
                           AND
                           Monat = '".$this->_LOCALS['Monat']."'
                         GROUP BY Tag
                         ORDER BY Tag DESC;";
         $result_tage = $SQL->executeTextStatement($select_tage);

         while($data_tage = $SQL->fetchData($result_tage)){
            $Tage[] = $data_tage['Tag'];
          // end while
         }


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl besuchte Seiten');

         $select_seiten = "SELECT Tag, COUNT(Tag) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                           GROUP BY Tag
                           ORDER BY Tag DESC;";
         $result_seiten = $SQL->executeTextStatement($select_seiten);

         $SeitenProTag = array();
         $Offset = 0;
         $MaxZahl = 0;

         while($data_seiten = $SQL->fetchData($result_seiten)){

            $E = new statEintrag();
            $E->setzeAnzeigeText((dateTimeManager::addLeadingZero($data_seiten['Tag'])).'.'.(dateTimeManager::addLeadingZero($this->_LOCALS['Monat'])).'.'.$this->_LOCALS['Jahr']);
            $E->setzeWert($data_seiten['Anzahl']);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=tag&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$this->_LOCALS['Monat'].'&Tag='.$data_seiten['Tag']);
            $S->setzeEintrag($E);

            // Seiten / Tag für Berechnung in Array schreiben
            $SeitenProTag[$Offset]['Tag'] = $data_seiten['Tag'];
            $SeitenProTag[$Offset]['Seiten'] = $data_seiten['Anzahl'];
            $Offset++;

            $MaxZahl = max($data_seiten['Anzahl'],$MaxZahl);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Besucher');

         $BesucheProTag = array();
         $MaxAnzahl = 0;
         for($i = 0; $i < count($Tage); $i++){

            $select_max = "SELECT SessionID, Tag FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$Tage[$i]."'
                           GROUP BY SessionID
                           ORDER BY Tag DESC;";
            $result_max =  $SQL->executeTextStatement($select_max);
            $Besucher = $SQL->getNumRows($result_max);

            $BesucheProTag[$i]['Tag'] = $Tage[$i];
            $BesucheProTag[$i]['Besucher'] = $Besucher;

            $E = new statEintrag();
            $E->setzeAnzeigeText((dateTimeManager::addLeadingZero($Tage[$i])).'.'.(dateTimeManager::addLeadingZero($this->_LOCALS['Monat'])).'.'.$this->_LOCALS['Jahr']);
            $E->setzeWert($Besucher);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=tag&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$this->_LOCALS['Monat'].'&Tag='.$Tage[$i]);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$Besucher);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Seiten/Besucher');

         for($i = 0; $i < count($BesucheProTag); $i++){

            $SeitenProBesucher[$i]['Tag'] = $BesucheProTag[$i]['Tag'];
            $SeitenProBesucher[$i]['Anzahl'] = round($SeitenProTag[$i]['Seiten'] / $BesucheProTag[$i]['Besucher'],0);

            $E = new statEintrag();
            $E->setzeAnzeigeText((dateTimeManager::addLeadingZero($BesucheProTag[$i]['Tag'])).'.'.(dateTimeManager::addLeadingZero($this->_LOCALS['Monat'])).'.'.$this->_LOCALS['Jahr']);
            $E->setzeWert(round($SeitenProTag[$i]['Seiten'] / $BesucheProTag[$i]['Besucher'],0));
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=tag&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$this->_LOCALS['Monat'].'&Tag='.$BesucheProTag[$i]['Tag']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$SeitenProBesucher[$i]['Anzahl']);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Aufgerufene Seiten / Medien (TOP 10)');

         $select_medien = "SELECT Name, COUNT(Name) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                           GROUP BY Name
                           ORDER BY Anzahl DESC, Name ASC
                           LIMIT 10;";
         $result_medien = $SQL->executeTextStatement($select_medien);

         $MaxZahl = 0;

         while($data_medien = $SQL->fetchData($result_medien)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_medien['Name']);
            $E->setzeWert($data_medien['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_medien['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Suchmaschinen / Spider / Crawler (TOP 10)');

         $select_spider = "SELECT REPLACE(Browser,'[BOT] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             INSTR(Browser,'[BOT]')
                           GROUP BY Browser
                           ORDER BY Anzahl DESC, Name ASC
                           LIMIT 10;";
         $result_spider = $SQL->executeTextStatement($select_spider);

         $MaxZahl = 0;

         while($data_spider = $SQL->fetchData($result_spider)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_spider['Browser']);
            $E->setzeWert($data_spider['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_spider['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Browser (TOP 10)');

         $select_browser = "SELECT REPLACE(Browser,'[BROWSER] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              Monat = '".$this->_LOCALS['Monat']."'
                              AND
                              INSTR(Browser,'[BROWSER]')
                            GROUP BY Browser
                            ORDER BY Anzahl DESC, Browser ASC
                            LIMIT 10;";
         $result_browser = $SQL->executeTextStatement($select_browser);

         $MaxZahl = 0;

         while($data_browser = $SQL->fetchData($result_browser)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_browser['Browser']);
            $E->setzeWert($data_browser['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_browser['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Sprache (TOP 10)');

         $select_sprachen = "SELECT Sprache, COUNT(Sprache) AS Anzahl FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              Monat = '".$this->_LOCALS['Monat']."'
                            GROUP BY Sprache
                            ORDER BY Anzahl DESC, Sprache ASC
                            LIMIT 10;";
         $result_sprachen = $SQL->executeTextStatement($select_sprachen);

         $MaxZahl = 0;

         while($data_sprachen = $SQL->fetchData($result_sprachen)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_sprachen['Sprache']);
            $E->setzeWert($data_sprachen['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_sprachen['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Betriebssysteme (TOP 10)');

         $select_betriebssystem = "SELECT Betriebssystem, COUNT(Betriebssystem) AS Anzahl FROM statistiken
                                   WHERE
                                     Jahr = '".$this->_LOCALS['Jahr']."'
                                     AND
                                     Monat = '".$this->_LOCALS['Monat']."'
                                   GROUP BY Betriebssystem
                                   ORDER BY Anzahl DESC, Betriebssystem ASC
                                   LIMIT 10;";
         $result_betriebssystem = $SQL->executeTextStatement($select_betriebssystem);

         $MaxZahl = 0;

         while($data_betriebssystem = $SQL->fetchData($result_betriebssystem)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_betriebssystem['Betriebssystem']);
            $E->setzeWert($data_betriebssystem['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_betriebssystem['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Herkunft (TOP 10)');

         $select_herkunft = "SELECT Herkunft, COUNT(Herkunft) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                             GROUP BY Herkunft
                             ORDER BY Anzahl DESC, Herkunft ASC
                             LIMIT 10;";
         $result_herkunft = $SQL->executeTextStatement($select_herkunft);

         $MaxZahl = 0;

         while($data_herkunft = $SQL->fetchData($result_herkunft)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_herkunft['Herkunft']);
            $E->setzeWert($data_herkunft['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_herkunft['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('IP-Adressen (bzw. Rechner) (TOP 10)');

         $select_ip = "SELECT IPAdresse, COUNT(IPAdresse) AS Anzahl FROM statistiken
                       WHERE
                         Jahr = '".$this->_LOCALS['Jahr']."'
                         AND
                         Monat = '".$this->_LOCALS['Monat']."'
                       GROUP BY IPAdresse
                       ORDER BY Anzahl DESC, IPAdresse ASC
                       LIMIT 10;";
         $result_ip = $SQL->executeTextStatement($select_ip);

         $MaxZahl = 0;

         while($data_ip = $SQL->fetchData($result_ip)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_ip['IPAdresse']);
            $E->setzeWert($data_ip['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_ip['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('DNS-Namen (bzw. Rechner) (TOP 10)');

         $select_dns = "SELECT DNSAdresse, COUNT(DNSAdresse) AS Anzahl FROM statistiken
                        WHERE
                          Jahr = '".$this->_LOCALS['Jahr']."'
                          AND
                          Monat = '".$this->_LOCALS['Monat']."'
                        GROUP BY DNSAdresse
                        ORDER BY Anzahl DESC, DNSAdresse ASC
                        LIMIT 10;";
         $result_dns = $SQL->executeTextStatement($select_dns);

         $MaxZahl = 0;

         while($data_dns = $SQL->fetchData($result_dns)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_dns['DNSAdresse']);
            $E->setzeWert($data_dns['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_dns['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Benutzer (TOP 10)');

         $select_benutzer = "SELECT BenutzerName, COUNT(BenutzerName) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                             GROUP BY BenutzerName
                             ORDER BY Anzahl DESC, BenutzerName ASC
                             LIMIT 10;";
         $result_benutzer = $SQL->executeTextStatement($select_benutzer);

         $MaxZahl = 0;

         while($data_benutzer = $SQL->fetchData($result_benutzer)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_benutzer['BenutzerName']);
            $E->setzeWert($data_benutzer['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_benutzer['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;

         $T->stop('getStatData4Month');

         return $Sektionen;

       // end function
      }


      /**
      *  Funktion __getStatData4Day() [private/nonstatic]<br />
      *  Erzeugt den Statistik-Baum für die Tages-Ansicht.<br />
      *  <br />
      *  Christian Schäfer<br />
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Day(){

         $SQL = &$this->__getServiceObject('core::database','MySQLHandler');
         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('getStatData4Day');

         $Sektionen = array();

         // Array mit vorkommenden Stunden zusammenstellen
         $select_stunden = "SELECT Stunde FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              Monat = '".$this->_LOCALS['Monat']."'
                              AND
                              Tag = '".$this->_LOCALS['Tag']."'
                            GROUP BY Stunde
                            ORDER BY Stunde DESC;";
         $result_stunden = $SQL->executeTextStatement($select_stunden);

         while($data_stunden = $SQL->fetchData($result_stunden)){
            $Stunden[] = $data_stunden['Stunde'];
          // end while
         }


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl besuchte Seiten');

         $select_seiten = "SELECT Stunde, COUNT(Stunde) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                           GROUP BY Stunde
                           ORDER BY Stunde DESC;";
         $result_seiten =  $SQL->executeTextStatement($select_seiten);

         $SeitenProTag = array();
         $Offset = 0;
         $MaxZahl = 0;

         while($data_seiten = $SQL->fetchData($result_seiten)){

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::addLeadingZero($data_seiten['Stunde']).':00 - '.dateTimeManager::addLeadingZero($data_seiten['Stunde'] + 1).':00');
            $E->setzeWert($data_seiten['Anzahl']);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=stunde&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$this->_LOCALS['Monat'].'&Tag='.$this->_LOCALS['Tag'].'&Stunde='.$data_seiten['Stunde']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_seiten['Anzahl']);

            // Seiten / Tag für Berechnung in Array schreiben
            $SeitenProStunde[$Offset]['Stunde'] = $data_seiten['Stunde'];
            $SeitenProStunde[$Offset]['Seiten'] = $data_seiten['Anzahl'];
            $Offset++;

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Besucher');

         $BesucheProTag = array();
         $MaxZahl = 0;

         for($i = 0; $i < count($Stunden); $i++){

            $select_max = "SELECT SessionID, Stunde FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                             AND
                             Stunde = '".$Stunden[$i]."'
                           GROUP BY SessionID
                           ORDER BY Stunde DESC;";
            $result_max =  $SQL->executeTextStatement($select_max);
            $Besucher = $SQL->getNumRows($result_max);

            $BesucheProStunde[$i]['Stunde'] = $Stunden[$i];
            $BesucheProStunde[$i]['Besucher'] = $Besucher;

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::addLeadingZero($Stunden[$i]).':00 - '.dateTimeManager::addLeadingZero($Stunden[$i] + 1).':00');
            $E->setzeWert($Besucher);
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=stunde&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$this->_LOCALS['Monat'].'&Tag='.$this->_LOCALS['Tag'].'&Stunde='.$Stunden[$i]);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$Besucher);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new linkTabellenStatSektion();
         $S->setzeTitel('Anzahl Seiten/Besucher');

         $MaxZahl = 0;

         for($i = 0; $i < count($BesucheProStunde); $i++){

            $SeitenProBesucher[$i]['Stunde'] = $BesucheProStunde[$i]['Stunde'];
            $SeitenProBesucher[$i]['Anzahl'] = round($SeitenProStunde[$i]['Seiten'] / $BesucheProStunde[$i]['Besucher'],0);

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::addLeadingZero($BesucheProStunde[$i]['Stunde']).':00 - '.dateTimeManager::addLeadingZero($BesucheProStunde[$i]['Stunde'] + 1).':00');
            $E->setzeWert(round($SeitenProStunde[$i]['Seiten'] / $BesucheProStunde[$i]['Besucher'],0));
            $E->setzeLink('./?Modul=StatistikPanel&pagepart=stunde&Jahr='.$this->_LOCALS['Jahr'].'&Monat='.$this->_LOCALS['Monat'].'&Tag='.$this->_LOCALS['Tag'].'&Stunde='.$BesucheProStunde[$i]['Stunde']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$BesucheProStunde[$i]['Stunde']);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Aufgerufene Seiten / Medien');

         $select_medien = "SELECT Name, COUNT(Name) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                           GROUP BY Name
                           ORDER BY Anzahl DESC, Name ASC;";
         $result_medien = $SQL->executeTextStatement($select_medien);

         $MaxZahl = 0;

         while($data_medien = $SQL->fetchData($result_medien)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_medien['Name']);
            $E->setzeWert($data_medien['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_medien['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Suchmaschinen / Spider / Crawler');

         $select_spider = "SELECT REPLACE(Browser,'[BOT] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                             AND
                             INSTR(Browser,'[BOT]')
                           GROUP BY Browser
                           ORDER BY Anzahl DESC, Name ASC;";
         $result_spider = $SQL->executeTextStatement($select_spider);

         $MaxZahl = 0;

         while($data_spider = $SQL->fetchData($result_spider)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_spider['Browser']);
            $E->setzeWert($data_spider['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_spider['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Browser');

         $select_browser = "SELECT REPLACE(Browser,'[BROWSER] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              Monat = '".$this->_LOCALS['Monat']."'
                              AND
                              Tag = '".$this->_LOCALS['Tag']."'
                              AND
                              INSTR(Browser,'[BROWSER]')
                            GROUP BY Browser
                            ORDER BY Anzahl DESC, Browser ASC;";
         $result_browser = $SQL->executeTextStatement($select_browser);

         $MaxZahl = 0;

         while($data_browser = $SQL->fetchData($result_browser)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_browser['Browser']);
            $E->setzeWert($data_browser['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_browser['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Sprache');

         $select_sprachen = "SELECT Sprache, COUNT(Sprache) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                               AND
                               Tag = '".$this->_LOCALS['Tag']."'
                             GROUP BY Sprache
                             ORDER BY Anzahl DESC, Sprache ASC;";
         $result_sprachen = $SQL->executeTextStatement($select_sprachen);

         $MaxZahl = 0;

         while($data_sprachen = $SQL->fetchData($result_sprachen)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_sprachen['Sprache']);
            $E->setzeWert($data_sprachen['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_sprachen['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Betriebssysteme');

         $select_betriebssystem = "SELECT Betriebssystem, COUNT(Betriebssystem) AS Anzahl FROM statistiken
                                   WHERE
                                     Jahr = '".$this->_LOCALS['Jahr']."'
                                     AND
                                     Monat = '".$this->_LOCALS['Monat']."'
                                     AND
                                     Tag = '".$this->_LOCALS['Tag']."'
                                   GROUP BY Betriebssystem
                                   ORDER BY Anzahl DESC, Betriebssystem ASC;";
         $result_betriebssystem = $SQL->executeTextStatement($select_betriebssystem);

         $MaxZahl = 0;

         while($data_betriebssystem = $SQL->fetchData($result_betriebssystem)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_betriebssystem['Betriebssystem']);
            $E->setzeWert($data_betriebssystem['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_betriebssystem['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Herkunft');

         $select_herkunft = "SELECT Herkunft, COUNT(Herkunft) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                               AND
                               Tag = '".$this->_LOCALS['Tag']."'
                             GROUP BY Herkunft
                             ORDER BY Anzahl DESC, Herkunft ASC;";
         $result_herkunft = $SQL->executeTextStatement($select_herkunft);

         $MaxZahl = 0;

         while($data_herkunft = $SQL->fetchData($result_herkunft)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_herkunft['Herkunft']);
            $E->setzeWert($data_herkunft['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_herkunft['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('IP-Adressen (bzw. Rechner)');

         $select_ip = "SELECT IPAdresse, COUNT(IPAdresse) AS Anzahl FROM statistiken
                       WHERE
                         Jahr = '".$this->_LOCALS['Jahr']."'
                         AND
                         Monat = '".$this->_LOCALS['Monat']."'
                         AND
                         Tag = '".$this->_LOCALS['Tag']."'
                       GROUP BY IPAdresse
                       ORDER BY Anzahl DESC, IPAdresse ASC;";
         $result_ip = $SQL->executeTextStatement($select_ip);

         while($data_ip = $SQL->fetchData($result_ip)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_ip['IPAdresse']);
            $E->setzeWert($data_ip['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_ip['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('DNS-Namen (bzw. Rechner)');

         $select_dns = "SELECT DNSAdresse, COUNT(DNSAdresse) AS Anzahl FROM statistiken
                        WHERE
                          Jahr = '".$this->_LOCALS['Jahr']."'
                          AND
                          Monat = '".$this->_LOCALS['Monat']."'
                          AND
                          Tag = '".$this->_LOCALS['Tag']."'
                        GROUP BY DNSAdresse
                        ORDER BY Anzahl DESC, DNSAdresse ASC;";
         $result_dns = $SQL->executeTextStatement($select_dns);

         while($data_dns = $SQL->fetchData($result_dns)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_dns['DNSAdresse']);
            $E->setzeWert($data_dns['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_dns['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Benutzer');

         $select_benutzer = "SELECT BenutzerName, COUNT(BenutzerName) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                               AND
                               Tag = '".$this->_LOCALS['Tag']."'
                             GROUP BY BenutzerName
                             ORDER BY Anzahl DESC, BenutzerName ASC;";
         $result_benutzer = $SQL->executeTextStatement($select_benutzer);

         while($data_benutzer = $SQL->fetchData($result_benutzer)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_benutzer['BenutzerName']);
            $E->setzeWert($data_benutzer['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_benutzer['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;

         $T->stop('getStatData4Day');

         return $Sektionen;

       // end function
      }


      /**
      *  Funktion __getStatData4Hour() [private/nonstatic]<br />
      *  Erzeugt den Statistik-Baum für die Stunden-Ansicht.<br />
      *  <br />
      *  Christian Schäfer<br />
      *  Version 0.1, 05.06.2006<br />
      */
      function getStatData4Hour(){

         $SQL = &$this->__getServiceObject('core::database','MySQLHandler');
         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('getStatData4Hour');

         $Sektionen = array();

         // Array mit vorkommenden Minuten zusammenstellen
         $select_minuten = "SELECT Minute FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              Monat = '".$this->_LOCALS['Monat']."'
                              AND
                              Tag = '".$this->_LOCALS['Tag']."'
                              AND
                              Stunde = '".$this->_LOCALS['Stunde']."'
                            GROUP BY Minute
                            ORDER BY Minute DESC;";
         $result_minuten = $SQL->executeTextStatement($select_minuten);

         while($data_minuten = $SQL->fetchData($result_minuten)){
            $Minuten[] = $data_minuten['Minute'];
          // end while
         }


         //
         // ======================================================================================================================
         //
         $S = new tabellenStatSektion();
         $S->setzeTitel('Anzahl besuchte Seiten');

         $select_seiten = "SELECT Minute, COUNT(Minute) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                             AND
                             Stunde = '".$this->_LOCALS['Stunde']."'
                           GROUP BY Minute
                           ORDER BY Minute DESC;";
         $result_seiten =  $SQL->executeTextStatement($select_seiten);

         $SeitenProTag = array();
         $Offset = 0;
         $MaxZahl = 0;

         while($data_seiten = $SQL->fetchData($result_seiten)){

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::addLeadingZero($this->_LOCALS['Stunde']).':'.dateTimeManager::addLeadingZero($data_seiten['Minute']));
            $E->setzeWert($data_seiten['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_seiten['Anzahl']);

            // Seiten / Tag für Berechnung in Array schreiben
            $SeitenProMinute[$Offset]['Minute'] = $data_seiten['Minute'];
            $SeitenProMinute[$Offset]['Seiten'] = $data_seiten['Anzahl'];
            $Offset++;

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new tabellenStatSektion();
         $S->setzeTitel('Anzahl Besucher');

         $BesucheProTag = array();
         $MaxZahl = 0;

         for($i = 0; $i < count($Minuten); $i++){

            $select_max = "SELECT SessionID, Minute FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                             AND
                             Stunde = '".$this->_LOCALS['Stunde']."'
                             AND
                             Minute = '".$Minuten[$i]."'
                           GROUP BY SessionID
                           ORDER BY Minute DESC;";
            $result_max =  $SQL->executeTextStatement($select_max);
            $Besucher = $SQL->getNumRows($result_max);

            $BesucheProMinute[$i]['Minute'] = $Minuten[$i];
            $BesucheProMinute[$i]['Besucher'] = $Besucher;

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::addLeadingZero($this->_LOCALS['Stunde']).':'.dateTimeManager::addLeadingZero($Minuten[$i]));
            $E->setzeWert($Besucher);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$Besucher);

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new tabellenStatSektion();
         $S->setzeTitel('Anzahl Seiten/Besucher');

         $MaxZahl = 0;

         for($i = 0; $i < count($BesucheProMinute); $i++){

            $SeitenProBesucher[$i]['Minute'] = $BesucheProMinute[$i]['Minute'];
            $SeitenProBesucher[$i]['Anzahl'] = round($SeitenProMinute[$i]['Seiten'] / $BesucheProMinute[$i]['Besucher'],0);

            $E = new statEintrag();
            $E->setzeAnzeigeText(dateTimeManager::addLeadingZero($this->_LOCALS['Stunde']).':'.dateTimeManager::addLeadingZero($BesucheProMinute[$i]['Minute']));
            $E->setzeWert(round($SeitenProMinute[$i]['Seiten'] / $BesucheProMinute[$i]['Besucher'],0));
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,round($SeitenProMinute[$i]['Seiten'] / $BesucheProMinute[$i]['Besucher'],0));

          // end for
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Aufgerufene Seiten / Medien');

         $select_medien = "SELECT Name, COUNT(Name) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                             AND
                             Stunde = '".$this->_LOCALS['Stunde']."'
                           GROUP BY Name
                           ORDER BY Anzahl DESC, Name ASC;";
         $result_medien = $SQL->executeTextStatement($select_medien);

         $MaxZahl = 0;

         while($data_medien = $SQL->fetchData($result_medien)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_medien['Name']);
            $E->setzeWert($data_medien['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_medien['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Suchmaschinen / Spider / Crawler');

         $select_spider = "SELECT REPLACE(Browser,'[BOT] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                           WHERE
                             Jahr = '".$this->_LOCALS['Jahr']."'
                             AND
                             Monat = '".$this->_LOCALS['Monat']."'
                             AND
                             Tag = '".$this->_LOCALS['Tag']."'
                             AND
                             Stunde = '".$this->_LOCALS['Stunde']."'
                             AND
                             INSTR(Browser,'[BOT]')
                           GROUP BY Browser
                           ORDER BY Anzahl DESC, Name ASC;";
         $result_spider = $SQL->executeTextStatement($select_spider);

         $MaxZahl = 0;

         while($data_spider = $SQL->fetchData($result_spider)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_spider['Browser']);
            $E->setzeWert($data_spider['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_spider['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Browser');

         $select_browser = "SELECT REPLACE(Browser,'[BROWSER] ','') AS Browser, COUNT(Browser) AS Anzahl FROM statistiken
                            WHERE
                              Jahr = '".$this->_LOCALS['Jahr']."'
                              AND
                              Monat = '".$this->_LOCALS['Monat']."'
                              AND
                              Tag = '".$this->_LOCALS['Tag']."'
                              AND
                              Stunde = '".$this->_LOCALS['Stunde']."'
                              AND
                              INSTR(Browser,'[BROWSER]')
                            GROUP BY Browser
                            ORDER BY Anzahl DESC, Browser ASC;";
         $result_browser = $SQL->executeTextStatement($select_browser);

         $MaxZahl = 0;

         while($data_browser = $SQL->fetchData($result_browser)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_browser['Browser']);
            $E->setzeWert($data_browser['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_browser['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Sprache');

         $select_sprachen = "SELECT Sprache, COUNT(Sprache) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                               AND
                               Tag = '".$this->_LOCALS['Tag']."'
                               AND
                               Stunde = '".$this->_LOCALS['Stunde']."'
                             GROUP BY Sprache
                             ORDER BY Anzahl DESC, Sprache ASC;";
         $result_sprachen = $SQL->executeTextStatement($select_sprachen);

         $MaxZahl = 0;

         while($data_sprachen = $SQL->fetchData($result_sprachen)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_sprachen['Sprache']);
            $E->setzeWert($data_sprachen['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_sprachen['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Betriebssysteme');

         $select_betriebssystem = "SELECT Betriebssystem, COUNT(Betriebssystem) AS Anzahl FROM statistiken
                                   WHERE
                                     Jahr = '".$this->_LOCALS['Jahr']."'
                                     AND
                                     Monat = '".$this->_LOCALS['Monat']."'
                                     AND
                                     Tag = '".$this->_LOCALS['Tag']."'
                                     AND
                                     Stunde = '".$this->_LOCALS['Stunde']."'
                                   GROUP BY Betriebssystem
                                   ORDER BY Anzahl DESC, Betriebssystem ASC;";
         $result_betriebssystem = $SQL->executeTextStatement($select_betriebssystem);

         $MaxZahl = 0;

         while($data_betriebssystem = $SQL->fetchData($result_betriebssystem)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_betriebssystem['Betriebssystem']);
            $E->setzeWert($data_betriebssystem['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_betriebssystem['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Herkunft');

         $select_herkunft = "SELECT Herkunft, COUNT(Herkunft) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                               AND
                               Tag = '".$this->_LOCALS['Tag']."'
                               AND
                               Stunde = '".$this->_LOCALS['Stunde']."'
                             GROUP BY Herkunft
                             ORDER BY Anzahl DESC, Herkunft ASC;";
         $result_herkunft = $SQL->executeTextStatement($select_herkunft);

         $MaxZahl = 0;

         while($data_herkunft = $SQL->fetchData($result_herkunft)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_herkunft['Herkunft']);
            $E->setzeWert($data_herkunft['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_herkunft['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('IP-Adressen (bzw. Rechner)');

         $select_ip = "SELECT IPAdresse, COUNT(IPAdresse) AS Anzahl FROM statistiken
                       WHERE
                         Jahr = '".$this->_LOCALS['Jahr']."'
                         AND
                         Monat = '".$this->_LOCALS['Monat']."'
                         AND
                         Tag = '".$this->_LOCALS['Tag']."'
                         AND
                         Stunde = '".$this->_LOCALS['Stunde']."'
                       GROUP BY IPAdresse
                       ORDER BY Anzahl DESC, IPAdresse ASC;";
         $result_ip = $SQL->executeTextStatement($select_ip);

         $MaxZahl = 0;

         while($data_ip = $SQL->fetchData($result_ip)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_ip['IPAdresse']);
            $E->setzeWert($data_ip['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_ip['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('DNS-Namen (bzw. Rechner)');

         $select_dns = "SELECT DNSAdresse, COUNT(DNSAdresse) AS Anzahl FROM statistiken
                        WHERE
                          Jahr = '".$this->_LOCALS['Jahr']."'
                          AND
                          Monat = '".$this->_LOCALS['Monat']."'
                          AND
                          Tag = '".$this->_LOCALS['Tag']."'
                          AND
                          Stunde = '".$this->_LOCALS['Stunde']."'
                        GROUP BY DNSAdresse
                        ORDER BY Anzahl DESC, DNSAdresse ASC;";
         $result_dns = $SQL->executeTextStatement($select_dns);

         $MaxZahl = 0;

         while($data_dns = $SQL->fetchData($result_dns)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_dns['DNSAdresse']);
            $E->setzeWert($data_dns['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_dns['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;


         //
         // ======================================================================================================================
         //
         $S = new einfacheStatSektion();
         $S->setzeTitel('Benutzer');

         $select_benutzer = "SELECT BenutzerName, COUNT(BenutzerName) AS Anzahl FROM statistiken
                             WHERE
                               Jahr = '".$this->_LOCALS['Jahr']."'
                               AND
                               Monat = '".$this->_LOCALS['Monat']."'
                               AND
                               Tag = '".$this->_LOCALS['Tag']."'
                               AND
                               Stunde = '".$this->_LOCALS['Stunde']."'
                             GROUP BY BenutzerName
                             ORDER BY Anzahl DESC, BenutzerName ASC;";
         $result_benutzer = $SQL->executeTextStatement($select_benutzer);

         $MaxZahl = 0;

         while($data_benutzer = $SQL->fetchData($result_benutzer)){

            $E = new statEintrag();
            $E->setzeAnzeigeText($data_benutzer['BenutzerName']);
            $E->setzeWert($data_benutzer['Anzahl']);
            $S->setzeEintrag($E);

            $MaxZahl = max($MaxZahl,$data_benutzer['Anzahl']);

          // end while
         }

         $S->setzeTeiler($this->__calculateDivisor($MaxZahl));
         $Sektionen[] = $S;

         $T->stop('getStatData4Hour');

         return $Sektionen;

       // end function
      }


      /**
      *  @private
      *
      *  Ermittelt den Teiler für die Darstellung der Balken an Hand eines Maximal-Wertes.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 04.06.2006<br />
      */
      function __calculateDivisor($MaxAnzahl){

         if($MaxAnzahl > $this->__MaxBreite){
            $Teiler = $MaxAnzahl/$this->__MaxBreite;
          // end if
         }
         else{
            $Teiler = 1;
          // end if
         }

         return $Teiler;

       // end function
      }

    // end class
   }
?>