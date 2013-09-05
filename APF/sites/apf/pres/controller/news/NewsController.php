<?php
namespace APF\sites\apf\pres\controller\news;

use APF\core\pagecontroller\BaseDocumentController;
use APF\sites\apf\pres\taglib\NewsItemTag;

class NewsController extends BaseDocumentController {

   private static $TYPE = 'type';

   /**
    * @public
    *
    * Presents the number of desired news to the user. In case of the start page, these are
    * the first 4 news, in case of the news page, all news except the first 4.
    */
   public function transformContent() {

      $type = $this->getDocument()->getAttribute(self::$TYPE);
      if ($type === 'start') {
         $maxCount = 5;
         $startCount = 0;
      } else {
         $maxCount = 1000;
         $startCount = 5;
      }

      $count = 0;
      $newsItems = & $this->getNewsItems();
      foreach ($newsItems as $objectId => $DUMMY) {
         if ($count >= $startCount && $startCount < $maxCount) {
            $newsItems[$objectId]->transformOnPlace();
            $startCount++;
         }
         $count++;
      }

   }

   /**
    * @return NewsItemTag[] List of news items.
    */
   private function &getNewsItems() {
      $children = & $this->getDocument()->getChildren();
      $newsItems = array();
      foreach ($children as $objectId => $DUMMY) {
         if ($children[$objectId] instanceof NewsItemTag) {
            $newsItems[$objectId] = & $children[$objectId];
         }
      }
      return $newsItems;
   }

}
