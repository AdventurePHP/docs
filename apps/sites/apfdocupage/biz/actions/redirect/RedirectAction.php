<?php
   import('sites::apfdocupage::biz','APFModel');
   import('tools::http','HeaderManager');


   /**
   *  @package sites::apfdocupage::biz::actions::redirect
   *  @class RedirectAction
   *
   *  Represents the front controller action for the redirect page.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 22.12.2008<br />
   */
   class RedirectAction extends AbstractFrontcontrollerAction
   {

      function RedirectAction(){
      }


      /**
      *  @public
      *
      *  Implements the abstract run method. Initializes the model.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.12.2008<br />
      */
      function run(){

         // get model
         $model = &Singleton::getInstance('APFModel');
         $pageIndicator = $model->getAttribute('page.indicator');
         $model->setAttribute('page.language','de');

         // check, if the page indicator is present
         $requestURI = $_SERVER['REQUEST_URI'];
         if(substr_count($requestURI,$pageIndicator['de']) > 0){
            HeaderManager::redirect('http://de.adventure-php-framework.org'.$requestURI);
          // end if
         }
         elseif(substr_count($requestURI,$pageIndicator['en']) > 0){
            HeaderManager::redirect('http://en.adventure-php-framework.org'.$requestURI);
          // end elseif
         }
         else{
            // do nothing but display the splash page
          // end else
         }

       // end function
      }

    // end class
   }
?>