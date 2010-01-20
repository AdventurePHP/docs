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
         $this->setAttribute('namespace','sites::apf::pres::templates');
         $this->setAttribute('template','quicknavi');
         parent::core_taglib_importdesign();
      }

      public function onParseTime(){
         $model = &Singleton::getInstance('APFModel');
         if($model->getDisplaySidebar() == true){
            parent::onParseTime();
         }
      }

    // end class
   }
?>