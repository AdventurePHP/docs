<?php
namespace DOCS\pres\controller\release;

use APF\core\benchmark\BenchmarkTimer;
use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use DOCS\biz\APFModel;
use DOCS\biz\UrlManager;
use APF\tools\filesystem\FilesystemManager;

/**
 * @package DOCS\pres\controller\release
 * @class ReleaseBaseController
 *
 * Implements basic functionality for the release pages.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 29.12.2009<br />
 */
abstract class ReleaseBaseController extends BaseDocumentController {

   private static $REV_HISTORY_PAGEID = '126';

   /**
    * @public
    * @var string The name of the param, that indicates the release version to show the
    * history of. It is public, because the rev:history tag uses this param, too.
    */
   public static $REV_HISTORY_PARAM = 'release';

   /**
    * @protected
    * @var string Defines, where the releases reside on the filesystem.
    */
   protected $releasesLocalDir = null;

   /**
    * @protected
    * @var string Defines the base URL, where the releases can be accessed via the HTTP protocol.
    */
   protected $releasesBaseURL = null;

   private static $PHP5_RELEASE_FILE_INDICATOR = '-php5';
   private static $NOARCH_RELEASE_FILE_INDICATOR = '-noarch';
   private static $CODE_RELEASE_FILE_INDICATOR = '-codepack';

   private static $SNAPSHOT_RELEASE_FOLDER_NAME = 'snapshot';

   public function __construct() {
      $this->releasesLocalDir = Registry::retrieve('DOCS', 'Releases.LocalDir');
      $this->releasesBaseURL = Registry::retrieve('DOCS', 'Releases.BaseURL');
   }

   /**
    * @protected
    *
    * Evaluates, whether a release is stable or not. All releases having
    * a dash are considered unstable.
    *
    * @param string $releaseNumber The release number to check.
    * @return bool True, in case the given release number is a stable release, false otherwise.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 29.12.2009<br />
    */
   protected function isStableRelease($releaseNumber) {
      if (preg_match('/\-(.*)/', $releaseNumber)) {
         return false;
      }
      return true;
   }

   /**
    * @protected
    *
    * Returns all available releases.
    *
    * @return string[] All available releases.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 29.12.2009<br />
    */
   protected function getAllReleases() {
      /* @var $t BenchmarkTimer */
      $t = & Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'ReleaseBaseController::getAllReleases()';
      $t->start($id);
      $rawReleases = array_reverse(FilesystemManager::getFolderContent($this->releasesLocalDir));

      // exclude snapshot release folder
      $releases = array();
      foreach ($rawReleases as $release) {
         if ($release != self::$SNAPSHOT_RELEASE_FOLDER_NAME) {
            $releases[] = $release;
         }
      }

      usort($releases, array('DOCS\pres\controller\release\ReleaseBaseController', 'sortReleases'));

      $t->stop($id);
      return $releases;
   }

   /**
    * @public
    * @static
    *
    * Implementation of the release file sort function.
    *
    * @param string $offsetOne Release 1 to compare with release 2.
    * @param string $offsetTwo Release 2 to compare with release 1.
    * @return int Comparison result.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 23.10.2007<br />
    * Version 0.2, 15.01.2008 (Update to the sort algorithm)<br />
    */
   public static function sortReleases($offsetOne, $offsetTwo) {

      $return = 0;

      if (substr_count($offsetOne, '-') > 0 && substr_count($offsetTwo, '-') > 0) {

         $dashPosOneValue = strpos($offsetOne, '-');
         $dashPosTwoValue = strpos($offsetTwo, '-');
         $offsetOneValue = ReleaseBaseController::normalizeVersionNumber(substr($offsetOne, 0, $dashPosOneValue));
         $offsetTwoValue = ReleaseBaseController::normalizeVersionNumber(substr($offsetTwo, 0, $dashPosTwoValue));

         if ($offsetOneValue == $offsetTwoValue) {

            $OffsetOneSecondValue = substr($offsetOne, $dashPosOneValue + 1);
            $OffsetTwoSecondValue = substr($offsetTwo, $dashPosTwoValue + 1);

            $IdenticalValuesArray = array();
            $IdenticalValuesArray[] = $OffsetOneSecondValue;
            $IdenticalValuesArray[] = $OffsetTwoSecondValue;
            natsort($IdenticalValuesArray);
            $IdenticalValuesArray = array_reverse($IdenticalValuesArray);

            if ($IdenticalValuesArray[1] === $OffsetOneSecondValue) {
               $return = 1;
            } else {
               $return = -1;
            }

         } else {
            $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
         }
      }

      if (substr_count($offsetOne, '-') > 0 && substr_count($offsetTwo, '-') == 0) {

         $dashPosOneValue = strpos($offsetOne, '-');
         $offsetOneValue = ReleaseBaseController::normalizeVersionNumber(substr($offsetOne, 0, $dashPosOneValue));
         $offsetTwoValue = ReleaseBaseController::normalizeVersionNumber($offsetTwo);

         if ($offsetOneValue == $offsetTwoValue) {

            $OffsetOneSecondValue = substr($offsetOne, $dashPosOneValue + 1);
            $OffsetTwoSecondValue = $offsetTwo;

            $IdenticalValuesArray = array();
            $IdenticalValuesArray[] = $OffsetOneSecondValue;
            $IdenticalValuesArray[] = $OffsetTwoSecondValue;
            natsort($IdenticalValuesArray);

            if ($IdenticalValuesArray[0] === $OffsetOneSecondValue) {
               $return = 1;
            } else {
               $return = -1;
            }

         } else {
            $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
         }
      }

      if (substr_count($offsetOne, '-') == 0 && substr_count($offsetTwo, '-') > 0) {

         $dashPosTwoValue = strpos($offsetTwo, '-');
         $offsetOneValue = ReleaseBaseController::normalizeVersionNumber($offsetOne);
         $offsetTwoValue = ReleaseBaseController::normalizeVersionNumber(substr($offsetTwo, 0, $dashPosTwoValue));

         if ($offsetOneValue == $offsetTwoValue) {
            $return = 0;
         } else {
            $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
         }
      }

      if (substr_count($offsetOne, '-') == 0 && substr_count($offsetTwo, '-') == 0) {

         $offsetOneValue = ReleaseBaseController::normalizeVersionNumber($offsetOne);
         $offsetTwoValue = ReleaseBaseController::normalizeVersionNumber($offsetTwo);

         if ($offsetOneValue == $offsetTwoValue) {
            $return = 0;
         } else {
            $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
         }
      }

      return $return;
   }

   /**
    * @private
    * @static
    *
    * Normalizes the version number to an integer.
    *
    * @param string $version The version number extracted by the folder.
    * @return int The normalized version number.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 29.12.2009<br />
    */
   public static function normalizeVersionNumber($version) {
      $version = preg_replace('/[A-Za-z\-]/', '', $version);
      $version = str_replace('.', '', $version);
      $length = strlen($version);
      if ($length < 3) {
         $version .= str_repeat('0', 3 - $length);
      }
      return (int)$version;
   }

   /**
    * @protected
    *
    * Displays one particular release.
    *
    * @param string $releaseNumber The number of the release to display.
    * @return string The HTML source for the given release.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 29.12.2009<br />
    */
   protected function displayRelease($releaseNumber) {

      // get templates
      $templateReleaseHead = & $this->getTemplate('ReleaseHead');
      $templateReleaseFile = & $this->getTemplate('ReleaseFile');

      // ----------------------------------------------------------------------------------------

      // fill release number
      $templateReleaseHead->setPlaceHolder('ReleaseNumber', $releaseNumber);

      // fetch files
      $files = FilesystemManager::getFolderContent($this->releasesLocalDir . '/' . $releaseNumber . '/download');

      // sort files
      sort($files);

      // new docu page: filter files, to be able to decide, whether to display PHP4 and/or PHP5
      // release files
      $files = $this->filterFiles($files);

      // fill offline documentation
      $docsFolder = 'docs';
      $dokuFiles = FilesystemManager::getFolderContent($this->releasesLocalDir . '/' . $releaseNumber . '/' . $docsFolder);

      // choose new template for versions > 1.10
      $templateOfflineDoku = & $this->getTemplate('OfflineDoku');

      $templateOfflineDoku->setPlaceHolder('ReleaseVersion', $releaseNumber);
      $bufferOfflineDoku = (string)'';

      for ($k = 0; $k < count($dokuFiles); $k++) {

         if (!is_dir($this->releasesLocalDir . '/' . $releaseNumber . '/' . $docsFolder . '/' . $dokuFiles[$k])) {

            // extract build date
            preg_match('/-([0-9]{2}\.[0-9]{2}\.[0-9]{4})-/', $dokuFiles[$k], $matches);
            if (isset($matches[1])) {
               $buildDate = $matches[1];
            } else {

               preg_match('/-([0-9]{4}-[0-9]{2}-[0-9]{2})-/', $dokuFiles[$k], $matches);
               if (isset($matches[1])) {
                  $buildDate = date('d.m.Y', strtotime($matches[1]));
               } else {

                  if ($this->language == 'de') {
                     $buildDate = 'unbekannt';
                  } else {
                     $buildDate = 'unknown';
                  }

               }

            }

            $templateOfflineDoku->setPlaceHolder('BuildDate', $buildDate);
            $templateOfflineDoku->setPlaceHolder('DokuFileFull', $dokuFiles[$k]);

            $templateOfflineDoku->setPlaceHolder('DokuFile', $this->getDisplayFileName($dokuFiles[$k]));

            $templateOfflineDoku->setPlaceHolder('ReleasesBaseURL', $this->releasesBaseURL);
            $bufferOfflineDoku .= $templateOfflineDoku->transformTemplate();

         }

      }

      // -- check version to be greater than 1.10, than display only one online api doku
      $templateDocumentation = & $this->getTemplate('Documentation');
      $templateDocumentation->setPlaceHolder('DocsFolder', $docsFolder);

      $templateDocumentation->setPlaceHolder('ReleaseVersion', $releaseNumber);
      $templateDocumentation->setPlaceHolder('OfflineDoku', $bufferOfflineDoku);
      $templateDocumentation->setPlaceHolder('ReleasesBaseURL', $this->releasesBaseURL);

      // Generate change-set link. This is a link on the change-set page with the current
      // release as it's param.
      $config = $this->getConfiguration('DOCS\pres', 'labels.ini');
      $title = $config->getSection($this->getLanguage())->getValue('downloads.changeset.text.linktext');
      $title .= $releaseNumber;

      /* @var $urlMan UrlManager */
      $urlMan = & $this->getServiceObject('DOCS\biz\UrlManager');

      /* @var $model APFModel */
      $model = & Singleton::getInstance('DOCS\biz\APFModel');
      $link = $urlMan->generateLink(self::$REV_HISTORY_PAGEID, $this->language, $model->getDefaultVersionId());
      $templateDocumentation->setPlaceHolder(
         'HistoryLink',
            '<a href="' . $link . '?' . self::$REV_HISTORY_PARAM . '=' . $releaseNumber . '" title="' . $title . '">' . $title . '</a>'
      );

      $templateReleaseHead->setPlaceHolder('Documentation', $templateDocumentation->transformTemplate());


      // display release files
      $bufferFiles = (string)'';

      for ($j = 0; $j < count($files); $j++) {

         if (!is_link($this->releasesLocalDir . '/' . $releaseNumber . '/download/' . $files[$j]) && !is_dir($this->releasesLocalDir . '/' . $releaseNumber . '/download/' . $files[$j])) {

            // gather file attributes
            $fileAttributes = FilesystemManager::getFileAttributes($this->releasesLocalDir . '/' . $releaseNumber . '/download/' . $files[$j]);

            // fill template
            $templateReleaseFile->setPlaceHolder('Link', $this->releasesBaseURL . '/' . $releaseNumber . '/download/' . $files[$j]);
            $templateReleaseFile->setPlaceHolder('Name', $this->getDisplayFileName($files[$j]));
            $templateReleaseFile->setPlaceHolder('Date', $fileAttributes['modificationdate']);
            $templateReleaseFile->setPlaceHolder('Size', round((int)$fileAttributes['size'] / 1000, 1));
            $templateReleaseFile->setPlaceHolder('Type', $this->getExtensionDisplayText($fileAttributes['extension']));

            // mark codepack releases with a special class
            $templateReleaseFile->setPlaceHolder('Class', '');
            if (substr_count($files[$j], self::$CODE_RELEASE_FILE_INDICATOR) > 0) {
               $templateReleaseFile->setPlaceHolder('Class', 'codepack');
            }

            // add file to files buffer
            $bufferFiles .= $templateReleaseFile->transformTemplate();

         }

      }

      // generate file list
      $templateReleaseHead->setPlaceHolder('ReleaseFiles', $bufferFiles);

      // generate whole release block
      return $templateReleaseHead->transformTemplate();

   }

   /**
    * @private
    *
    * Maps the file extension to a display text.
    *
    * @param string $ext The file extension.
    * @return string The human readable extension label.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    */
   private function getExtensionDisplayText($ext) {
      $displayName = (string)'';
      switch ($ext) {
         case 'zip':
            $displayName = 'zip';
            break;
         case 'gz':
            $displayName = 'gzip';
            break;
         case 'bz2':
            $displayName = 'bzip2';
            break;
      }
      return $displayName;
   }

   /**
    * @private
    *
    * Filters the given list of files to contain only the desired branch files.
    *
    * @param string[] $files The full file list.
    * @return string[] The filtered file list.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    */
   private function filterFiles($files) {
      $filteredList = array();

      foreach ($files as $file) {

         // allow noarch files
         if (substr_count($file, self::$NOARCH_RELEASE_FILE_INDICATOR) > 0) {
            $filteredList[] = $file;
            continue;
         }

         // allow PHP5 files
         if (substr_count($file, self::$PHP5_RELEASE_FILE_INDICATOR) > 0) {
            $filteredList[] = $file;
            continue;
         }
      }

      return $filteredList;

   }

   /**
    * @private
    *
    * Shortens the display file name removing the date signature.
    *
    * @param string $fileName The real file name.
    * @return string The display file name.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    */
   private function getDisplayFileName($fileName) {
      return preg_replace('/\-([0-9]{4})-([0-9]{2})-([0-9]{2})-([0-9]{4})/', '', $fileName);
   }

   /**
    * @private
    *
    * Wraps the content of the releases with a div, that is used to
    * style the tables.
    *
    * @param string $content The release file content (table of files, docs, etc).
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    */
   protected function setContentPlaceHolder($content) {
      $this->setPlaceHolder(
         'Content',
            '<div id="DownloadFiles">'
            . PHP_EOL
            . $content
            . PHP_EOL
            . '</div>'
      );
   }

}
