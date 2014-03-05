<?php
namespace DEV\wizard\controller;

use APF\core\pagecontroller\BaseDocumentController;

class IndexController extends BaseDocumentController {

   public function transformContent() {

      $form = & $this->getForm('index-adaption');

      if ($form->isSent() && $form->isValid()) {

         $control = & $form->getFormElementByName('rel-folder');
         $folder = $control->getValue();

         $fileName = 'index.php';
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

         // 4) Create release folder stubs
         $docsFolder = $folder . DIRECTORY_SEPARATOR . '2.X/docs';
         if (!file_exists($docsFolder)) {
            mkdir($docsFolder, 0777, true);
         }

         $downloadsFolder = $folder . DIRECTORY_SEPARATOR . '2.X/downloads';
         if (!file_exists($downloadsFolder)) {
            mkdir($downloadsFolder, 0777, true);
         }

         // 5) Adapt Registry::register('APF\sites\apf', 'Releases.LocalDir', 'C:/Users/Christian/Entwicklung/Build/RELEASES');
         $content = preg_replace(
            '/^Registry::register\(\'APF\\\\sites\\\\apf\', \'Releases\.LocalDir\', \'(.+)\'\);$/m',
               'Registry::register(\'APF\sites\apf\', \'Releases.LocalDir\', \'' . $folder . '\');',
            $content
         );

         file_put_contents($fileName, $content);
         $this->getTemplate('success')->transformOnPlace();

      } else {
         $form->transformOnPlace();
      }
   }

} 