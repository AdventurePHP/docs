<?php
namespace DOCS\biz\errorhandler;

use APF\core\errorhandler\DefaultErrorHandler;
use ErrorException;

class LiveErrorHandler extends DefaultErrorHandler {

   public function handleError($errorNumber, $errorMessage, $errorFile, $errorLine) {
      // chain to LiveExceptionHandler for simplicity reasons
      throw new ErrorException($errorMessage, $errorNumber, 1, $errorFile, $errorLine);
   }

}
