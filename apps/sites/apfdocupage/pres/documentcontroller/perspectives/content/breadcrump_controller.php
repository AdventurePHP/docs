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
      *  Version 0.2, 22.11.2008 (Link to current page is now generated from the model information)<br />
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

         // insert link to the current page
         $lang = $Model->getAttribute('page.language');
         $pageIndicators = $Model->getAttribute('page.indicator');
         $pageID = $Model->getAttribute('page.id');
         $urlName = $Model->getAttribute('page.urlname');
         $this->setPlaceHolder('CurrentURL','./?'.$pageIndicators[$lang].'='.$pageID.'-'.$urlName);

       // end function
      }

    // end class
   }
?>