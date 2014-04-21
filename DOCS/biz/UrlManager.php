<?php
namespace DOCS\biz;

use APF\core\database\ConnectionManager;
use APF\core\database\DatabaseConnection;
use APF\core\pagecontroller\APFObject;
use APF\core\singleton\Singleton;

/**
 * @package DOCS\biz
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
    * Returns the title of the page described by the given params.
    *
    * @param string $pageId The id of the target page.
    * @param string $lang The desired target language.
    * @param string $versionId The page's version.
    * @return string The page title.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2009<br />
    */
   public function getPageTitle($pageId, $lang, $versionId) {

      // deliver cached content, if possible
      $hash = md5($pageId . $lang . $versionId);
      if (isset($this->titleCache[$hash])) {
         return $this->titleCache[$hash];
      }

      // select title from the database
      $sql = & $this->getConnection();
      // avoid injections!
      $pageId = $sql->escapeValue($pageId);
      $lang = $sql->escapeValue($lang);
      $versionId = $sql->escapeValue($versionId);
      $select = 'SELECT Title
                 FROM search_articles
                 WHERE PageID = \'' . $pageId . '\' AND Language = \'' . $lang . '\' AND Version = \'' . $versionId . '\'';
      $result = $sql->executeTextStatement($select);
      $data = $sql->fetchData($result);

      /* @var $model APFModel */
      $model = & Singleton::getInstance('DOCS\biz\APFModel');
      $defaultVersionId = $model->getDefaultVersionId();
      if (empty($data['Title']) && $versionId != $defaultVersionId) {
         $title = $this->getPageTitle($pageId, $lang, $defaultVersionId);
      } else {
         $title = $data['Title'];
      }

      $this->titleCache[$hash] = $title;
      return $this->titleCache[$hash];

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

   /**
    * @public
    *
    * Generates a APF docs page link.
    *
    * @param string $pageId The id of the target page.
    * @param string $lang The desired target language.
    * @param string $versionId The page's version.
    * @return string The desired link.
    * @throws \InvalidArgumentException In case of empty version identifier.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2009<br />
    */
   public function generateLink($pageId, $lang, $versionId) {

      if (empty($versionId)) {
         throw new \InvalidArgumentException('Version identifier must not be null or empty!');
      }

      // deliver cached content, if possible
      $hash = md5($pageId . $lang . $versionId);
      if (isset($this->linkCache[$hash])) {
         return $this->linkCache[$hash];
      }

      // setup the basic part of the link
      $pageIdent = (string)$pageId;
      /* @var $model APFModel */
      $model = & Singleton::getInstance('DOCS\biz\APFModel');
      $urlLangIdent = $model->getUrlIdentifier($lang);
      $urlVersionIdent = $model->getVersionUrlIdentifier();
      $defaultVersionId = $model->getDefaultVersionId();

      // fetch the url name from the database using the fulltext search
      $sql = & $this->getConnection();
      // avoid injections!
      $pageId = $sql->escapeValue($pageId);
      $lang = $sql->escapeValue($lang);
      $versionId = $sql->escapeValue($versionId);
      $select = 'SELECT URLName
                 FROM search_articles
                 WHERE PageID = \'' . $pageId . '\' AND Language = \'' . $lang . '\' AND Version = \'' . $versionId . '\' ';
      $result = $sql->executeTextStatement($select);
      $data = $sql->fetchData($result);
      $urlName = $data['URLName'];

      // in case we cannot generate a url for the requested version id, we need to fallback to the default version
      if (empty($data['URLName'])) {
         $select = 'SELECT URLName
                 FROM search_articles
                 WHERE PageID = \'' . $pageId . '\' AND Language = \'' . $lang . '\' AND Version = \'' . $model->getDefaultVersionId() . '\' ';
         $result = $sql->executeTextStatement($select);
         $data = $sql->fetchData($result);
         $urlName = $data['URLName'];
         $versionId = $model->getDefaultVersionId();
      }

      if (!empty($urlName)) {
         $pageIdent .= '-' . $urlName;
      }

      if ($versionId === null || $versionId == $defaultVersionId) {
         $this->linkCache[$hash] = '/' . $urlLangIdent . '/' . $pageIdent;
      } else {
         $this->linkCache[$hash] = '/' . $urlLangIdent . '/' . $pageIdent . '/' . $urlVersionIdent . '/' . $versionId;
      }

      return $this->linkCache[$hash];
   }

}
