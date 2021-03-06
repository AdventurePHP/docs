<?php
namespace DOCS\pres\controller\quicknavi;

use APF\core\pagecontroller\BaseDocumentController;
use APF\tools\link\LinkGenerator;
use APF\tools\link\Url;

/**
 * Fills the dynamic content of the social network bar.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 28.12.2009<br />
 */
class SocialBookmarkBarController extends BaseDocumentController {

   public function transformContent() {
      $this->setPlaceHolder('currentUrl', LinkGenerator::generateUrl(Url::fromCurrent(true)));
   }

}
