<?php
namespace DOCS\biz\actions\jscss;

use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use APF\core\http\HeaderImpl;
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
      $request = self::getRequest();

      if ($request->isGzipSupported()) {
         $content = gzencode(file_get_contents($fileName), 9);
         $response->setBody($content);
         $response->setHeader(new HeaderImpl('Content-Encoding', 'gzip'));
         $response->setHeader(new HeaderImpl('Content-Length', strlen($content)));
      } elseif ($request->isDeflateSupported()) {
         $content = gzdeflate(file_get_contents($fileName), 9);
         $response->setBody($content);
         $response->setHeader(new HeaderImpl('Content-Encoding', 'deflate'));
         $response->setHeader(new HeaderImpl('Content-Length', strlen($content)));
      } else {
         $content = file_get_contents($fileName);
         $response->setBody($content);
         $response->setHeader(new HeaderImpl('Content-Length', strlen($content)));
      }

      $response->send();
   }

}
