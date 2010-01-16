<?php
   import('sites::apf::biz','APFModel');
   import('sites::apf::pres::taglib','int_taglib_link');

   /**
    * @package sites::apf::pres::taglib
    * @class html_taglib_quicknavi
    *
    * Implements the taglib, that displays the quicknavi content.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.12.2009<br />
    */
   class html_taglib_quicknavi extends Document {

      public function html_taglib_quicknavi() {

         // call the parent constructor
         parent::Document();

         // include the additional tag libs
         $this->__TagLibs[] = new TagLib('sites::apf::pres::taglib','int','link');
         
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
      function onParseTime() {

         // get model
         $model = Singleton::getInstance('APFModel');

         // include the content of the model's content file in the current object
         $this->__Content .= file_get_contents(
                 $model->getAttribute('content.filepath')
                 .'/quicknavi/'
                 .$model->getAttribute('page.quicknavifilename')
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