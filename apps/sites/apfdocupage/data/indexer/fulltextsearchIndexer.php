<?php
   import('core::logging','Logger');
   import('core::database','connectionManager');
   import('core::filesystem','filesystemHandler');
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package modules::fulltextsearch::data
   *  @class fulltextsearchIndexer
   *
   *  Implementierts the indexer for the full text search.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 06.03.2008<br />
   *  Version 0.2, 07.06.2008 (removed timer due to performance leaks)<br />
   */
   class fulltextsearchIndexer extends coreObject
   {

      /**
      *  @private
      *  Name of the log file.
      */
      var $__LogFileName = 'fulltextsearchindexer';


      /**
      *  @private
      *  content dir.
      */
      var $__ContentFolder = './apps/sites/apfdocupage/pres/content';


      function fulltextsearchIndexer(){
      }


      /**
      *  @public
      *
      *  Imports the articles from the content directory.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 16.03.2008<br />
      *  Version 0.2, 02.10.2008 (Changed to fit new documentation page)<br />
      */
      function importArticles(){

         // Timer holen
         $T = &Singleton::getInstance('benchmarkTimer');

         // Logger erzeugen
         $L = &Singleton::getInstance('Logger');

         // Konfiguration holen
         $Config = &$this->__getConfiguration('sites::apfdocupage::biz','fulltextsearch');

         // Connection holen
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection($Config->getValue('Database','ConnectionKey'));

         // Bisherige Artikel löschen
         $L->logEntry($this->__LogFileName,'[DELETE] Delete articles ...');
         $delete = 'TRUNCATE search_articles';
         $SQL->executeTextStatement($delete);

         // Dateien auslesen
         $fH = new filesystemHandler($this->__ContentFolder);
         $Files = $fH->showDirContent();

         // Dateien importieren
         foreach($Files as $File){

            // Auf Datei prüfen
            if(!is_dir($this->__ContentFolder.'/'.$File)){

               // Log-Eintrag schreiben
               $L->logEntry($this->__LogFileName,'[START] Create article from "'.$File.'" ...');

               // Status-Cache löschen
               clearstatcache();

               // extract attributes from the file name
               /*echo '<br />$Lang: '.*/$Lang = substr($File,2,2);
               /*echo '<br />$FileName: '.*/$FileName = substr($File,5,(strlen($File) - 10));
               /*echo '<br />$PageID: '.*/$PageID = substr($FileName,0,3);
               /*echo '<br />$ModStamp: '.*/$ModStamp = date('Y-m-d H:i:s',filemtime($this->__ContentFolder.'/'.$File));

               // extract title and urlname
               $Content = file_get_contents($this->__ContentFolder.'/'.$File);

               preg_match('/title="([A-Za-z0-9\(\) \/\-&;.:]+)"/i',$Content,$TitleMatches);

               if(isset($TitleMatches[1])){
                  $Title = $TitleMatches[1];
                // end if
               }
               else{
                  $Title = '---';
                  $L->logEntry($this->__LogFileName,'- File "'.$FileName.'" contains no title ...');
                // end else
               }

               //echo '<br />$Title: '.$Title;

               preg_match('/urlname="([A-Za-z0-9\-]+)"/i',$Content,$URLNameMatches);

               if(isset($URLNameMatches[1])){
                  $URLName = $URLNameMatches[1];
                // end if
               }
               else{
                  $URLName = '---';
                  $L->logEntry($this->__LogFileName,'- File "'.$FileName.'" contains no urlname ...');
                // end else
               }

               //echo '<br />$URLName: '.$URLName;

               // In Artikel-Datenbank einfügen
               $insert = 'INSERT INTO search_articles
                          (Title,PageID,URLName,Language,FileName,ModificationTimestamp)
                          VALUES
                          (\''.$Title.'\',\''.$PageID.'\',\''.$URLName.'\',\''.$Lang.'\',\''.$FileName.'\',\''.$ModStamp.'\');';
               $SQL->executeTextStatement($insert);

               // Log-Eintrag schreiben
               $L->logEntry($this->__LogFileName,'[FINISH] Create article from "'.$File.'" ...');
               $L->flushLogBuffer();

               //echo '<br />---------------------------';

             // end if
            }

          // end if
         }

       // end function
      }


      /**
      *  @public
      *
      *  Creates the index out of the articles in database.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 09.03.2008<br />
      *  Version 0.2, 03.20.2008 (Changed some details to fit new page structure)<br />
      */
      function createIndex(){

         // create logger instance
         $L = &Singleton::getInstance('Logger');

         // get configuration
         $Config = &$this->__getConfiguration('sites::apfdocupage::biz','fulltextsearch');

         // get connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection($Config->getValue('Database','ConnectionKey'));

         // delete the recent index
         $delete = 'TRUNCATE search_index';
         $SQL->executeTextStatement($delete);

         // select articles
         $select_articles = 'SELECT * FROM search_articles';
         $result_articles = $SQL->executeTextStatement($select_articles);

         while($data_articles = $SQL->fetchData($result_articles)){

            // gather article id
            $ArticleID = $data_articles['ArticleID'];

            // log index run
            $L->logEntry($this->__LogFileName,'[START] Indexing article "'.$data_articles['FileName'].'" (ID: '.$ArticleID.') ...');

            // generate html code of the current content
            $Content = $this->__createPageOutput($data_articles['PageID'],$data_articles['FileName'],$data_articles['Language']);

            // normalize content
            $Content = $this->__normalizeContent($Content,$data_articles['Language']);

            // Noch vorhandene Wörter in ein Array verpacken, so dass es duchlaufen werden kann
            // Trennung an Hand von Leer-, Satz- oder Sonderzeichen
            $ContentArray = preg_split('[\s|-|,|;|:|/|!|\?|\.|\n|\r|\t]',$Content);

            // free memory
            unset($Content);

            // log word count
            $L->logEntry($this->__LogFileName,'- Words in text: '.count($ContentArray));

            // delete old index
            $delete_index = 'DELETE FROM search_index WHERE ArticleID = \''.$ArticleID.'\'';
            $SQL->executeTextStatement($delete_index);

            // do indexing
            $Index = array();

            foreach($ContentArray as $Word){

               // trim current word
               $Word = trim($Word);

               // inly indev non empty words
               if(!empty($Word)){

                  // retrieve word key (or save implicitly)
                  $WordID = $this->__getWordID($Word);

                  // create index
                  if(isset($Index[$WordID])){
                      $Index[$WordID]['WordCount'] = $Index[$WordID]['WordCount'] + 1;
                   // end if
                  }
                  else{
                     $Index[$WordID]['WordID'] = $WordID;
                     $Index[$WordID]['WordCount'] = 1;
                   // end else
                  }

                // end if
               }

             // end else
            }

            // free memory
            unset($ContentArray);

            // sort index
            sort($Index);

            // log word count
            $L->logEntry($this->__LogFileName,'- Indexed words: '.count($Index));

            // save result
            foreach($Index as $WordID => $IndexValues){

               // create index
               $insert_index = 'INSERT INTO search_index
                                (WordID,ArticleID,WordCount)
                                VALUES
                                (\''.$IndexValues['WordID'].'\',\''.$ArticleID.'\',\''.$IndexValues['WordCount'].'\')';
               $SQL->executeTextStatement($insert_index);

             // end foreach
            }

            // free memory
            unset($Index);

            // create log entry
            $L->logEntry($this->__LogFileName,'[FINISH] Indexing article "'.$data_articles['FileName'].'" (ID: '.$ArticleID.') ...');
            $L->logEntry($this->__LogFileName,'');
            $L->flushLogBuffer();

          // end while
         }

       // end function
      }


      /**
      *  @private
      *
      *  Liefert die ID eines Suchwortes zurück. Falls das Wort noch nicht<br />
      *  in der Datenbank gespeichert ist, wird dieses gespeichert.<br />
      *
      *  @param string $Word; Wort für den Suchindex
      *  @return int $WordID; ID des Suchwortes
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 06.03.2008<br />
      */
      function __getWordID($Word){

         // Timer starten
         //$T = &Singleton::getInstance('benchmarkTimer');
         //$T->start('fulltextsearchIndexer->__getWordID('.$Word.')');

         // Konfiguration holen
         $Config = &$this->__getConfiguration('sites::apfdocupage::biz','fulltextsearch');

         // Connection holen
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection($Config->getValue('Database','ConnectionKey'));

         // Wort selektieren
         $select_word = 'SELECT WordID FROM search_word WHERE Word = \''.$Word.'\'';
         $result_word = $SQL->executeTextStatement($select_word);
         $data_word = $SQL->fetchData($result_word);

         // ID auslesen
         if(!isset($data_word['WordID'])){
            $insert_word = 'INSERT INTO search_word (Word) VALUES (\''.$Word.'\')';
            $result_word = $SQL->executeTextStatement($insert_word);
            $ID = $SQL->getLastID();
          // end if
         }
         else{
            $ID = $data_word['WordID'];
          // end else
         }

         // Timer stoppen
         //$T->stop('fulltextsearchIndexer->__getWordID('.$Word.')');

         // ID zurückgeben
         return $ID;

       // end function
      }


      /**
      *  @private
      *
      *  Erzeugt den HTML-Code einer Seite.<br />
      *
      *  @param string $PageID id of the current page
      *  @param string $FileName URL name of the content page's file
      *  @param string $Language language of the page
      *  @return string $PageOutput HTML code of the content page
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 09.03.2008<br />
      *  Version 0.2, 03.10.2008 (Introduced the APFModel to be able to use the html:content tag)<br />
      */
      function __createPageOutput($PageID,$FileName,$Language){

         // create a page
         $CurrentPage = new Page('SearchIndex',false);

         // fill the model
         $Model = &Singleton::getInstance('APFModel');
         $Model->setAttribute('page.id',$PageID);
         $Model->setAttribute('page.contentfilename','c_'.$Language.'_'.$FileName.'.html');
         $Model->setAttribute('page.language',$Language);

         // apply context
         $CurrentPage->set('Context',$this->__Context);

         // load indexer template
         $CurrentPage->loadDesign('sites::apfdocupage::pres::templates::indexer','createindex');

         // create output
         return $CurrentPage->transform();

       // end function
      }


      /**
      *  @private
      *
      *  Normalisiert den Inhalt und entfernt Stopwörter.<br />
      *
      *  @param string $Content; Inhalt einer Seite (HTML-Code)
      *  @param string $Language; Sprache der Seite
      *  @return string $NormalizedContent; Normalisierter Inhalt der Seite
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 09.03.2008<br />
      */
      function __normalizeContent($Content,$Language){

         // Timer starten
         //$T = &Singleton::getInstance('benchmarkTimer');
         //$T->start('fulltextsearchIndexer->__normalizeContent()');

         // Sonderzeichen ersetzen und normalisieren
         //$T->start('Sonderzeichen ersetzen');
         $locSearch[] = '/ß/i';
         $locSearch[] = '/ä/i';
         $locSearch[] = '/ö/i';
         $locSearch[] = '/ü/i';
         $locSearch[] = '/\|/i';
         $locSearch[] = '/«|»|<|>|\{|\}|\[|\]|\(|\)/i';
         $locSearch[] = '/\'|\"/';
         $locSearch[] = '/=/';

         $locReplace[] = 'ss';
         $locReplace[] = 'ae';
         $locReplace[] = 'oe';
         $locReplace[] = 'ue';
         $locReplace[] = '';
         $locReplace[] = '';
         $locReplace[] = '';
         $locReplace[] = '';

         $Content = strip_tags($Content);
         $Content = stripslashes($Content);
         $Content = html_entity_decode($Content);
         $Content = strtolower($Content);
         $Content = trim($Content);
         $Content = preg_replace($locSearch,$locReplace,$Content);
         //$T->stop('Sonderzeichen ersetzen');

         // Stopwords löschen und gegen Leerzeichen ersetzen
         //$T->start('Stopwords ersetzen');
         //$T->start('import()');
         include(APPS__PATH.'/sites/apfdocupage/data/indexer/Stopwords.php');
         //$T->stop('import()');
         foreach($Stopwords[$Language] as $Stopword){
            $Content = preg_replace('/ '.$Stopword.' /',' ',$Content);
          // end foreach
         }
         //$T->stop('Stopwords ersetzen');

         // Wörter mit nur zwei Buchstaben entfernen
         //$T->start('Wörter mit > 2 Buchstaben ersetzen');
         $Content = preg_replace('/(\s[A-Za-z]{1,2})\s/','',$Content);
         //$T->stop('Wörter mit > 2 Buchstaben ersetzen');

         // Timer stoppen
         //$T->stop('fulltextsearchIndexer->__normalizeContent()');

         // Inhalt zurückgeben
         return $Content;

       // end function
      }

    // end class
   }
?>