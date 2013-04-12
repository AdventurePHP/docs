<?php
namespace APF\sites\apf\biz;

use APF\core\benchmark\BenchmarkTimer;
use APF\core\database\ConnectionManager;
use APF\core\database\DatabaseConnection;
use APF\core\pagecontroller\APFObject;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;

/**
 * @package APF\sites\apf\biz
 * @name UrlManager
 *
 * This tool let's you generate a APF docu page link by a
 * given page id (e.g. "072") and provides multi-language
 * page titles.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 22.12.2009<br />
 */
final class UrlManager extends APFObject {

   private $linkCache = array();
   private $titleCache = array();

   /**
    * @public
    *
    * Generates a APF docs page link.
    *
    * @param string $pageId The id of the target page.
    * @param string $lang The desired target language.
    * @param string $versionId The page's version.
    * @return string The desired link.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2009<br />
    */
   public function generateLink($pageId, $lang, $versionId = null) {

      /* @var $t BenchmarkTimer */
      $t = & Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'UrlManager::generateLink(' . $pageId . ',' . $lang . ', ' . $versionId . ')';
      $t->start($id);

      // deliver cached content, if possible
      $hash = md5($pageId . $lang . $versionId);
      if (isset($this->linkCache[$hash])) {
         $t->stop($id);
         return $this->linkCache[$hash];
      }

      // setup the basic part of the link
      $pageIdent = (string)$pageId;
      /* @var $model APFModel */
      $model = & Singleton::getInstance('APF\sites\apf\biz\APFModel');
      $urlLangIdent = $model->getUrlIdentifier($lang);
      $urlVersionIdent = $model->getVersionUrlIdentifier();

      // fetch the url name from the database using the fulltext search
      $sql = & $this->getConnection();
      // avoid injections!
      $pageId = $sql->escapeValue($pageId);
      $lang = $sql->escapeValue($lang);
      $select = 'SELECT URLName
                 FROM search_articles
                 WHERE PageID = \'' . $pageId . '\' AND Language = \'' . $lang . '\'';
      $result = $sql->executeTextStatement($select);
      $data = $sql->fetchData($result);
      $urlName = $data['URLName'];

      if (!empty($urlName)) {
         $pageIdent .= '-' . $urlName;
      }

      if ($versionId === null) {
         $this->linkCache[$hash] = '/' . $urlLangIdent . '/' . $pageIdent;
      } else {
         $this->linkCache[$hash] = '/' . $urlLangIdent . '/' . $pageIdent . '/' . $urlVersionIdent . '/' . $versionId;
      }

      $t->stop($id);
      return $this->linkCache[$hash];
   }

   /**
    * @public
    *
    * Returns the title of the page described by the given params.
    *
    * @param string $pageId The id of the target page.
    * @param string $lang The desired target language.
    * @return string The page title.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2009<br />
    */
   public function getPageTitle($pageId, $lang) {

      /* @var $t BenchmarkTimer */
      $t = & Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'UrlManager::getPageTitle(' . $pageId . ',' . $lang . ')';
      $t->start($id);

      // deliver cached content, if possible
      $hash = md5($pageId, $lang);
      if (isset($this->titleCache[$hash])) {
         $t->stop($id);
         return $this->titleCache[$hash];
      }

      // select title from the database
      $sql = & $this->getConnection();
      // avoid injections!
      $pageId = $sql->escapeValue($pageId);
      $lang = $sql->escapeValue($lang);
      $select = 'SELECT Title
                 FROM search_articles
                 WHERE PageID = \'' . $pageId . '\' AND Language = \'' . $lang . '\'';
      $result = $sql->executeTextStatement($select);
      $data = $sql->fetchData($result);
      $title = $data['Title'];

      $this->linkCache[$hash] = $title;
      $t->stop($id);
      return $this->linkCache[$hash];

   }

   /**
    * Initialize database connection for current usage.
    *
    * @return DatabaseConnection The database connection.
    */
   private function &getConnection() {
      /* @var $cM ConnectionManager */
      $cM = & $this->getServiceObject('APF\core\database\ConnectionManager');
      return $cM->getConnection('FulltextSearch');
   }

}
