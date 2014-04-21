<?php
namespace DOCS\pres\controller\release;

/**
 * @package DOCS\pres\controller\release
 * @class CurrentReleaseController
 *
 * Displays the current release (stable) package.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 28.12.2009<br />
 */
class CurrentReleaseController extends ReleaseBaseController {

   public function __construct() {
      parent::__construct();
   }

   public function transformContent() {

      // initialize output buffer
      $bufferReleases = (string)'';

      // read releases
      $releases = $this->getAllReleases();

      // display only stable releases
      if (count($releases) > 0) {
         for ($i = 0; $i < count($releases); $i++) {
            if ($this->isStableRelease($releases[$i])) {
               $bufferReleases .= $this->displayRelease($releases[$i]);
               break;
            }
         }
      }

      // display content
      $this->setContentPlaceHolder($bufferReleases);

   }

}
