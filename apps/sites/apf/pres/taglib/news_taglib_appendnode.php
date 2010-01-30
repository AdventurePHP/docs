<?php
   import('core::pagecontroller','core_taglib_appendnode');

   /**
    * @package sites::apf::pres::taglib
    * @class news_taglib_appendnode
    *
    * Enhances the <em>&lt;core:appendnode /&gt;</em> taglib to be able
    * to include language dependent template fragments.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 29.01.2010<br />
    */
   class news_taglib_appendnode extends core_taglib_appendnode {

      public function news_taglib_appendnode(){
         parent::core_taglib_appendnode();
      }

      /**
       * Includes the news template depending on the desired language.
       */
      public function onParseTime(){
         $this->setAttribute('namespace','sites::apf::pres::news');
         $this->setAttribute('template',$this->__Language.'_news');
         parent::onParseTime();
       // end function
      }
      
    // end class
   }
?>