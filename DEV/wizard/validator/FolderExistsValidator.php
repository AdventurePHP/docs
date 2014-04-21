<?php
namespace DEV\wizard\validator;

use APF\tools\form\validator\TextFieldValidator;

class FolderExistsValidator extends TextFieldValidator {

   public function validate($input) {
      if (file_exists($input) && is_dir($input)) {
         return true;
      }

      return false;
   }

}
