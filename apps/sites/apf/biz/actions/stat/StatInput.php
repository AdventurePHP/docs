<?php
class StatInput extends FrontcontrollerInput {

   public function StatInput() {
   }

   public function getLanguage() {
      return $this->getAttribute('lang');
   }

   public function getPageId() {
      return $this->getAttribute('id');
   }

   public function getTitle() {
      return urldecode($this->getAttribute('title'));
   }

}
