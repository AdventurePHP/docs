<?php
namespace APF\sites\apf\biz;

interface SearchResult {

   /**
    * @return string The current language.
    */
   public function getLanguage();

   public function getPageId();

   /**
    * @return string The current title.
    */
   public function getTitle();

   /**
    * @return \DateTime The last modification date.
    */
   public function getLastModified();


}