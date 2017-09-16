<?php
namespace DEV\wizard\controller;

use APF\core\pagecontroller\BaseDocumentController;

class IndexController extends BaseDocumentController {

   public function transformContent() {

      $form = &$this->getForm('index-adaption');

      if ($form->isSent() && $form->isValid()) {

         $fileName = './www/index.php';
         $content = file_get_contents($fileName);

         // 1) Disable HttpCacheManager::sendHtmlCacheHeaders();
         $content = preg_replace(
               '/^HttpCacheManager::sendHtmlCacheHeaders\(\);$/m',
               '//HttpCacheManager::sendHtmlCacheHeaders();',
               $content
         );

         // 2) Disable GlobalErrorHandler::registerErrorHandler(new LiveErrorHandler());
         $content = preg_replace(
               '/^GlobalErrorHandler::registerErrorHandler\(new LiveErrorHandler\(\)\);$/m',
               '//GlobalErrorHandler::registerErrorHandler(new LiveErrorHandler());',
               $content
         );

         // 3) Disable GlobalExceptionHandler::registerExceptionHandler(new LiveExceptionHandler());
         $content = preg_replace(
               '/^GlobalExceptionHandler::registerExceptionHandler\(new LiveExceptionHandler\(\)\);$/m',
               '//GlobalExceptionHandler::registerExceptionHandler(new LiveExceptionHandler());',
               $content
         );

         file_put_contents($fileName, $content);
         $this->getTemplate('success')->transformOnPlace();

      } else {
         $form->transformOnPlace();
      }
   }

}
