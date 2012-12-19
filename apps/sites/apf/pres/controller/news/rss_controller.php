<?php
class rss_controller extends BaseDocumentController {

   private static $TYPE = 'type';

   /**
    * @public
    *
    * Presents the number of desired news to the user. In case of the start page, these are
    * the first 4 news, in case of the news page, all news except the first 4.
    */
   public function transformContent() {

      $type = $this->getAttribute(self::$TYPE);
      if ($type === 'start') {
         $maxCount = 4;
         $startCount = 0;
      } else {
         $maxCount = 1000;
         $startCount = 4;
      }

      $count = 0;
      $newsItems = &$this->getNewsItems();
      foreach ($newsItems as $objectId => $DUMMY) {
         if ($count > $startCount && $startCount < $maxCount) {
            $newsItems[$objectId]->transformOnPlace();
            $newsItems[$objectId]->setDisplayMode(NewsItemTag::$MODE_RSS);
            $startCount++;
         }
         $count++;
      }

   }

   /**
    * @return NewsItemTag[] List of news items.
    */
   private function &getNewsItems() {
      $children = &$this->__Document->getChildren();
      $newsItems = array();
      foreach ($children as $objectId => $DUMMY) {
         if (get_class($children[$objectId]) === 'NewsItemTag') {
            $newsItems[$objectId] = &$children[$objectId];
         }
      }
      return $newsItems;
   }

}
