<?php
import('tools::string', 'StringAssistant');

class EntityEncodeTag extends Document {

   public function transform() {
      return StringAssistant::encodeCharactersToHTML($this->getContent());
   }

}
