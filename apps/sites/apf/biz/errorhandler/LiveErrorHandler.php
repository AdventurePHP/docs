<?php
import('core::errorhandler', 'DefaultErrorHandler');

class LiveErrorHandler extends DefaultErrorHandler {

   function handleError($errorNumber, $errorMessage, $errorFile, $errorLine) {

      // fill attributes
      $this->__ErrorNumber = $errorNumber;
      $this->__ErrorMessage = $errorMessage;
      $this->__ErrorFile = $errorFile;
      $this->__ErrorLine = $errorLine;

      $this->logError();

      // script kiddie message
      if (substr_count($this->__ErrorMessage, 'parse_url') > 0) {
         echo 'No script kiddies allowed here! ;)';
         exit(0);
      }

      // redirect to start page
      header('Location: /');
      exit(0);

   }

}

?>