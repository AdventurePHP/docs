<?php
   import('sites::apfdocupage::biz','APFModel');
   import('sites::apfdocupage::pres::taglib','php_taglib_highlight');
   import('sites::apfdocupage::pres::taglib','code_taglib_highlight');
   import('sites::apfdocupage::pres::taglib','html_taglib_highlight');
   import('sites::apfdocupage::pres::taglib','doku_taglib_link');
   import('sites::apfdocupage::pres::taglib','doku_taglib_title');


   /**
   *  @package sites::apfdocupage::pres::taglib
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
      *  Version 0.2, 19.09.2008(Added several taglibs)<br />
      */
      function html_taglib_content(){

         // call the parent constructor
         parent::Document();

         // include the necessary tag libs
         $this->__TagLibs[] = new TagLib('sites::apfdocupage::pres::taglib','php','highlight');
         $this->__TagLibs[] = new TagLib('sites::apfdocupage::pres::taglib','code','highlight');
         $this->__TagLibs[] = new TagLib('sites::apfdocupage::pres::taglib','html','highlight');
         $this->__TagLibs[] = new TagLib('sites::apfdocupage::pres::taglib','doku','link');
         $this->__TagLibs[] = new TagLib('sites::apfdocupage::pres::taglib','doku','title');

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