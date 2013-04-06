<?php
namespace APF\sites\apf\pres\controller\quicknavi;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;

/**
 * @package sites::apf::pres::controller::quicknavi
 * @class SocialBookmarkBarController
 *
 * Fills the dynamic content of the social network bar.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 28.12.2009<br />
 */
class SocialBookmarkBarController extends BaseDocumentController {

   public function transformContent() {
      $currentUrl = Registry::retrieve('apf::core', 'CurrentRequestURL');
      $this->setPlaceHolder('currentUrl', $currentUrl);
   }

}
