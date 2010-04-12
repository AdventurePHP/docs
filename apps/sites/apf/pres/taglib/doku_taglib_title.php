<?php
   import('sites::apf::biz','APFModel');

   /**
    * @package sites::apf::pres::taglib
    * @class doku_taglib_title
    *
    * Implements the title taglib. The tag informs the model about the page's title and
    * displays a <h2> heading.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 18.09.2008<br />
    */
   class doku_taglib_title extends Document {

      /**
       * @private
       * Indicates the page's title.
       */
      var $__Title = null;

      function doku_taglib_title(){
      }

      /**
       * @public
       *
       * Analyzes the attributes and content and informs the model.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 18.09.2008<br />
       * Version 0.2, 19.09.2008 (Added meta tag and urlname handling; changed description output format)<br />
       * Version 0.3, 30.09.2008 (Removed double blanks in meta description)<br />
       */
      function onParseTime(){

         // get page title
         $this->__Title = $this->getAttribute('title');
         if($this->__Title === null){
            throw new IllegalArgumentException('[doku_taglib_title::onParseTime()] The attribute "title" is missing. Please provide the page title!',E_USER_ERROR);
            exit(1);
          // end if
         }

         // get page tags
         $tags = $this->getAttribute('tags');
         if($tags === null){
            throw new IllegalArgumentException('[doku_taglib_title::onParseTime()] The attribute "tags" is missing. Please provide the page meta tags!',E_USER_ERROR);
            exit(1);
          // end if
         }

         // get urlname
         $urlName = $this->getAttribute('urlname');
         if($urlName === null){
            throw new IllegalArgumentException('[doku_taglib_title::onParseTime()] The attribute "urlname" is missing. Please provide url name of the page!',E_USER_ERROR);
            exit(1);
          // end if
         }

         // get page description
         if(empty($this->__Content)){
            throw new IllegalArgumentException('[doku_taglib_title::onParseTime()] No page description given in the tag\'s content area. Please provide the page description!',E_USER_ERROR);
            exit(1);
          // end if
         }

         // get parent documentation page id
         $parentPageId = $this->getAttribute('parent');

         // inform model
         $model = Singleton::getInstance('APFModel');
         $model->setTitle($this->__Title);
         $model->setAttribute('page.description',str_replace('  ',' ',str_replace("\r",'',str_replace("\n",'',trim($this->__Content)))));
         $model->setAttribute('page.tags',$tags);
         $model->setAttribute('page.urlname',$urlName);
         $model->setParentPageId($parentPageId);

       // end function
      }

      /**
       * @public
       *
       * Displays the <h2> heading.
       *
       * @return string $Heading the <h2> heading
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 18.09.2008<br />
       * Version 0.2, 03.10.2008 (Introduced the "display" attribute. If present and set to false, the title will not be displayed)<br />
       */
      function transform(){
         $display = $this->getAttribute('display');
         if($display == 'false'){
            return (string)'';
          // end if
         }
         else{
           return '<h2>'.$this->__Title.'</h2>';
          // end else
         }

       // end function
      }

    // end class
   }
?>