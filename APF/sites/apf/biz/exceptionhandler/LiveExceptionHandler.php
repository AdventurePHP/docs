<?php
namespace APF\sites\apf\biz\exceptionhandler;

use APF\core\exceptionhandler\DefaultExceptionHandler;

class LiveExceptionHandler extends DefaultExceptionHandler {

   public function handleException(\Exception $exception) {

      // fill attributes
      $this->exceptionNumber = $exception->getCode();
      $this->exceptionMessage = $exception->getMessage();
      $this->exceptionFile = $exception->getFile();
      $this->exceptionLine = $exception->getLine();
      $this->exceptionTrace = $exception->getTrace();
      $this->exceptionType = get_class($exception);

      $this->logException();

      // script kiddie message
      if (substr_count($this->exceptionMessage, 'parse_url') > 0) {
         echo 'No script kiddies allowed here! ;)';
         exit(0);
      }

      // redirect to start page
      header('Location: /');
      exit(0);

   }

}
