<?php
namespace DOCS\biz\actions\stat;

use APF\core\frontcontroller\FrontcontrollerInput;

class StatInput extends FrontcontrollerInput {

   public function getLanguage() {
      return $this->getParameter('lang');
   }

   public function getPageId() {
      return $this->getParameter('id');
   }

   public function getTitle() {
      return urldecode($this->getParameter('title'));
   }

}
