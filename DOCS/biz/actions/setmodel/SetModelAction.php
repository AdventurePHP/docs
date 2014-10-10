<?php
namespace DOCS\biz\actions\setmodel;

use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use APF\core\singleton\Singleton;
use APF\tools\http\HeaderManager;
use DOCS\biz\APFModel;

/**
 * @package DOCS\biz\actions\setmodel
 * @class SetModelAction
 *
 * Represents the front controller action to initialize the page's model.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 22.08.2008<br />
 */
class SetModelAction extends AbstractFrontcontrollerAction {

   private static $ABOUT_PAGEID = '073';

   public function isActive() {
      return $this->getFrontController()->getActionByName('stat') == null;
   }

   /**
    * @public
    *
    * Implements the abstract run method. Initializes the model.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.08.2008<br />
    * Version 0.2, 17.09.2008 (Added functionality to write the content and quicknavi file names to the model)<br />
    * Version 0.3, 19.09.2008 (Title is now set by the &lt;doku:title /&gt;-Tag)<br />
    * Version 0.4, 31.01.2009 (Removed the variablenHandler)<br />
    */
   public function run() {

      /* @var $model APFModel */
      $model = &Singleton::getInstance('DOCS\biz\APFModel');

      $request = &self::getRequest();

      // register request parameters
      $pageIndicatorNames = $model->getAttribute('page.indicator');
      $pageIndicatorNameDe = $pageIndicatorNames['de'];
      $pageIndicatorNameEn = $pageIndicatorNames['en'];
      $currentPageIndicators[$pageIndicatorNameDe] = $request->getParameter($pageIndicatorNameDe);
      $currentPageIndicators[$pageIndicatorNameEn] = $request->getParameter($pageIndicatorNameEn);

      // check request parameters and set current language
      if ($currentPageIndicators[$pageIndicatorNameDe] != null) {
         $language = 'de';
         $model->setLanguage($language);
         $this->getFrontController()->setLanguage($language);
         $this->setLanguage($language);
         $model->setAttribute('perspective.name', 'content');
      } elseif ($currentPageIndicators[$pageIndicatorNameEn] != null) {
         $language = 'en';
         $model->setLanguage($language);
         $this->getFrontController()->setLanguage($language);
         $this->setLanguage($language);
         $model->setAttribute('perspective.name', 'content');
      } else {

         // use default language of the front controller (maybe a problem, perhaps use
         // a session instance to store the language)
         $language = $this->getFrontController()->getLanguage();
         $this->setLanguage($language);
         $model->setLanguage($language);
         $model->setAttribute('perspective.name', 'start');
      }

      // fill current page id
      $pageName = $currentPageIndicators[$pageIndicatorNames[$model->getLanguage()]];
      $pageId = substr($pageName, 0, 3);
      if (empty($pageId)) {
         $pageId = '001';
      }
      $model->setAttribute('page.id', $pageId);

      // fill current version
      $versionId = $request->getParameter($model->getVersionUrlIdentifier());
      if (empty($versionId)) {
         $versionId = $model->getDefaultVersionId();
      }
      $model->setVersionId($versionId);

      $contentFilePath = $model->getAttribute('content.filepath');

      $model->setPageVersions($this->getAvailableVersions($contentFilePath . '/content', 'c', $language, $pageId));

      // fill the current content and quicknavi file name
      $model->setPageContentFileName($this->getFileName($contentFilePath . '/content', 'c', $language, $pageId, $versionId));
      $model->setPageNaviFileName($this->getFileName($contentFilePath . '/quicknavi', 'n', $language, $pageId, $versionId));

      // initialize sidebar status
      if ($pageId === self::$ABOUT_PAGEID) {
         $model->setDisplaySidebar(false);
      }

      // send real 404 in case the file is not found
      if (strpos($model->getPageContentFileName(), '404') !== false) {
         HeaderManager::send($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
      }

   }

   /**
    * @private
    *
    * Returns the file name for the content and quick navi files.
    *
    * @param string $contentFilePath The file path of the content file.
    * @param string $prefix The file prefix (c or n).
    * @param string $language The current language.
    * @param string $pageId The current page id.
    * @param string $versionId The current page's version id.
    *
    * @return string The name of the file to load.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 17.09.2008<br />
    */
   private function getFileName($contentFilePath, $prefix, $language, $pageId, $versionId) {

      // read files from given directory
      $contentFiles = glob($contentFilePath . '/' . $prefix . '_' . $language . '_' . $pageId . '_' . $versionId . '*');

      // check, if appropriate file exists
      if (!isset($contentFiles[0])) {
         return $prefix . '_' . $language . '_404_2.X.html';
      } else {
         return basename($contentFiles[0]);
      }

   }

   private function getAvailableVersions($contentFilePath, $prefix, $language, $pageId) {
      $files = glob($contentFilePath . '/' . $prefix . '_' . $language . '_' . $pageId . '*');
      $versions = array();
      foreach ($files as $file) {
         $fileName = basename($file);
         if (preg_match('#' . $prefix . '_' . $language . '_' . $pageId . '_([A-Za-z0-9\.]{3})_#', $fileName, $matches)) {
            $versions[] = $matches[1];
         }
      }

      return $versions;
   }

}
