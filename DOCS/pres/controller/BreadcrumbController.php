<?php
namespace DOCS\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\singleton\Singleton;
use DOCS\biz\APFModel;
use DOCS\biz\UrlManager;

/**
 * Document controller that displays the breadcrumb when navigating
 * to the second stage.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 18.12.2009<br />
 * Version 0.2, 22.12.2009 (Introduced link generating facility)<br />
 */
class BreadcrumbController extends BaseDocumentController {

   public function transformContent() {

      $model = Singleton::getInstance(APFModel::class);
      /* @var $model APFModel */
      $parent = $model->getParentPageId();
      $lang = $model->getLanguage();
      $version = $model->getVersionId();
      if ($parent !== '0') {
         /* @var $linkGen UrlManager */
         $linkGen = $this->getServiceObject(UrlManager::class);
         $docuLink = $linkGen->generateLink($parent, $lang, $version);
         $docuTitle = $linkGen->getPageTitle($parent, $lang, $version);

         $breadcrumb = $this->getTemplate('breadcrumb');
         $breadcrumb->setPlaceHolder('title', $model->getTitle());
         $breadcrumb->setPlaceHolder(
               'mainpage',
               '<a href="' . $docuLink . '" title="' . $docuTitle . '">' . $docuTitle . '</a>'
         );
         $breadcrumb->transformOnPlace();
      }

   }

}
