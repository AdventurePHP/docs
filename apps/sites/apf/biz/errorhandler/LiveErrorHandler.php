<?php
namespace APF\sites\apf\biz\errorhandler;

use APF\core\errorhandler\DefaultErrorHandler;

class LiveErrorHandler extends DefaultErrorHandler {

   public function handleError($errorNumber, $errorMessage, $errorFile, $errorLine, array $errorContext) {

      // fill attributes
      $this->errorNumber = $errorNumber;
      $this->errorMessage = $errorMessage;
      $this->errorFile = $errorFile;
      $this->errorLine = $errorLine;
      $this->errorContext = $errorContext;

      $this->logError();

      // script kiddie message
      if (substr_count($this->errorMessage, 'parse_url') > 0) {
         echo 'No script kiddies allowed here! ;)';
         exit(0);
      }

      // redirect to start page
      header('Location: /');
      exit(0);

   }

}
