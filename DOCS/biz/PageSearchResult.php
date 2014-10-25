<?php
namespace DOCS\biz;

/**
 * This class represents the domain object of the full-text search functionality.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 08.03.2008<br />
 * Version 0.2, 02.10.2008<br />
 */
class PageSearchResult implements SearchResult {

   private $language = 'de';

   private $title;
   private $pageID;
   private $versionId;

   /**
    * @var \DateTime Last modification date.
    */
   private $lastMod;
   private $wordCount;
   private $indexedWord;

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

   public function setWordCount($count) {
      $this->wordCount = $count;
   }

   public function getWordCount() {
      return $this->wordCount;
   }

   public function setIndexedWord($word) {
      $this->indexedWord = $word;
   }

   public function getIndexedWord() {
      return $this->indexedWord;
   }

   public function setVersionId($versionId) {
      $this->versionId = $versionId;
   }

   public function getVersionId() {
      return $this->versionId;
   }

}
