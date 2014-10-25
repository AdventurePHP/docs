<?php
namespace DOCS\pres\controller\release;

/**
 * Implements the document controller for the current unstable release view.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 29.12.2009<br />
 */
class CurrentUnstableController extends ReleaseBaseController {

   public function transformContent() {

      // initialize output buffer
      $bufferReleases = (string)'';

      // read releases
      $releases = $this->getAllReleases();

      // display only stable releases
      if (count($releases) > 0) {
         for ($i = 0; $i < count($releases); $i++) {
            if (!$this->isStableRelease($releases[$i])) {
               $bufferReleases .= $this->displayRelease($releases[$i]);
               break;
            }
         }
      }

      // display content
      $this->setContentPlaceHolder($bufferReleases);

   }

}
