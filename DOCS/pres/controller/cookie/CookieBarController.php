<?php
namespace DOCS\pres\controller\cookie;

use APF\core\pagecontroller\BaseDocumentController;

/**
 * Handles the cookie bar display logic.
 */
class CookieBarController extends BaseDocumentController {

   public function transformContent() {

      $cookieBar = $this->getRequest()->getCookie('CookieBar');

      // display cookie bar for new visitors, hide when accepted (=cookie set)
      if ($cookieBar === null) {
         $template = $this->getTemplate('cookie-bar');
         $template->getTemplate('content-' . $this->getLanguage())->transformOnPlace();
         $template->transformOnPlace();
      }

   }

}
