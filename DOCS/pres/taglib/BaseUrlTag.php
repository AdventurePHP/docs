<?php
namespace DOCS\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\core\registry\Registry;

class BaseUrlTag extends Document {

   public function onAfterAppend() {
   }

   public function transform() {
      return Registry::retrieve('DOCS', $this->getAttribute('type'));
   }

}
