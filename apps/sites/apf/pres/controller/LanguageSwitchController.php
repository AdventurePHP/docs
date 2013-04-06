<?php
namespace APF\sites\apf\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;
use APF\sites\apf\biz\UrlManager;

/**
 * Displays the langswitch for the page. Includes progressive enhancement
 * to have a select box using js on the page and a fallback with plain links
 * enabling search engines to index the files properly.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 22.12.2009<br />
 */
class LanguageSwitchController extends BaseDocumentController {

   public function transformContent() {

      /* @var $model APFModel */
      $model = &Singleton::getInstance('APFModel');
      $lang = $model->getLanguage();
      $pageId = $model->getPageId();

      /* @var $urlMan UrlManager */
      $urlMan = &$this->getServiceObject('APF\sites\apf\biz\UrlManager');
      $linkDe = $urlMan->generateLink($pageId, 'de');
      $linkEn = $urlMan->generateLink($pageId, 'en');

      $nameDe = $urlMan->getPageTitle($pageId, 'de');
      $nameEn = $urlMan->getPageTitle($pageId, 'en');

      $this->setPlaceHolder('link_de', $linkDe);
      $this->setPlaceHolder('link_en', $linkEn);

      $this->setPlaceHolder('name_de', $nameDe);
      $this->setPlaceHolder('name_en', $nameEn);

      // add css class to be able to do progressive enhancement
      // using js code by janek
      if ($lang == 'de') {
         $this->setPlaceHolder('class_de', 'current de');
      } else {
         $this->setPlaceHolder('class_de', 'de');
      }
      if ($lang == 'en') {
         $this->setPlaceHolder('class_en', 'current en');
      } else {
         $this->setPlaceHolder('class_en', 'en');
      }

   }

}