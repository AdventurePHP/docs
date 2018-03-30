<?php
namespace DOCS\data\indexer;

use APF\core\configuration\Configuration;
use APF\core\database\AbstractDatabaseHandler;
use APF\core\database\ConnectionManager;
use APF\core\database\DatabaseHandlerException;
use APF\core\logging\LogEntry;
use APF\core\logging\Logger;
use APF\core\pagecontroller\APFObject;
use APF\core\pagecontroller\Document;
use APF\core\pagecontroller\Page;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use APF\tools\filesystem\FilesystemManager;
use DOCS\biz\APFModel;

/**
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
   private $contentFolder = './DOCS/pres/content';

   private $charset;

   /**
    * @var array List of stop words according to language (e.g. $stopWords['en'][1] => 'but')
    */
   private $stopWords = [];

   public function __construct() {
      $this->charset = Registry::retrieve('APF\core', 'Charset');
      $this->stopWords = include(dirname(__FILE__) . '/stopwords.php');
   }

   public function setLogFileName($logFileName) {
      $this->logFileName = $logFileName;
   }

   public function setContentFolder($contentFolder) {
      $this->contentFolder = $contentFolder;
   }

   /**
    * @public
    *
    * Imports the articles from the content directory.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 16.03.2008<br />
    * Version 0.2, 02.10.2008 (Changed to fit new documentation page)<br />
    * Version 0.3, 03.10.2008 (Added some new characters to the title regexp)<br />
    * Version 0.4, 15.10.2008 (Added some characters to the url name)<br />
    * Version 0.5, 10.01.2009 (Added the ? to the allowed characters of the title)<br />
    */
   public function importArticles() {

      /* @var $L Logger */
      $L = Singleton::getInstance(Logger::class);

      $config = $this->getConfig();
      $SQL = $this->getConnection($config);

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
            $version = substr($file, 9, 3);

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

            $insert = 'INSERT INTO search_articles
                             (Title, PageID, Version, ParentPage, URLName, Language, FileName, ModificationTimestamp)
                             VALUES
                             (\'' . $title . '\', \'' . $pageId . '\', \'' . $version . '\', \'' . $parentPage . '\', \'' . $urlName . '\', \'' . $lang . '\', \'' . $fileName . '\', \'' . $modStamp . '\');';
            $SQL->executeTextStatement($insert);

            $L->logEntry($this->logFileName, '[FINISH] Create article from "' . $file . '" ...');
            $L->flushLogBuffer();
         }
      }
   }

   /**
    * @return Configuration
    */
   private function getConfig() {
      return $this->getConfiguration('DOCS\biz', 'fulltextsearch.ini');
   }

   /**
    * @param Configuration $config
    *
    * @return AbstractDatabaseHandler
    */
   private function getConnection(Configuration $config) {
      /* @var $cM ConnectionManager */
      $cM = $this->getServiceObject(ConnectionManager::class);

      return $cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));
   }

   /**
    * @public
    *
    * Creates the index out of the articles in database.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 09.03.2008<br />
    * Version 0.2, 03.20.2008 (Changed some details to fit new page structure)<br />
    */
   public function createIndex() {

      /* @var $l Logger */
      $l = Singleton::getInstance(Logger::class);

      $config = $this->getConfig();
      $SQL = $this->getConnection($config);

      // delete the recent index
      $delete = 'TRUNCATE search_index';
      $SQL->executeTextStatement($delete);

      // select articles
      $select_articles = 'SELECT * FROM search_articles';
      $result_articles = $SQL->executeTextStatement($select_articles);

      while ($data_articles = $SQL->fetchData($result_articles)) {

         // gather article id
         $articleId = $data_articles['ArticleID'];

         // log index run
         $l->logEntry($this->logFileName, '[START] Indexing article "' . $data_articles['FileName'] . '" (ID: ' . $articleId . ', Lang: ' . $data_articles['Language'] . ') ...');

         // generate html code of the current content
         $content = $this->createPageOutput($data_articles['PageID'], $data_articles['FileName'], $data_articles['Language'], $data_articles['Version']);

         // normalize content
         $content = $this->normalizeContent($content, $data_articles['Language']);

         // split up words to build up the search index
         $contentArray = preg_split('[\s|-|,|;|:|/|!|\?|\.|\n|\r|\t]', $content);

         // free memory
         unset($content);

         // log word count
         $l->logEntry($this->logFileName, '- Words in text: ' . count($contentArray));

         // delete old index
         $delete_index = 'DELETE FROM search_index WHERE ArticleID = \'' . $articleId . '\'';
         $SQL->executeTextStatement($delete_index);

         // do indexing
         $index = [];

         foreach ($contentArray as $word) {

            // trim current word
            $word = trim($word);

            // only index non-empty words
            if (!empty($word)) {

               try {
                  // retrieve word key (or save implicitly)
                  $wordId = $this->getWordId($word);

                  // create index
                  if (isset($index[$wordId])) {
                     $index[$wordId]['WordCount'] = $index[$wordId]['WordCount'] + 1;
                  } else {
                     $index[$wordId]['WordID'] = $wordId;
                     $index[$wordId]['WordCount'] = 1;
                  }
               } catch (DatabaseHandlerException $e) {
                  $l->logEntry($this->logFileName, 'Word "' . $word . '" cannot be added to index. Details: ' . $e, LogEntry::SEVERITY_ERROR);
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
         /** @noinspection PhpUnusedLocalVariableInspection */
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
    * @private
    *
    * Creates the page output of the requested page.
    *
    * @param string $pageId id of the current page.
    * @param string $fileName URL name of the content page's file.
    * @param string $lang language of the page.
    * @param string $version The current page version requested.
    *
    * @return string HTML code of the content page.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 09.03.2008<br />
    * Version 0.2, 03.10.2008 (Introduced the APFModel to be able to use the html:content tag)<br />
    */
   private function createPageOutput($pageId, $fileName, $lang, $version) {

      // fill the model
      /* @var $model APFModel */
      $model = Singleton::getInstance(APFModel::class);
      $model->setAttribute('page.id', $pageId);
      $model->setVersionId($version);
      $model->setPageContentFileName('c_' . $lang . '_' . $fileName . '.html');
      $model->setAttribute('page.language', $lang);
      $model->setAttribute('content.filepath', './DOCS/pres');

      Document::addTagLib('DOCS\pres\taglib\DocumentationLinkTag', 'doku', 'link');
      Document::addTagLib('DOCS\pres\taglib\DocumentationTitleTag', 'doku', 'title');
      Document::addTagLib('DOCS\pres\taglib\GenericHighlightTag', 'gen', 'highlight');
      Document::addTagLib('DOCS\pres\taglib\InternalLinkTag', 'int', 'link');

      Document::addTemplateExpression(\DOCS\pres\expression\BaseUrlExpression::class);

      // create a page
      $currentPage = new Page();

      // apply context and language
      $currentPage->setContext($this->getContext());
      $currentPage->setLanguage($lang);

      // load indexer template
      $currentPage->loadDesign('DOCS\pres\templates\indexer', 'createindex');

      // create output
      return $currentPage->transform();

   }

   /**
    * @private
    *
    * Normalizes the content and removes stop words.
    *
    * @param string $content HTML code of the page.
    * @param string $language Language of the page.
    *
    * @return string Normalized content.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 09.03.2008<br />
    */
   private function normalizeContent($content, $language) {

      // replace special characters and normalize
      $locSearch = [
            '/ß/',
            '/ä/',
            '/ö/',
            '/ü/',
            '/Ä/',
            '/Ö/',
            '/Ü/',
            '/\|/i',
            '/<|>|\{|\}|\[|\]|\(|\)/i',
            '/\'|\"/',
            '/=/'
      ];

      $locReplace = [
            'ss',
            'ae',
            'oe',
            'ue',
            'ae',
            'oe',
            'ue',
            '',
            '',
            '',
            ''
      ];

      $content = strip_tags($content);
      $content = stripslashes($content);
      $content = html_entity_decode($content, ENT_QUOTES, $this->charset);
      $content = strtolower($content);
      $content = trim($content);
      $content = preg_replace($locSearch, $locReplace, $content);

      foreach ($this->stopWords[$language] as $stopWord) {
         $content = preg_replace('/ ' . $stopWord . ' /', ' ', $content);
      }

      // remove words with less or equal that two characters to limit search index size
      return preg_replace('/(\s[A-Za-z]{1,2})\s/', '', $content);

   }

   /**
    * @private
    *
    * Returns the id of the given word while lazily creating the database entry.
    *
    * @param string $word Word for the search index.
    *
    * @return int Id of the given word within the index.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 06.03.2008<br />
    */
   private function getWordId($word) {

      $config = $this->getConfig();
      $sql = $this->getConnection($config);

      $select_word = 'SELECT WordID FROM search_word WHERE Word = \'' . $word . '\'';
      $result_word = $sql->executeTextStatement($select_word);
      $data_word = $sql->fetchData($result_word);

      if (!isset($data_word['WordID'])) {
         $insert_word = 'INSERT INTO search_word (Word) VALUES (\'' . $word . '\')';
         $sql->executeTextStatement($insert_word);
         $id = $sql->getLastID();
      } else {
         $id = $data_word['WordID'];
      }

      return $id;
   }

}
