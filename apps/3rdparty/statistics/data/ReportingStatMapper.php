<?php
   import('tools::datetime','dateTimeManager');
   import('core::database','ConnectionManager');
   import('3rdparty::statistics::biz','SimpleStatSection');
   import('3rdparty::statistics::biz','TableStatSection');
   import('3rdparty::statistics::biz','LinkTableStatSection');
   import('3rdparty::statistics::biz','StatEntry');

   /**
   *  @package 3rdparty::statistics::data
   *  @module ReportingStatMapper
   *
   *  Implements the statistic data loader.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 15.11.2008<br />
   *  Version 0.2. 16.11.2008<br />
   */
   class ReportingStatMapper extends APFObject {

      /**
      *  @private
      *  Contains a reference on the database connection.
      */
      private $conn = null;

      private $table = 'statistics';

      /**
      *  @private
      *  Contains the maximum length of the output area.
      */
      private $imageMaxLength = 800;

      /**
      *  @public
      *
      *  Initializes the mapper.
      *
      *  @param string $initParam the desired database connection key
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008<br />
      */
      public function init($initParam){
         $cM = &$this->getServiceObject('core::database','ConnectionManager');
         $this->conn = &$cM->getConnection($initParam);
      }

      /**
      *  @public
      *
      *  Returns the list of stat sections for the overview.
      *
      *  @return array $statSections list of stat sections
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008<br />
      */
      public function getStatData4Overview(){
         return $this->genericGetStatData(
                                     'Year',
                                     null,
                                     '10'
                                    );
       // end function
      }

      /**
      *  @public
      *
      *  Returns the list of stat sections for the year period.
      *
      *  @param string $year desired year
      *  @return array $statSections list of stat sections
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008<br />
      */
      public function getStatData4Year($year){
         return $this->genericGetStatData(
                                     'Month',
                                     'Year = \''.$year.'\'',
                                     '10'
                                    );
       // end function
      }

      /**
      *  @public
      *
      *  Returns the list of stat sections for the month period.
      *
      *  @param string $year desired year
      *  @param string $month desired month
      *  @return array $statSections list of stat sections
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008<br />
      */
      public function getStatData4Month($year,$month){
         return $this->genericGetStatData(
                                     'Day',
                                     'Year = \''.$year.'\' AND Month = \''.$month.'\'',
                                     '10'
                                    );
       // end function
      }

      /**
      *  @public
      *
      *  Returns the list of stat sections for the day period.
      *
      *  @param string $year desired year
      *  @param string $month desired month
      *  @param string $day desired day
      *  @return array $statSections list of stat sections
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008<br />
      */
      public function getStatData4Day($year,$month,$day){
         return $this->genericGetStatData(
                                     'Hour',
                                     'Year = \''.$year.'\' AND Month = \''.$month.'\' AND Day = \''.$day.'\'',
                                     '20'
                                    );
       // end function
      }

      /**
      *  @public
      *
      *  Returns the list of stat sections for the hour period.
      *
      *  @param string $year desired year
      *  @param string $month desired month
      *  @param string $day desired day
      *  @param string $hour desired hour
      *  @return array $statSections list of stat sections
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008<br />
      */
      public function getStatData4Hour($year,$month,$day,$hour){
         return $this->genericGetStatData(
                                     'Minute',
                                     'Year = \''.$year.'\' AND Month = \''.$month.'\' AND Day = \''.$day.'\' AND Hour = \''.$hour.'\''
                                    );
       // end function
      }

      /**
      *  @public
      *
      *  Implements a reusable loader for the various statistic views (overview, year, ...).
      *
      *  @param string $attribute the current attribute to load
      *  @param string $where the where statement for the current view
      *  @param string $limit the limit clause for the current view
      *  @return array $sections the stat section for the current view
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.11.2008 (Initial version of the abstract stat loader)<br />
      *  Version 0.2. 16.11.2008 (Finished work and tried to abstract some more parts)<br />
      */
      private function genericGetStatData($attribute,$where = null,$limit = null){

         // initialize return list
         $statSections = array();

         // create array with all available periode values (years, months, ...)
         $select_period = 'SELECT '.$attribute.' FROM '.$this->table.' ';

         if($where !== null){
            $select_period .= 'WHERE '.$where.' ';
          // end if
         }

         $select_period .= 'GROUP BY '.$attribute.' ';
         $select_period .= 'ORDER BY '.$attribute.' DESC';
         $select_period = $select_period.';';
         $result_period = $this->conn->executeTextStatement($select_period);

         $available_period_values = array();
         while($data_period = $this->conn->fetchData($result_period)){
            $available_period_values[] = $data_period[$attribute];
          // end while
         }


         // 1. select count of visited pages per available period values
         $sect = new LinkTableStatSection();
         $sect->setAttribute('Title','Number of pages');

         $select_pages = 'SELECT '.$attribute.', COUNT('.$attribute.') AS count FROM '.$this->table.' ';

         if($where !== null){
            $select_pages .= 'WHERE '.$where.' ';
          // end if
         }

         $select_pages .= 'GROUP BY '.$attribute.' ';
         $select_pages .= 'ORDER BY '.$attribute.' DESC';
         $select_pages = $select_pages.';';
         $result_pages = $this->conn->executeTextStatement($select_pages);

         $pagesPerPeriod = array();
         $offset = 0;
         $max = 0;
         $entries = array();
         while($data_pages = $this->conn->fetchData($result_pages)){

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$data_pages[$attribute]);
            $entry->setAttribute('Value',$data_pages['count']);

            $pagesPerPeriod[$offset][$attribute] = $data_pages[$attribute];
            $pagesPerPeriod[$offset]['pages'] = $data_pages['count'];
            $offset++;

            $max = max($data_pages['count'],$max);
            $entries[] = $entry;

          // end while
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->calculateDivisor($max));
         $statSections[] = $sect;


         // 2. select count of visitors per available period values
         $sect = new LinkTableStatSection();
         $sect->setAttribute('Title','Number of unique visitors');

         // Maximalzahl der Besuche ermitteln
         $visitorsPerPeriod = array();
         $max = 0;
         $entries = array();
         $pagesPerVisitor = array();
         for($i = 0; $i < count($available_period_values); $i++){

            $select_max = 'SELECT SessionID FROM '.$this->table.'';
            if($where !== null){
               $select_max .= ' WHERE '.$where.' AND '.$attribute.' = \''.$available_period_values[$i].'\'';
             // end if
            }
            $select_max .= ' GROUP BY SessionID;';
            $result_max =  $this->conn->executeTextStatement($select_max);
            $visitor_count = $this->conn->getNumRows($result_max);

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$available_period_values[$i]);
            $entry->setAttribute('Value',$visitor_count);

            $pagesPerVisitor[$i][$attribute] = $available_period_values[$i];
            $pagesPerVisitor[$i]['count'] = round($pagesPerPeriod[$i]['pages'] / $visitor_count,0);

            $max = max($max,$visitor_count);
            $entries[] = $entry;

          // end for
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->calculateDivisor($max));
         $statSections[] = $sect;


         // 3. select / create pages per visitor (from the first two lists)
         $sect = new LinkTableStatSection();
         $sect->setAttribute('Title','Pages per unique visitor');

         $max = 0;
         $entries = array();
         for($i = 0; $i < count($pagesPerVisitor); $i++){

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$pagesPerVisitor[$i][$attribute]);
            $entry->setAttribute('Value',$pagesPerVisitor[$i]['count']);

            $max = max($max,$pagesPerVisitor[$i]['count']);
            $entries[] = $entry;
          // end for
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->calculateDivisor($max));
         $statSections[] = $sect;


         // 4. select TOP 10 requested sites / media files
         $select_pages = 'SELECT PageName AS displaytext, COUNT(PageName) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_pages .= ' WHERE '.$where;
          // end if
         }
         $select_pages .= ' GROUP BY PageName
                           ORDER BY count DESC, PageName ASC';
         if($limit !== null){
            $select_pages .= ' LIMIT '.$limit;
          // end if
         }
         $select_pages = $select_pages.';';
         $statSections[] = $this->getStatSectionByStatement($select_pages,'Pages by name');


         // 5. select TOP 10 bots
         $select_spider = 'SELECT REPLACE(Browser,\'[BOT] \',\'\') AS displaytext, COUNT(Browser) AS count FROM '.$this->table.'
                           WHERE INSTR(Browser,\'[BOT]\')';
         if($where !== null){
            $select_spider .= ' AND '.$where;
          // end if
         }
         $select_spider .= ' GROUP BY Browser
                            ORDER BY count DESC, PageName ASC';
         if($limit !== null){
            $select_spider .= ' LIMIT '.$limit;
          // end if
         }
         $select_spider = $select_spider.';';
         $statSections[] = $this->getStatSectionByStatement($select_spider,'Spider / crawler');


         // 6. select TOP 10 browsers
         $select_browser = 'SELECT REPLACE(Browser,\'[BROWSER] \',\'\') AS displaytext, COUNT(Browser) AS count FROM '.$this->table.'
                            WHERE INSTR(Browser,\'[BROWSER]\')';
         if($where !== null){
            $select_browser .= ' AND '.$where;
          // end if
         }
         $select_browser .= ' GROUP BY Browser
                             ORDER BY count DESC, Browser ASC';
         if($limit !== null){
            $select_browser .= ' LIMIT '.$limit;
          // end if
         }
         $select_browser = $select_browser.';';
         $statSections[] = $this->getStatSectionByStatement($select_browser,'Browser');


         // 7. select TOP 10 languages
         $select_lang = 'SELECT ClientLanguage AS displaytext, COUNT(ClientLanguage) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_lang .= ' WHERE '.$where;
          // end if
         }
         $select_lang .= ' GROUP BY ClientLanguage
                          ORDER BY count DESC, ClientLanguage ASC';
         if($limit !== null){
            $select_lang .= ' LIMIT '.$limit;
          // end if
         }
         $select_lang = $select_lang.';';
         $statSections[] = $this->getStatSectionByStatement($select_lang,'Client languages');


         // 8. select TOP 10 OSes
         $select_os = 'SELECT OS AS displaytext, COUNT(OS) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_os .= ' WHERE '.$where;
          // end if
         }
         $select_os .= ' GROUP BY OS
                          ORDER BY count DESC, OS ASC';
         if($limit !== null){
            $select_os .= ' LIMIT '.$limit;
          // end if
         }
         $select_os = $select_os.';';
         $statSections[] = $this->getStatSectionByStatement($select_os,'Operating systems');


         // 9. select TOP 10 referer
         $select_referer = 'SELECT Referer AS displaytext, COUNT(Referer) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_referer .= ' WHERE '.$where;
          // end if
         }
         $select_referer .= ' GROUP BY Referer
                             ORDER BY count DESC, Referer ASC';
         if($limit !== null){
            $select_referer .= ' LIMIT '.$limit;
          // end if
         }
         $select_referer = $select_referer.';';
         $statSections[] = $this->getStatSectionByStatement($select_referer,'Referer');


         // 10. select TOP 10 IP addresses
         $select_ip = 'SELECT IPAddress AS displaytext, COUNT(IPAddress) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_ip .= ' WHERE '.$where;
          // end if
         }
         $select_ip .= ' GROUP BY IPAddress
                        ORDER BY count DESC, IPAddress ASC';
         if($limit !== null){
            $select_ip .= ' LIMIT '.$limit;
          // end if
         }
         $select_ip = $select_ip.';';
         $statSections[] = $this->getStatSectionByStatement($select_ip,'IP addresses');


         // 11. select TOP 10 DNS addresses
         $select_dns = 'SELECT DNSAddress AS displaytext, COUNT(DNSAddress) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_dns .= ' WHERE '.$where;
          // end if
         }
         $select_dns .= ' GROUP BY DNSAddress
                         ORDER BY count DESC, DNSAddress ASC';
         if($limit !== null){
            $select_dns .= ' LIMIT '.$limit;
          // end if
         }
         $select_dns = $select_dns.';';
         $statSections[] = $this->getStatSectionByStatement($select_dns,'DNS names');


         // 12. select TOP 10 users
         $select_user = 'SELECT UserName as displaytext, COUNT(UserName) AS count FROM '.$this->table.'';
         if($where !== null){
            $select_user .= ' WHERE '.$where;
          // end if
         }
         $select_user .= ' GROUP BY UserName
                          ORDER BY count DESC, UserName ASC';
         if($limit !== null){
            $select_user .= ' LIMIT '.$limit;
          // end if
         }
         $select_user = $select_user.';';
         $statSections[] = $this->getStatSectionByStatement($select_user,'User');


         // return generated sections
         return $statSections;

       // end function
      }

      /**
      *  @private
      *
      *  Reads the desired stat section from the database using the given statement and title.
      *
      *  @param string $statement the statement to load the section's data
      *  @param string $title the title of the section
      *  @return SimpleStatSection $sect the resulting stat section
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 16.11.2008<br />
      */
      private function getStatSectionByStatement($statement,$title){

         $sect = new SimpleStatSection();
         $sect->setAttribute('Title',$title);

         $result = $this->conn->executeTextStatement($statement);

         $max = 0;
         $entries = array();

         while($data = $this->conn->fetchData($result)){

            $entry = new StatEntry();
            $entry->setAttribute('DisplayText',$data['displaytext']);
            $entry->setAttribute('Value',$data['count']);

            $max = max($max,$data['count']);
            $entries[] = $entry;

          // end while
         }

         $sect->setAttribute('Entries',$entries);
         $sect->setAttribute('Divisor',$this->calculateDivisor($max));
         return $sect;

       // end function
      }


      /**
      *  @private
      *
      *  Calculates the current divisor to get the maximum lenth of one value. For display purpose only.
      *
      *  @param string $value current maximum value
      *  qreturn string $divisor calculate divisor for te current stat section
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 04.06.2006<br />
      */
      private function calculateDivisor($value){

         if($value > $this->imageMaxLength){
            $divisor = $value/$this->imageMaxLength;
          // end if
         }
         else{
            $divisor = 1;
          // end if
         }

         return $divisor;

       // end function
      }

    // end class
   }
?>