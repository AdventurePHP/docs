<?php
namespace APF\sites\apf\biz\actions\stat;

use APF\core\frontcontroller\FrontcontrollerInput;

class StatInput extends FrontcontrollerInput {

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
