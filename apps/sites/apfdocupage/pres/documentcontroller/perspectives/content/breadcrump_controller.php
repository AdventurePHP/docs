<?php
   /**
   *  @package sites::apfdocupage::pres
   *  @class breadcrump_controller
   *
   *  Document controller for the "breadcrump.html" template. Displays the breadcrump navigation.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 31.08.2008<br />
   */
   class breadcrump_controller extends baseController
   {

      function breadcrump_controller(){
      }


      /**
      *  @public
      *
      *  Displays the breadcrump navigation.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 31.08.2008<br />
      */
      function transformContent(){

         // get model object
         $Model = &Singleton::getInstance('APFModel');

         // get current page title
         $PageTitle = $Model->getAttribute('page.title');

         // get language dependent home template
         $Template__Home = &$this->__getTemplate('Home_'.$this->__Language);

         // Display HOME
         $this->setPlaceHolder('Home',$Template__Home->transformTemplate());

         // insert title of the current page
         $this->setPlaceHolder('CurrentTitle',$PageTitle);

       // end function
      }

    // end class
   }
?>