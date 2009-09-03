<?php
   import('core::logging','Logger');
   import('core::database','connectionManager');
   import('tools::filesystem','FilesystemManager');
   import('sites::apfdocupage::biz','APFModel');


   /**
    * @package modules::fulltextsearch::data
    * @class fulltextsearchIndexer
    *
    * Implements an indexer for the full text search.
    * Details on the concept can be seen on http://www.phpbar.de/w/Volltextsuche.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 06.03.2008<br />
    * Version 0.2, 07.06.2008 (removed timer due to performance leaks)<br />
    */
   class fulltextsearchIndexer extends coreObject {

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
      *  Version 0.3, 03.10.2008 (Added some new characters to the title regexp)<br />
      *  Version 0.4, 15.10.2008 (Added some characters to the urlname)<br />
      *  Version 0.5, 10.01.2009 (Added the ? to the allowed characters of the title)<br />
      */
      function importArticles(){

         // Timer holen
         $T = &Singleton::getInstance('BenchmarkTimer');

         // Logger erzeugen
         $L = &Singleton::getInstance('Logger');

         // Konfiguration holen
         $Config = &$this->__getConfiguration('sites::apfdocupage::biz','fulltextsearch');

         // Connection holen
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQL = &$cM->getConnection($Config->getValue('Database','ConnectionKey'));

         // delete old articles
         $L->logEntry($this->__LogFileName,'[DELETE] Delete articles ...');
         $delete = 'TRUNCATE search_articles';
         $SQL->executeTextStatement($delete);

         // list content files
         $Files = FilesystemManager::getFolderContent($this->__ContentFolder);

         // import files
         foreach($Files as $File){

            // Auf Datei prüfen
            if(!is_dir($this->__ContentFolder.'/'.$File)){

               // Log-Eintrag schreiben
               $L->logEntry($this->__LogFileName,'[START] Create article from "'.$File.'" ...');

               // Status-Cache löschen
               clearstatcache();

               // extract attributes from the file name
               $Lang = substr($File,2,2);
               $FileName = substr($File,5,(strlen($File) - 10));
               $PageID = substr($FileName,0,3);
               $ModStamp = date('Y-m-d H:i:s',filemtime($this->__ContentFolder.'/'.$File));

               // extract title and urlname
               $Content = file_get_contents($this->__ContentFolder.'/'.$File);

               preg_match('/title="([A-Za-z0-9\(\) \/\-&;.:!\?]+)"/i',$Content,$TitleMatches);

               if(isset($TitleMatches[1])){
                  $Title = $TitleMatches[1];
                // end if
               }
               else{
                  $Title = '---';
                  $L->logEntry($this->__LogFileName,'- File "'.$FileName.'" contains no title ...');
                // end else
               }

               preg_match('/urlname="([A-Za-z0-9\-\.]+)"/i',$Content,$URLNameMatches);

               if(isset($URLNameMatches[1])){
                  $URLName = $URLNameMatches[1];
                // end if
               }
               else{
                  $URLName = '---';
                  $L->logEntry($this->__LogFileName,'- File "'.$FileName.'" contains no urlname ...');
                // end else
               }

               // In Artikel-Datenbank einfügen
               $insert = 'INSERT INTO search_articles
                          (Title,PageID,URLName,Language,FileName,ModificationTimestamp)
                          VALUES
                          (\''.$Title.'\',\''.$PageID.'\',\''.$URLName.'\',\''.$Lang.'\',\''.$FileName.'\',\''.$ModStamp.'\');';
               $SQL->executeTextStatement($insert);

               // Log-Eintrag schreiben
               $L->logEntry($this->__LogFileName,'[FINISH] Create article from "'.$File.'" ...');
               $L->flushLogBuffer();

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
            //echo '<br /><br />ArticleID: '.$ArticleID.' (file: '.$data_articles['FileName'].')<br />';

            // log index run
            $L->logEntry($this->__LogFileName,'[START] Indexing article "'.$data_articles['FileName'].'" (ID: '.$ArticleID.', Lang: '.$data_articles['Language'].') ...');

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

         // fill the model
         $Model = &Singleton::getInstance('APFModel');
         $Model->setAttribute('page.id',$PageID);
         $Model->setAttribute('page.contentfilename','c_'.$Language.'_'.$FileName.'.html');
         $Model->setAttribute('page.language',$Language);

         // create a page
         $CurrentPage = new Page('SearchIndex',false);

         // apply context and language
         $CurrentPage->set('Context',$this->__Context);
         $CurrentPage->set('Language',$Language);

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

         // Sonderzeichen ersetzen und normalisieren
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

         // Stopwords löschen und gegen Leerzeichen ersetzen
         include(APPS__PATH.'/sites/apfdocupage/data/indexer/Stopwords.php');
         foreach($Stopwords[$Language] as $Stopword){
            $Content = preg_replace('/ '.$Stopword.' /',' ',$Content);
          // end foreach
         }

         // Wörter mit nur zwei Buchstaben entfernen
         $Content = preg_replace('/(\s[A-Za-z]{1,2})\s/','',$Content);

         // Inhalt zurückgeben
         return $Content;

       // end function
      }

    // end class
   }
?>