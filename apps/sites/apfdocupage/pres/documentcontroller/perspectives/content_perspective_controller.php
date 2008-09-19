<?php
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives
   *  @class content_perspective_controller
   *
   *  Implements the content perspective's document controller.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 19.09.2008<br />
   */
   class content_perspective_controller extends baseController
   {

      function content_perspective_controller(){
      }


      /**
      *  @public
      *
      *  Displays the meta information of the content perspective.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 19.09.2008<br />
      */
      function transformContent(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // current URI
         $this->setPlaceHolder('URI','http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

         // current date
         $TZDiff = date('O');
         $this->setPlaceHolder('Date',date('Y-m-d\TH:i:s').substr($TZDiff,0,3).':'.substr($TZDiff,3,2));

         // current language
         $this->setPlaceHolder('Language',$Model->getAttribute('page.language'));

         // current title
         $this->setPlaceHolder('Title',$Model->getAttribute('page.title'));

         // expires header
         $this->setPlaceHolder('Expires',date('D, d M Y H:i:s \G\M\T',strtotime('+4 weeks')));

         // keywords
         $this->setPlaceHolder('Keywords',$Model->getAttribute('page.tags'));

         // description
         $this->setPlaceHolder('Description',$Model->getAttribute('page.description'));

       // end function
      }

    // end class
   }
?>