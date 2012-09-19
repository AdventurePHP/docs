<?php
import('tools::string', 'StringAssistant');

class entity_taglib_encode extends Document {

   public function transform() {
      return StringAssistant::encodeCharactersToHTML($this->getContent());
   }

}
