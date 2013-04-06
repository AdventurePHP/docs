<?php
namespace APF\sites\apf\biz\actions\changelang;

use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use APF\sites\apf\biz\UrlManager;
use APF\tools\request\RequestHandler;
use APF\tools\http\HeaderManager;

/**
 * @package sites::apf::biz::actions::changelang
 * @class ChangeLangAction
 *
 * Represents the front controller action to change the site's language.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 15.12.2009<br />
 */
class ChangeLangAction extends AbstractFrontcontrollerAction {

   private static $LANG = 'lang';
   private static $PAGE_ID = 'pageid';

   /**
    * @public
    *
    * Changes the language by redirecting to the appropriate url.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 15.12.2009<br />
    */
   public function run() {

      // clean inout parameters to avoid sql injection!
      $targetLang = preg_replace('/([^en|^de])/i', '', RequestHandler::getValue(self::$LANG));
      $targetPageId = preg_replace('/([^0-9]+)/', '', RequestHandler::getValue(self::$PAGE_ID));

      /* @var $urlMan UrlManager */
      $urlMan = & $this->getServiceObject('sites::apf::biz', 'UrlManager');
      $forwardUrl = $urlMan->generateLink($targetPageId, $targetLang);

      HeaderManager::forward($forwardUrl);

   }

}
