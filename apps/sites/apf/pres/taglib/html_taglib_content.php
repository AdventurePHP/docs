<?php
   import('sites::apf::biz','APFModel');
   import('sites::apf::pres::taglib','gen_taglib_highlight');
   import('sites::apf::pres::taglib','doku_taglib_link');
   import('sites::apf::pres::taglib','doku_taglib_title');
   import('sites::apf::pres::taglib','int_taglib_link');

   /**
    * @package sites::apf::pres::taglib
    * @class html_taglib_content
    *
    * Implements the "html:content" tag.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.03.2008<br />
    * Version 0.2, 17.09.2008 (Changed function to fit new model structure)<br />
    */
   class html_taglib_content extends Document {

      /**
       * @public
       *
       * Initializes the known taglibs to start from this DOM node with.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 28.03.2008<br />
       * Version 0.2, 19.09.2008(Added several taglibs)<br />
       */
      function html_taglib_content(){

         // call the parent constructor
         parent::Document();

         // include the additional tag libs
         $this->__TagLibs[] = new TagLib('sites::apf::pres::taglib','gen','highlight');
         $this->__TagLibs[] = new TagLib('sites::apf::pres::taglib','doku','link');
         $this->__TagLibs[] = new TagLib('sites::apf::pres::taglib','int','link');
         $this->__TagLibs[] = new TagLib('sites::apf::pres::taglib','doku','title');

       // end function
      }

      /**
       * @public
       *
       * Reads and parses the content from the appropriate content file.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 28.03.2008<br />
       * Version 0.2, 17.09.2008 (Changed function to fit new model structure)<br />
       */
      function onParseTime(){

         // get model
         $model = Singleton::getInstance('APFModel');

         // include the content of the model's content file in the current object
         $this->__Content = file_get_contents(
                 $model->getAttribute('content.filepath')
                 .'/content/'
                 .$model->getAttribute('page.contentfilename')
         );

         // extract tag libs included in the content
         $this->__extractTagLibTags();

         // extract document controller statements
         $this->__extractDocumentController();

       // end if
      }

    // end class
   }
?>