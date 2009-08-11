<?php
   import('modules::socialbookmark::pres::taglib','social_taglib_bookmark');
   import('sites::apfdocupage::biz','APFModel');
   import('tools::request','RequestHandler');


   /**
   *  @package sites::apfdocupage::pres::taglib
   *  @module apf_taglib_socialbookmark
   *
   *  Implements a wrapper taglib for the social bookmark module.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 25.05.2008<br />
   *  Version 0.2, 17.10.2008 (Addapted to new documentation page)<br />
   */
   class apf_taglib_socialbookmark extends social_taglib_bookmark
   {

      function apf_taglib_socialbookmark(){
         parent::social_taglib_bookmark();
       // end function
      }


      /**
      *  @public
      *
      *  Creates the bookmark symbols with use of the social bookmark manager.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 25.05.2008<br />
      *  Version 0.2, 17.10.2008 (Addapted to new documentation page)<br />
      */
      function transform(){

         // get model
         $Model = &$this->__getServiceObject('sites::apfdocupage::biz','APFModel');

         // get model attributes
         $PageIndicators = $Model->getAttribute('page.indicator');
         $CurrentPageIndicator = $PageIndicators[$Model->getAttribute('page.language')];

         // register current url name
         $_LOCALS = RequestHandler::getValues(array($CurrentPageIndicator));

         // set title
         $this->__Attributes['title'] = 'Adventure PHP Framework - '.$Model->getAttribute('page.title');

         // create bookmark list
         return parent::transform();

       // end function
      }

    // end class
   }
?>