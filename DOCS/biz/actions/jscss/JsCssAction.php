<?php
namespace DOCS\biz\actions\jscss;

use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use DOCS\pres\http\HttpCacheManager;

/**
 * Delivers compressed versions of js and css files.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 15.12.2009<br />
 * Version 0.2, 23.12.2009 (Introduced js file delivery)<br />
 */
class JsCssAction extends AbstractFrontcontrollerAction {

   /**
    * @public
    *
    * Streams the desired js or css file.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 15.12.2009<br />
    */
   public function run() {

      /* @var $input JsCssInput */
      $input = $this->getInput();

      $fileName = $input->getFileName();

      if ($input->isCssFileRequested()) {
         HttpCacheManager::sendCssCacheHeaders();
      } else {
         HttpCacheManager::sendJsCacheHeaders();
      }
      $response = self::getResponse();
      $response->setBody(file_get_contents($fileName));
      $response->send();
   }

}
