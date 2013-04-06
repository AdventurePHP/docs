<?php
namespace APF\sites\apf\pres\controller\external;

use APF\tools\filesystem\FilesystemManager;
use APF\sites\apf\pres\controller\release\ReleaseBaseController;

/**
 * @package APF\sites\apf\pres\controller\external
 * @class ApiDocumentationController
 *
 * Implements the document controller of the API documentation listing.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.01.2009<br />
 */
class ApiDocumentationController extends ReleaseBaseController {

   public function __construct() {
      parent::__construct();
   }

   /**
    * @public
    *
    * Displays the list of available API documentations.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.01.2009<br />
    */
   public function transformContent() {

      // get the desired release
      $releases = $this->getAllReleases();

      // pre-fill the template
      $tmpl = &$this->getTemplate('Release');
      $tmpl->setPlaceHolder('ReleaseURL', $this->releasesBaseURL);
      $tmpl_version = &$this->getTemplate('Version_' . $this->language);
      $tmpl->setPlaceHolder('Version', $tmpl_version->transformTemplate());

      $tmpl_linkname1 = &$this->getTemplate('LinkName1_' . $this->language);
      $tmpl->setPlaceHolder('LinkName1', $tmpl_linkname1->transformTemplate());
      $tmpl_linkname2 = &$this->getTemplate('LinkName2_' . $this->language);
      $tmpl->setPlaceHolder('LinkName2', $tmpl_linkname2->transformTemplate());
      $tmpl_linkname3 = &$this->getTemplate('LinkName3_' . $this->language);
      $tmpl->setPlaceHolder('LinkName3', $tmpl_linkname3->transformTemplate());

      $tmpl_linktitle = &$this->getTemplate('LinkTitle_' . $this->language);
      $tmpl->setPlaceHolder('LinkTitle', $tmpl_linktitle->transformTemplate());

      // prefill new template
      $tmpl_new = &$this->getTemplate('Release_1.10');
      $tmpl_new->setPlaceHolder('ReleaseURL', $this->releasesBaseURL);
      $tmpl_new->setPlaceHolder('Version', $tmpl_version->transformTemplate());

      $tmpl_linkname = &$this->getTemplate('LinkName_' . $this->language);
      $tmpl_new->setPlaceHolder('LinkName', $tmpl_linkname->transformTemplate());

      $tmpl_new->setPlaceHolder('LinkTitle', $tmpl_linktitle->transformTemplate());

      // display available API documentations
      $buffer = (string)'';

      for ($i = 0; $i < count($releases); $i++) {

         // gather version -------------------------------------------------------------------
         $dashOffset = strpos($releases[$i], '-');
         if ($dashOffset !== false) {
            $rawVersion = substr($releases[$i], 0, $dashOffset);
         } else {
            $rawVersion = $releases[$i];
         }
         $version = ReleaseBaseController::normalizeVersionNumber($rawVersion);
         // ----------------------------------------------------------------------------------

         if ($version >= 110) {
            $tmpl_new->setPlaceHolder('ReleaseName', $releases[$i]);
            $buffer .= $tmpl_new->transformTemplate();
         } else {
            $tmpl->setPlaceHolder('ReleaseName', $releases[$i]);
            $buffer .= $tmpl->transformTemplate();
         }

      }

      $this->setPlaceHolder('Releases', $buffer);

   }

}
