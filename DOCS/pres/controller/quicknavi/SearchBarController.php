<?php
namespace DOCS\pres\controller\quicknavi;

use APF\core\pagecontroller\BaseDocumentController;

class SearchBarController extends BaseDocumentController {
   public function transformContent() {

      if ($this->language === 'de') {
         $this->setPlaceHolder('searchUrl', '/Seite/044-Suche');
      } else {
         $this->setPlaceHolder('searchUrl', '/Page/044-Search');
      }

   }

}
