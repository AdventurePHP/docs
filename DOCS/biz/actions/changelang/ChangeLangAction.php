<?php
namespace DOCS\biz\actions\changelang;

use APF\core\frontcontroller\AbstractFrontControllerAction;
use DOCS\biz\UrlManager;

/**
 * Represents the front controller action to change the site's language.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 15.12.2009<br />
 */
class ChangeLangAction extends AbstractFrontControllerAction {

   private static $LANG = 'lang';
   private static $PAGE_ID = 'page-id';
   private static $VERSION_ID = 'version-id';

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

      // clean input parameters to avoid sql injection!
      $request = $this->getRequest();
      $targetLang = preg_replace('/([^en|^de])/i', '', $request->getParameter(self::$LANG));
      $targetPageId = preg_replace('/([^0-9]+)/', '', $request->getParameter(self::$PAGE_ID));
      $targetVersion = $request->getParameter(self::$VERSION_ID);
      if ($targetVersion !== null) {
         $targetVersion = preg_replace('/([^0-9A-Za-z\.]+)/', '', $targetVersion);
      }

      /* @var $urlMan UrlManager */
      $urlMan = $this->getServiceObject(UrlManager::class);
      $forwardUrl = $urlMan->generateLink($targetPageId, $targetLang, $targetVersion);

      $this->getResponse()->forward($forwardUrl);
   }

}
