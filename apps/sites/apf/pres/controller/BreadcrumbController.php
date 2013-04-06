<?php
namespace APF\sites\apf\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;
use APF\sites\apf\biz\UrlManager;

/**
 * @package APF\sites\apf\pres\controller
 * @class BreadcrumbController
 *
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

      $model = & Singleton::getInstance('APFModel');
      /* @var $model APFModel */
      $parent = $model->getParentPageId();
      $lang = $model->getLanguage();
      if ($parent !== '0') {
         /* @var $linkGen UrlManager */
         $linkGen = & $this->getServiceObject('APF\sites\apf\biz\UrlManager');
         $docuLink = $linkGen->generateLink($parent, $lang);
         $docuTitle = $linkGen->getPageTitle($parent, $lang);

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
