<?php
namespace DOCS\biz;

/**
 * This class represents the domain object of the wiki search functionality.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 19.05.2013<br />
 */
class WikiSearchResult implements SearchResult {

   private $language = 'de';

   private $title;
   private $pageID;

   /**
    * @var \DateTime Last modification date.
    */
   private $lastMod;

   /**
    * @param string $language
    */
   public function setLanguage($language) {
      $this->language = $language;
   }

   /**
    * @return string
    */
   public function getLanguage() {
      return $this->language;
   }

   public function setPageId($pageId) {
      $this->pageID = $pageId;
   }

   public function getPageId() {
      return $this->pageID;
   }

   public function setTitle($title) {
      $this->title = $title;
   }

   public function getTitle() {
      return $this->title;
   }

   public function setLastModified(\DateTime $lastMod) {
      $this->lastMod = $lastMod;
   }

   /**
    * @return \DateTime The last modification date.
    */
   public function getLastModified() {
      return $this->lastMod;
   }

}
