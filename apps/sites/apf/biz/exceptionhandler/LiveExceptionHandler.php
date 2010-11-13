<?php
   import('core::exceptionhandler','DefaultExceptionHandler');

   class LiveExceptionHandler extends DefaultExceptionHandler {

      public function handleException(Exception $exception){

         // fill attributes
         $this->__ExceptionNumber = $exception->getCode();
         $this->__ExceptionMessage = $exception->getMessage();
         $this->__ExceptionFile = $exception->getFile();
         $this->__ExceptionLine = $exception->getLine();
         $this->__ExceptionTrace = $exception->getTrace();
         $this->__ExceptionType = get_class($exception);

         // log exception
         $this->__logException();

         // script kiddie message
         if(substr_count($this->__ErrorMessage, 'parse_url') > 0){
            echo 'No script kiddies allowed here! ;)';
            exit(0);
         }

         // redirect to start page
         header('Location: /');
         exit(0);

      }

   }
?>