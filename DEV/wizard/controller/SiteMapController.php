<?php
namespace DEV\wizard\controller;

use APF\core\pagecontroller\BaseDocumentController;
use DOCS\data\sitemap\XmlSiteMapCreator;
use Exception;

class SiteMapController extends BaseDocumentController {

   public function transformContent() {

      $form = $this->getForm('site-map-maintenance');

      if ($form->isSent()) {

         try {
            $this->updateSiteMap();
         } catch (Exception $e) {
            $tmpl = $this->getTemplate('error');
            $tmpl->setPlaceHolder('exception-message', $e->getMessage());
            $tmpl->transformOnPlace();

            return;
         }

         $this->getTemplate('success')->transformOnPlace();

      } else {
         $form->transformOnPlace();
      }

   }

   private function updateSiteMap() {

      // configure some php params
      ini_set('html_errors', 'off');
      error_reporting(E_ALL);
      ini_set('display_errors', 'On');
      set_time_limit(0);
      ini_set('memory_limit', '300M');

      $siteMap = new XmlSiteMapCreator();
      $siteMap->setContext('dummy');
      $siteMap->setLanguage('de');

      $fH = fopen('sitemap.xml', 'w+');
      fwrite($fH, $siteMap->createSitemap());
      fclose($fH);
   }

}
