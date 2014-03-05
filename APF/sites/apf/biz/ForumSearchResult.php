<?php
namespace APF\sites\apf\biz;

/**
 * @package APF\sites\apf\biz
 * @class ForumSearchResult
 *
 * This class represents the domain object of the forum search functionality.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 19.05.2013<br />
 */
class ForumSearchResult implements SearchResult {

   private $language = 'de';

   private $title;
   private $pageID;

   /**
    * @var \DateTime Last modification date.
    */
   private $lastMod;

   private $forumId;
   private $topicId;
   private $postId;

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

   /**
    * @param mixed $forumId
    */
   public function setForumId($forumId) {
      $this->forumId = $forumId;
   }

   /**
    * @return mixed
    */
   public function getForumId() {
      return $this->forumId;
   }

   /**
    * @param mixed $postId
    */
   public function setPostId($postId) {
      $this->postId = $postId;
   }

   /**
    * @return mixed
    */
   public function getPostId() {
      return $this->postId;
   }

   /**
    * @param mixed $topicId
    */
   public function setTopicId($topicId) {
      $this->topicId = $topicId;
   }

   /**
    * @return mixed
    */
   public function getTopicId() {
      return $this->topicId;
   }

}
