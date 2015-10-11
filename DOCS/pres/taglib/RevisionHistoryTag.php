<?php
namespace DOCS\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use DOCS\biz\APFModel;
use DOCS\pres\controller\release\ReleaseBaseController;

/**
 * Displays the revision history for the desired release.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 30.12.2009<br />
 */
class RevisionHistoryTag extends Document {

   private static $FALLBACK_RELEASE_PARAM = 'fallback';

   public function onParseTime() {

      // get the desired release
      $release = $this->getReleaseNumber();

      // append the content to the current document and re-analyze the structure (link tags!)
      $this->content = '<div id="RevisionHistory">' . PHP_EOL;
      $this->content .= $this->getReleaseHeader($release);
      $this->content .= $this->getReleaseDescription($release);
      $this->content .= '</div>' . PHP_EOL;
      $this->extractTagLibTags();

      // append release number to HTML title to avoid duplicate titles for SEO reasons
      /* @var $model APFModel */
      $model = Singleton::getInstance(APFModel::class);
      $model->setTitle($model->getTitle() . ' ' . $this->getReleaseNumber());

   }

   public function onAfterAppend() {
   }

   private function getReleaseHeader($releaseNumber) {
      $title = (string) '<h2>';
      $config = $this->getConfiguration('DOCS\pres', 'labels.ini');
      $title .= $config->getSection($this->getLanguage())->getValue('downloads.changeset.text.heading');

      return $title . $releaseNumber . '</h2>';
   }

   /**
    * @private
    *
    * Returns the formatted text of the release description file. Resolves unencoded ampersands.
    *
    * @param string $releaseNumber The desired release to display the history of.
    *
    * @return string The revision history text from the release file.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    */
   private function getReleaseDescription($releaseNumber) {
      $relLocalDir = Registry::retrieve('DOCS', 'Releases.LocalDir');
      $historyFile = $relLocalDir . '/' . $releaseNumber . '/' . $this->language . '_release_description.html';
      if (file_exists($historyFile)) {
         return preg_replace('/&([A-Za-z0-9\-_]+)=([A-Za-z0-9\-_]+)/', '&amp;$1=$2', file_get_contents($historyFile));
      }

      return '';
   }

   /**
    * @return string The release number contained in the url, or a fallback number form the tag definition.
    */
   private function getReleaseNumber() {
      $release = $this->getRequest()->getParameter(ReleaseBaseController::$REV_HISTORY_PARAM);
      if ($release === null) {
         $release = $this->getAttribute(self::$FALLBACK_RELEASE_PARAM);
      }

      return $release;
   }

}
