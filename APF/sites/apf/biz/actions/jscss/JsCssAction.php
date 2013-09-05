<?php
namespace APF\sites\apf\biz\actions\jscss;

use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use APF\sites\apf\pres\http\HttpCacheManager;

/**
 * @package APF\sites\apf\biz\actions\jscss
 * @class JsCssAction
 *
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
      ob_start();

      if ($input->isCssFileRequested()) {
         HttpCacheManager::sendCssCacheHeaders();
      } else {
         HttpCacheManager::sendJsCacheHeaders();
      }
      echo file_get_contents($fileName);
      ob_end_flush();
      exit(0);
   }

}
