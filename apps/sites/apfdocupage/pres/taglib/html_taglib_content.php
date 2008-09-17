<?php
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::demosite::pres::taglib
   *  @class html_taglib_content
   *
   *  Implements the "html:content" tag.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 28.03.2008<br />
   *  Version 0.2, 17.09.2008 (Changed function to fit new model structure)<br />
   */
   class html_taglib_content extends Document
   {


      /**
      *  @public
      *
      *  Constructor of the class. Calls the parent constructor.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 28.03.2008<br />
      */
      function html_taglib_content(){
         parent::Document();
       // end function
      }


      /**
      *  @public
      *
      *  Reads and parses the content from the appropriate content file.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 28.03.2008<br />
      *  Version 0.2, 17.09.2008 (Changed function to fit new model structure)<br />
      */
      function onParseTime(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // include the content of the model's content file in the current object
         $this->__Content = file_get_contents($Model->getAttribute('content.filepath').'/content/'.$Model->getAttribute('page.contentfilename'));

         // extract tag libs included in the content
         $this->__extractTagLibTags();

         // extract document controller statements
         $this->__extractDocumentController();

       // end if
      }

    // end class
   }
?>