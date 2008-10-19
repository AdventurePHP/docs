<?php
   import('sites::apfdocupage::biz','APFModel');
   import('sites::apfdocupage::pres::taglib','doku_taglib_link');


   /**
   *  @package sites::apfdocupage::pres::taglib
   *  @class html_taglib_quicknavi
   *
   *  Implements the "html:quicknavi" tag.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 05.10.2008<br />
   */
   class html_taglib_quicknavi extends Document
   {


      /**
      *  @public
      *
      *  Constructor of the class. Calls the parent constructor.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 28.03.2008<br />
      *  Version 0.2, 19.09.2008(Added several taglibs)<br />
      */
      function html_taglib_quicknavi(){

         // call the parent constructor
         parent::Document();

         // include the necessary tag libs
         $this->__TagLibs[] = new TagLib('sites::apfdocupage::pres::taglib','doku','link');

       // end function
      }


      /**
      *  @public
      *
      *  Reads and parses the content from the appropriate quicknavi file.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 05.10.2008<br />
      */
      function onParseTime(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // include the content of the model's quicknavi file in the current object
         $this->__Content = file_get_contents($Model->getAttribute('content.filepath').'/quicknavi/'.$Model->getAttribute('page.quicknavifilename'));

         // extract tag libs included in the content
         $this->__extractTagLibTags();

         // extract document controller statements
         $this->__extractDocumentController();

       // end function
      }

    // end class
   }
?>