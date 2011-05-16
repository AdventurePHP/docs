<?php
import('core::logging', 'Logger');
import('tools::filesystem', 'FilesystemManager');
import('sites::apf::biz', 'APFModel');

/**
 * @package sites::apf::data::indexer
 * @class FulltextsearchIndexer
 *
 * Implements an indexer for the full text search.
 * Details on the concept can be seen on http://www.phpbar.de/w/Volltextsuche.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 06.03.2008<br />
 * Version 0.2, 07.06.2008 (removed timer due to performance leaks)<br />
 */
class FulltextsearchIndexer extends APFObject {

   /**
    * @private
    * @var string Name of the log file.
    */
   private $logFileName = 'fulltextsearchindexer';
   
   /**
    * @private
    * @var string Content dir.
    */
   private $contentFolder = '../apps/sites/apf/pres/content';

   public function setLogFileName($logFileName) {
      $this->logFileName = $logFileName;
   }

   public function setContentFolder($contentFolder) {
      $this->contentFolder = $contentFolder;
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
   public function importArticles() {

      $T = &Singleton::getInstance('BenchmarkTimer');
      $L = &Singleton::getInstance('Logger');

      $config = $this->getConfiguration('sites::apf::biz', 'fulltextsearch.ini');

      $cM = &$this->getServiceObject('core::database', 'ConnectionManager');
      $SQL = &$cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      // delete old articles
      $L->logEntry($this->logFileName, '[DELETE] Delete articles ...');
      $delete = 'TRUNCATE search_articles';
      $SQL->executeTextStatement($delete);

      // list content files
      $files = FilesystemManager::getFolderContent($this->contentFolder);

      // import files
      foreach ($files as $file) {

         if (!is_dir($this->contentFolder . '/' . $file)) {

            $L->logEntry($this->logFileName, '[START] Create article from "' . $file . '" ...');

            clearstatcache();

            // extract attributes from the file name
            $lang = substr($file, 2, 2);
            $fileName = substr($file, 5, (strlen($file) - 10));
            $pageId = substr($fileName, 0, 3);
            $modStamp = date('Y-m-d H:i:s', filemtime($this->contentFolder . '/' . $file));

            // extract title and urlname
            $content = file_get_contents($this->contentFolder . '/' . $file);

            preg_match('/title="([A-Za-z0-9äöüßÄÖÜ\(\) \/\-&;,.:!\?]+)"/i', $content, $titleMatches);

            if (isset($titleMatches[1])) {
               $title = $titleMatches[1];
            } else {
               $title = '---';
               $L->logEntry($this->logFileName, '- File "' . $fileName . '" contains no title ...');
            }

            preg_match('/urlname="([A-Za-z0-9\-\.]+)"/i', $content, $urlNameMatches);

            if (isset($urlNameMatches[1])) {
               $urlName = $urlNameMatches[1];
            } else {
               $urlName = '---';
               $L->logEntry($this->logFileName, '- File "' . $fileName . '" contains no urlname ...');
            }

            preg_match('/parent="([0-9]{1,3})"/i', $content, $parentPageMatches);

            if (isset($parentPageMatches[1])) {
               $parentPage = $parentPageMatches[1];
            } else {
               $parentPage = '0';
               $L->logEntry($this->logFileName, '- File "' . $fileName . '" contains no parent page ...');
            }

            // In Artikel-Datenbank einf�gen
            $insert = 'INSERT INTO search_articles
                             (Title,PageID,ParentPage,URLName,Language,FileName,ModificationTimestamp)
                             VALUES
                             (\'' . $title . '\',\'' . $pageId . '\',\'' . $parentPage . '\',\'' . $urlName . '\',\'' . $lang . '\',\'' . $fileName . '\',\'' . $modStamp . '\');';
            $SQL->executeTextStatement($insert);

            $L->logEntry($this->logFileName, '[FINISH] Create article from "' . $file . '" ...');
            $L->flushLogBuffer();
         }
      }
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
   public function createIndex() {

      // create logger instance
      $l = &Singleton::getInstance('Logger');

      // get configuration
      $config = $this->getConfiguration('sites::apf::biz', 'fulltextsearch.ini');

      // get connection
      $cM = &$this->getServiceObject('core::database', 'ConnectionManager');
      $SQL = &$cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      // delete the recent index
      $delete = 'TRUNCATE search_index';
      $SQL->executeTextStatement($delete);

      // select articles
      $select_articles = 'SELECT * FROM search_articles';
      $result_articles = $SQL->executeTextStatement($select_articles);

      while ($data_articles = $SQL->fetchData($result_articles)) {

         // gather article id
         $articleId = $data_articles['ArticleID'];
         //echo '<br /><br />ArticleID: '.$articleId.' (pageId: '.$data_articles['PageID'].', file: '.$data_articles['FileName'].', lang: '.$data_articles['Language'].')';
         // log index run
         $l->logEntry($this->logFileName, '[START] Indexing article "' . $data_articles['FileName'] . '" (ID: ' . $articleId . ', Lang: ' . $data_articles['Language'] . ') ...');

         // generate html code of the current content
         $content = $this->createPageOutput($data_articles['PageID'], $data_articles['FileName'], $data_articles['Language']);

         // normalize content
         $content = $this->normalizeContent($content, $data_articles['Language']);

         // Noch vorhandene W�rter in ein Array verpacken, so dass es duchlaufen werden kann
         // Trennung an Hand von Leer-, Satz- oder Sonderzeichen
         $contentArray = preg_split('[\s|-|,|;|:|/|!|\?|\.|\n|\r|\t]', $content);

         // free memory
         unset($content);

         // log word count
         $l->logEntry($this->logFileName, '- Words in text: ' . count($contentArray));

         // delete old index
         $delete_index = 'DELETE FROM search_index WHERE ArticleID = \'' . $articleId . '\'';
         $SQL->executeTextStatement($delete_index);

         // do indexing
         $index = array();

         foreach ($contentArray as $word) {

            // trim current word
            $word = trim($word);

            // inly indev non empty words
            if (!empty($word)) {

               // retrieve word key (or save implicitly)
               $wordId = $this->getWordId($word);

               // create index
               if (isset($index[$wordId])) {
                  $index[$wordId]['WordCount'] = $index[$wordId]['WordCount'] + 1;
                  // end if
               } else {
                  $index[$wordId]['WordID'] = $wordId;
                  $index[$wordId]['WordCount'] = 1;
               }
            }
         }

         // free memory
         unset($contentArray);

         // sort index
         sort($index);

         // log word count
         $l->logEntry($this->logFileName, '- Indexed words: ' . count($index));

         // save result
         foreach ($index as $wordId => $indexValues) {

            // create index
            $insert_index = 'INSERT INTO search_index
                                   (WordID,ArticleID,WordCount)
                                   VALUES
                                   (\'' . $indexValues['WordID'] . '\',\'' . $articleId . '\',\'' . $indexValues['WordCount'] . '\')';
            $SQL->executeTextStatement($insert_index);
         }

         // free memory
         unset($index);

         // create log entry
         $l->logEntry($this->logFileName, '[FINISH] Indexing article "' . $data_articles['FileName'] . '".');
         $l->logEntry($this->logFileName, '');
         $l->flushLogBuffer();
      }
   }

   /**
    *  @private
    *
    *  Liefert die ID eines Suchwortes zur�ck. Falls das Wort noch nicht<br />
    *  in der Datenbank gespeichert ist, wird dieses gespeichert.<br />
    *
    *  @param string $Word; Wort f�r den Suchindex
    *  @return int $WordID; ID des Suchwortes
    *
    *  @author Christian Achatz
    *  @version
    *  Version 0.1, 06.03.2008<br />
    */
   private function getWordId($Word) {

      // Konfiguration holen
      $config = $this->getConfiguration('sites::apf::biz', 'fulltextsearch.ini');

      // Connection holen
      $cM = &$this->getServiceObject('core::database', 'ConnectionManager');
      $sql = &$cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      // Wort selektieren
      $select_word = 'SELECT WordID FROM search_word WHERE Word = \'' . $Word . '\'';
      $result_word = $sql->executeTextStatement($select_word);
      $data_word = $sql->fetchData($result_word);

      // ID auslesen
      if (!isset($data_word['WordID'])) {
         $insert_word = 'INSERT INTO search_word (Word) VALUES (\'' . $Word . '\')';
         $result_word = $sql->executeTextStatement($insert_word);
         $ID = $sql->getLastID();
      } else {
         $ID = $data_word['WordID'];
      }

      return $ID;

   }

   /**
    *  @private
    *
    *  Erzeugt den HTML-Code einer Seite.<br />
    *
    *  @param string $pageId id of the current page
    *  @param string $fileName URL name of the content page's file
    *  @param string $lang language of the page
    *  @return string $PageOutput HTML code of the content page
    *
    *  @author Christian Achatz
    *  @version
    *  Version 0.1, 09.03.2008<br />
    *  Version 0.2, 03.10.2008 (Introduced the APFModel to be able to use the html:content tag)<br />
    */
   private function createPageOutput($pageId, $fileName, $lang) {

      // fill the model
      $model = &Singleton::getInstance('APFModel');
      $model->setAttribute('page.id', $pageId);
      $model->setAttribute('page.contentfilename', 'c_' . $lang . '_' . $fileName . '.html');
      $model->setAttribute('page.language', $lang);

      // create a page
      $currentPage = new Page();

      // apply context and language
      $currentPage->setContext($this->__Context);
      $currentPage->setLanguage($lang);

      // load indexer template
      $currentPage->loadDesign('sites::apf::pres::templates::indexer', 'createindex');

      // create output
      return $currentPage->transform();

   }

   /**
    *  @private
    *
    *  Normalisiert den Inhalt und entfernt Stopw�rter.<br />
    *
    *  @param string $Content; Inhalt einer Seite (HTML-Code)
    *  @param string $Language; Sprache der Seite
    *  @return string $NormalizedContent; Normalisierter Inhalt der Seite
    *
    *  @author Christian Achatz
    *  @version
    *  Version 0.1, 09.03.2008<br />
    */
   private function normalizeContent($Content, $Language) {

      // Sonderzeichen ersetzen und normalisieren
      $locSearch[] = '/�/i';
      $locSearch[] = '/�/i';
      $locSearch[] = '/�/i';
      $locSearch[] = '/�/i';
      $locSearch[] = '/\|/i';
      $locSearch[] = '/�|�|<|>|\{|\}|\[|\]|\(|\)/i';
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
      $Content = preg_replace($locSearch, $locReplace, $Content);

      // Stopwords löschen und gegen Leerzeichen ersetzen
      include(APPS__PATH . '/sites/apf/data/indexer/stopwords.php');
      foreach ($Stopwords[$Language] as $Stopword) {
         $Content = preg_replace('/ ' . $Stopword . ' /', ' ', $Content);
      }

      // Wörter mit nur zwei Buchstaben entfernen
      return preg_replace('/(\s[A-Za-z]{1,2})\s/', '', $Content);

   }

}
?>