<?php
import('tools::filesystem', 'FilesystemManager');

/**
 * @package sites::apf::pres::controller::release
 * @class release_base_controller
 *
 * Implements basic functionality for the release pages.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 29.12.2009<br />
 */
abstract class release_base_controller extends BaseDocumentController {

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

   /**
    * @var boolean Indicates, whether to display the PHP4 files of the release.
    */
   protected $displayPHP4Files = false;

   /**
    * @var boolean Indicates, whether to display the PHP5 files of the release.
    */
   protected $displayPHP5Files = true;

   private static $PHP4_RELEASE_FILE_INDICATOR = '-php4';
   private static $PHP5_RELEASE_FILE_INDICATOR = '-php5';
   private static $NOARCH_RELEASE_FILE_INDICATOR = '-noarch';
   private static $PERFCODE_RELEASE_FILE_INDICATOR = '-perfcodepack';
   private static $CODE_RELEASE_FILE_INDICATOR = '-codepack';

   private static $SNAPSHOT_RELEASE_FOLDER_NAME = 'snapshot';

   public function __construct() {
      $this->releasesLocalDir = Registry::retrieve('sites::apf', 'Releases.LocalDir');
      $this->releasesBaseURL = Registry::retrieve('sites::apf', 'Releases.BaseURL');
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
      $t = & Singleton::getInstance('BenchmarkTimer');
      $id = 'release_base_controller::getAllReleases()';
      $t->start($id);
      $rawReleases = array_reverse(FilesystemManager::getFolderContent($this->releasesLocalDir));

      // exclude snapshot release folder
      $releases = array();
      foreach ($rawReleases as $release) {
         if ($release != self::$SNAPSHOT_RELEASE_FOLDER_NAME) {
            $releases[] = $release;
         }
      }

      usort($releases, array('release_base_controller', 'sortReleases'));

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

      //echo '<br />"'.$OffsetOne.'" | "'.$OffsetTwo.'" | Ergebnis:';

      if (substr_count($offsetOne, '-') > 0 && substr_count($offsetTwo, '-') > 0) {

         $dashPosOneValue = strpos($offsetOne, '-');
         $dashPosTwoValue = strpos($offsetTwo, '-');
         $offsetOneValue = release_base_controller::normalizeVersionNumber(substr($offsetOne, 0, $dashPosOneValue));
         $offsetTwoValue = release_base_controller::normalizeVersionNumber(substr($offsetTwo, 0, $dashPosTwoValue));

         if ($offsetOneValue == $offsetTwoValue) {

            /*echo */
            $OffsetOneSecondValue = substr($offsetOne, $dashPosOneValue + 1);
            /*echo ':'.*/
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
         $offsetOneValue = release_base_controller::normalizeVersionNumber(substr($offsetOne, 0, $dashPosOneValue));
         $offsetTwoValue = release_base_controller::normalizeVersionNumber($offsetTwo);

         if ($offsetOneValue == $offsetTwoValue) {

            /*echo */
            $OffsetOneSecondValue = substr($offsetOne, $dashPosOneValue + 1);
            /*echo ':'.*/
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
         $offsetOneValue = release_base_controller::normalizeVersionNumber($offsetOne);
         $offsetTwoValue = release_base_controller::normalizeVersionNumber(substr($offsetTwo, 0, $dashPosTwoValue));

         if ($offsetOneValue == $offsetTwoValue) {
            $return = 0;
         } else {
            $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
         }
      }

      if (substr_count($offsetOne, '-') == 0 && substr_count($offsetTwo, '-') == 0) {

         $offsetOneValue = release_base_controller::normalizeVersionNumber($offsetOne);
         $offsetTwoValue = release_base_controller::normalizeVersionNumber($offsetTwo);

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
      $version = str_replace('.', '', $version);
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

      // gather version -------------------------------------------------------------------------
      $dashOffset = strpos($releaseNumber, '-');
      if ($dashOffset !== false) {
         $rawVersion = substr($releaseNumber, 0, $dashOffset);
      } else {
         $rawVersion = $releaseNumber;
      }
      $version = release_base_controller::normalizeVersionNumber($rawVersion);
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
      //echo '<br />$version: '.$version.', raw: '.$rawVersion.', $releaseNumber: '.$releaseNumber;
      if ($version > 110) {
         $docsFolder = 'docs';
      } elseif ($version == 110 && substr_count($releaseNumber, 'RC') == 0) {
         $docsFolder = 'docs';
      } elseif ($version == 110 && substr_count($releaseNumber, 'RC') > 0) {
         $docsFolder = 'doku';
      } else {
         $docsFolder = 'doku';
      }
      $dokuFiles = FilesystemManager::getFolderContent($this->releasesLocalDir . '/' . $releaseNumber . '/' . $docsFolder);

      // choose new template for versions > 1.10
      if ($version >= 110) {
         $templateOfflineDoku = & $this->getTemplate('OfflineDoku_110');
      } else {
         $templateOfflineDoku = & $this->getTemplate('OfflineDoku');
      }

      $templateOfflineDoku->setPlaceHolder('ReleaseVersion', $releaseNumber);
      $bufferOfflineDoku = (string)'';

      for ($k = 0; $k < count($dokuFiles); $k++) {

         if (!is_dir($this->releasesLocalDir . '/' . $releaseNumber . '/' . $docsFolder . '/' . $dokuFiles[$k])) {

            if ($version >= 110) {

               if ($this->language == 'de') {
                  $libType = 'Gepackte HTML-Seiten';
                  $dokuType = 'Komplette Dokumentation';
               } else {
                  $libType = 'Packed html files';
                  $dokuType = 'Complete docs';
               }

            } else {

               // gather file type
               switch (substr($dokuFiles[$k], strrpos($dokuFiles[$k], '.') + 1)) {

                  case 'chm':
                     $libType = 'chm';
                     break;
                  default:
                     $libType = 'html + zip';
                     break;

               }

               // gather docu type
               if (substr_count($dokuFiles[$k], '-core-') > 0) {

                  if ($this->language == 'de') {
                     $dokuType = 'Core';
                  } else {
                     $dokuType = 'core';
                  }

               } elseif (substr_count($dokuFiles[$k], '-modules-') > 0) {

                  if ($this->language == 'de') {
                     $dokuType = 'Modules';
                  } else {
                     $dokuType = 'module';
                  }

               } else {

                  if ($this->language == 'de') {
                     $dokuType = 'Tools';
                  } else {
                     $dokuType = 'tool';
                  }

               }

            }

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

            if ($version < 110) {
               $templateOfflineDoku->setPlaceHolder('LibType', $libType);
               $templateOfflineDoku->setPlaceHolder('DokuType', $dokuType);
            }
            $templateOfflineDoku->setPlaceHolder('BuildDate', $buildDate);
            $templateOfflineDoku->setPlaceHolder('DokuFileFull', $dokuFiles[$k]);

            if ($version >= 110) {
               $templateOfflineDoku->setPlaceHolder('DokuFile', $this->getDisplayFileName($dokuFiles[$k]));
            }

            $templateOfflineDoku->setPlaceHolder('ReleasesBaseURL', $this->releasesBaseURL);
            $bufferOfflineDoku .= $templateOfflineDoku->transformTemplate();

         }

      }

      // -- check version to be greater than 1.10, than display only one online api doku
      if ($version >= 110) {
         $templateDocumentation = & $this->getTemplate('Documentation_new');
         $templateDocumentation->setPlaceHolder('DocsFolder', $docsFolder);
      } else {
         $templateDocumentation = & $this->getTemplate('Documentation');
      }
      $templateDocumentation->setPlaceHolder('ReleaseVersion', $releaseNumber);
      $templateDocumentation->setPlaceHolder('OfflineDoku', $bufferOfflineDoku);
      $templateDocumentation->setPlaceHolder('ReleasesBaseURL', $this->releasesBaseURL);

      // Generate changeset link. This is a link on the changeset page with the current
      // release as it's param.
      $config = $this->getConfiguration('sites::apf::pres', 'labels.ini');
      $title = $config->getSection($this->getLanguage())->getValue('downloads.changeset.text.linktext');
      $title .= $releaseNumber;

      /* @var $urlMan UrlManager */
      $urlMan = & $this->getServiceObject('sites::apf::biz', 'UrlManager');
      $link = $urlMan->generateLink(self::$REV_HISTORY_PAGEID, $this->language);
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
            //echo printObject($FileAttributes);

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
    * In order to decide, which branch to display, adjust the
    * <ul>
    * <li>$this->displayPHP4Files</li>
    * <li>$this->displayPHP5Files</li>
    * </ul>
    * properties within the derived classes.
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
         //echo '<br />'.$file;

         // disallow perfcode packages
         if (substr_count($file, self::$PERFCODE_RELEASE_FILE_INDICATOR) > 0) {
            continue;
         }

         // allow noarch files
         if (substr_count($file, self::$NOARCH_RELEASE_FILE_INDICATOR) > 0) {
            //echo ' -> yes';
            $filteredList[] = $file;
            continue;
         }

         // allow PHP4 files
         if (substr_count($file, self::$PHP4_RELEASE_FILE_INDICATOR) > 0 && $this->displayPHP4Files === true) {
            //echo ' -> yes';
            $filteredList[] = $file;
            continue;
         }

         // allow PHP5 files
         if (substr_count($file, self::$PHP5_RELEASE_FILE_INDICATOR) > 0 && $this->displayPHP5Files === true) {
            //echo ' -> yes';
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
    * Wrapps the content of the releases with a div, that is used to
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
