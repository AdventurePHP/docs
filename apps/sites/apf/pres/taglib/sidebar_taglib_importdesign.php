<?php
   import('sites::apf::biz','APFModel');

   /**
    * @package sites::apf::pres::controller
    * @class sidebar_taglib_importdesign
    *
    * Displays the sidebar, depending on the model information.
    *
    * @author Christian Achatz
    * @version 0.1, 19.01.2010<br />
    */
   class sidebar_taglib_importdesign extends core_taglib_importdesign {

      public function sidebar_taglib_importdesign(){
         parent::core_taglib_importdesign();
      }

      /**
       * @public
       *
       * Re-implements the <em>onParseTime()</em> method for this taglib. This is
       * necessary, because the pagepart behaviour is not needed here.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 06.02.2010<br />
       */
      public function onParseTime(){

         $t = &Singleton::getInstance('BenchmarkTimer');
         $id = '('.get_class($this).') '.$this->__ObjectID.'::onParseTime()';
         $t->start($id);

         $model = &Singleton::getInstance('APFModel');
         if($model->getDisplaySidebar() == true){

            // get content
            $this->__loadContentFromFile('sites::apf::pres::templates','quicknavi');

            // parse document controller statements
            $this->__extractDocumentController();

            // extract further xml tags
            $this->__extractTagLibTags();
            
          // end if
         }

         $t->stop($id);

       // end function
      }

    // end class
   }
?>