<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\tools\string\StringAssistant;

class EntityEncodeTag extends Document {

   public function transform() {
      return StringAssistant::encodeCharactersToHTML($this->getContent());
   }

}
