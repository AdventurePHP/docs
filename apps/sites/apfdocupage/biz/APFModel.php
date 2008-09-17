<?php
   /**
   *  @package sites::apfdocupage::biz
   *  @class APFModel
   *
   *  Implements the model of the APF docu page.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 22.08.2008<br />
   */
   class APFModel extends coreObject
   {

      /**
      *  @public
      *
      *  Initializes the model's attributes.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.08.2008<br />
      */
      function APFModel(){

         // indicates the current perspective
         $this->__Attributes['perspective.name'] = 'start';

         // indicates the current language
         $this->__Attributes['page.language'] = 'de';

         // indivates the current page id
         $this->__Attributes['page.id'] = '001';

         // indicates the current page title
         $this->__Attributes['page.title'] = 'Startseite';

         // defines the page indicator per language
         $this->__Attributes['page.indicator'] = array(
                                                       'de' => 'Seite',
                                                       'en' => 'Page'
         );

       // end function
      }

    // end class
   }
?>