<?php
namespace APF\sites\apf\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;
use APF\sites\apf\biz\UrlManager;

class TopNavigationController extends BaseDocumentController {

   /**
    * @var string[] main navigation definition.
    */
   private $navi = array();
   private static $DOC_PAGE_ID = '072';

   public function __construct() {
      $this->navi[] = '001';
      $this->navi[] = '008';
      $this->navi[] = self::$DOC_PAGE_ID;
      $this->navi[] = '118';
      $this->navi[] = '073';
   }

   public function transformContent() {

      /* @var $model APFModel */
      $model = Singleton::getInstance('APF\sites\apf\biz\APFModel');
      $pageId = $model->getPageId();

      // by default, the documentation tab is active for all other sites!
      if (!$this->isTopLevelNavi($pageId)) {
         $pageId = $this->getParentNodeId();
      }

      /* @var $urlMan UrlManager */
      $urlMan = & $this->getServiceObject('APF\sites\apf\biz\UrlManager');

      $buffer = (string)'';
      foreach ($this->navi as $naviNode) {
         $buffer .= '<li>';
         $title = $urlMan->getPageTitle($naviNode, $this->language);
         if ($pageId === $naviNode) {
            $buffer .= '<span>' . $title . '</span>';
         } else {
            $link = $urlMan->generateLink($naviNode, $this->language);
            $buffer .= '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
         }
         $buffer .= '</li>' . PHP_EOL;
      }

      $this->setPlaceHolder('menu', $buffer);
   }

   /**
    * @private
    *
    * Checks, whether the current page is a top level navi node.
    *
    * @param string $pageId The current page id.
    * @return bool true, in case it is, false otherwise.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1 ,15.12.2009<br />
    */
   private function isTopLevelNavi($pageId) {
      foreach ($this->navi as $naviNode) {
         if ($pageId === $naviNode) {
            return true;
         }
      }
      return false;
   }

   /**
    * @return string The parent page's id.
    */
   private function getParentNodeId() {
      /* @var $model APFModel */
      $model = Singleton::getInstance('APF\sites\apf\biz\APFModel');
      $parentPageId = $model->getParentPageId();
      if ($parentPageId == '0') {
         return self::$DOC_PAGE_ID;
      }
      // special case: pages beyond the "component documentation" must have
      // the component page as parent, but the "documentation" must be active!
      if (!in_array($parentPageId, $this->navi)) {
         return self::$DOC_PAGE_ID;
      }
      return $parentPageId;
   }

}
