<?php
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives
   *  @class start_perspective_controller
   *
   *  Implements the start perspective's document controller.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 15.10.2008<br />
   */
   class start_perspective_controller extends baseController
   {

      function start_perspective_controller(){
      }


      /**
      *  @public
      *
      *  Displays the meta information of the start perspective.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 15.10.2008<br />
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

         // expires header
         $this->setPlaceHolder('Expires',date('D, d M Y H:i:s \G\M\T',strtotime('+4 weeks')));

       // end function
      }

    // end class
   }
?>