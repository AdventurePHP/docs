<?php
import('sites::apf::pres::controller::release', 'release_base_controller');

/**
 * @package sites::apf::pres::controller::release
 * @class releases_controller
 *
 * Implements the document controller for the releases view.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 16.08.2007<br />
 * Version 0.2, 19.09.2008 (Refactoring for the new documentation page)<br />
 */
class all_stable_releases_controller extends release_base_controller {

   private static $TYPE = 'type';
   private static $PHP4 = 'php4';
   private static $PHP5 = 'php5';

   public function __construct() {
      parent::__construct();
   }

   /**
    * @public
    *
    * Displays the releases available in the release folder. The directory structure is
    * /<release_name>/[downloads|docs]/. The downloads folder contains all files, the docs folder
    * includes the online and offline documentation.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 16.08.2007<br />
    * Version 0.2, 18.08.2007 (Release files are sorted by name now)<br />
    * Version 0.3, 19.09.2008 (Refactoring for the new documentation page)<br />
    * Version 0.4, 29.12.2009 (Displays only stable versions now)<br />
    */
   public function transformContent() {

      // resolve the packages to display using the attributes of the document
      $type = $this->getAttribute(self::$TYPE);
      if ($type === self::$PHP5) {
         $this->displayPHP4Files = false;
         $this->displayPHP5Files = true;
      }
      if ($type === self::$PHP4) {
         $this->displayPHP4Files = true;
         $this->displayPHP5Files = false;
      }

      // initialize output buffer
      $bufferReleases = (string)'';

      // read releases
      $releases = $this->getAllReleases();

      // display only stable releases
      if (count($releases) > 0) {
         for ($i = 0; $i < count($releases); $i++) {
            if ($this->isStableRelease($releases[$i])) {
               $bufferReleases .= $this->displayRelease($releases[$i]);
            }
         }
      }

      // display content
      $this->setContentPlaceHolder($bufferReleases);

   }

}
