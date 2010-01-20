<?php
   /**
    * @package sites::apf::biz
    * @class APFModel
    *
    * Implements the model of the APF docu page.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.08.2008<br />
    */
   class APFModel extends coreObject {

      private static $PAGEID = 'page.id';
      private static $PARENT_PAGEID = 'parent.page.id';
      private static $LANG = 'page.language';
      private static $TITLE = 'page.title';
      private static $DISPLAY_SIDEBAR = 'page.display.sidebar';

      /**
       * @public
       *
       * Initializes the model's attributes.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 22.08.2008<br />
       * Version 0.2, 17.09.2008 (Added content and quicknavi file name)<br />
       * Version 0.3, 18.09.2008 (Added the page description attribute)<br />
       * Version 0.4, 19.09.2008 (Added the pags tags and urlname attribute)<br />
       * Version 0.5, 19.10.2008 (Changed content file path to new directory)<br />
       */
      function APFModel(){

         // indicates the path to the content and quicknavi files
         $this->__Attributes['content.filepath'] = '../apps/sites/apf/pres';

         // indicates the current perspective
         $this->__Attributes['perspective.name'] = 'start';

         // indicates the current language
         $this->__Attributes[self::$LANG] = null;

         // indivates the current page id
         $this->__Attributes[self::$PAGEID] = null;

         // indivates the parent's page id
         $this->__Attributes[self::$PARENT_PAGEID] = null;

         // indicates the current page title
         $this->__Attributes[self::$TITLE] = null;

         // indicates the current urlname
         $this->__Attributes['page.urlname'] = null;

         // indicates the current page description
         $this->__Attributes['page.description'] = null;

         // indicates the current page tags
         $this->__Attributes['page.tags'] = null;

         // indicates the current content file name
         $this->__Attributes['page.contentfilename'] = null;

         // indicates the current quicknavi file name
         $this->__Attributes['page.quicknavifilename'] = null;

         // defines the page indicator per language
         $this->__Attributes['page.indicator'] = array(
                                                       'de' => 'Seite',
                                                       'en' => 'Page'
         );

         // display sidebar (true) or not (false)
         $this->__Attributes[self::$DISPLAY_SIDEBAR] = true;

       // end function
      }

      public function getLangUrlIdentifier(){
         $lang = $this->getAttribute(self::$LANG);
         return $this->getUrlIdentifier($lang);
      }

      /**
       * @public
       *
       * Returns the language identifier used to decide the language displayed.
       * 
       * @param string $lang The desired language.
       * @return string The url lang identifier.
       */
      public function getUrlIdentifier($lang){
         if(isset($this->__Attributes['page.indicator'][$lang])){
            return $this->__Attributes['page.indicator'][$lang];
         }
         else {
            return 'Page';
         }
      }

      public function getPageId(){
         return $this->getAttribute(self::$PAGEID);
      }

      public function getLanguage(){
         return $this->getAttribute(self::$LANG);
      }

      public function setLanguage($lang){
         $this->setAttribute(self::$LANG,$lang);
      }

      public function setParentPageId($pageId){
         $this->setAttribute(self::$PARENT_PAGEID,$pageId);
      }

      public function getParentPageId(){
         return $this->getAttribute(self::$PARENT_PAGEID);
      }

      public function getTitle(){
         return $this->getAttribute(self::$TITLE);
      }

      public function setTitle($title){
         $this->setAttribute(self::$TITLE,$title);
      }

      public function setDisplaySidebar($displaySidebar){
         $this->setAttribute(self::$DISPLAY_SIDEBAR,$displaySidebar);
      }

      public function getDisplaySidebar(){
         return $this->getAttribute(self::$DISPLAY_SIDEBAR);
      }

    // end class
   }
?>