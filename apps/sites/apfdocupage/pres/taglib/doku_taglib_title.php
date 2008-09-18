<?php
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres::taglib
   *  @class doku_taglib_title
   *
   *  Implements the title taglib. The tag informs the model about the page's title and
   *  displays a <h2> heading.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 18.09.2008<br />
   */
   class doku_taglib_title extends Document
   {


      /**
      *  @private
      *  Indicates the page's title.
      */
      var $__Title = null;


      function doku_taglib_title(){
      }


      /**
      *  @public
      *
      *  Analyzes the attributes and content and informs the model.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 18.09.2008<br />
      */
      function onParseTime(){

         // get page title
         if(!isset($this->__Attributes['title'])){
            trigger_error('[doku_taglib_title::onParseTime()] The attribute "title" is missing. Please provide the page title!',E_USER_ERROR);
            exit(1);
          // end if
         }
         $this->__Title = $this->__Attributes['title'];

         // get page description
         if(empty($this->__Content)){
            trigger_error('[doku_taglib_title::onParseTime()] No page description given in the tag\'s content area. Please provide the page description!',E_USER_ERROR);
            exit(1);
          // end if
         }

         // inform model
         $Model = &Singleton::getInstance('APFModel');
         $Model->setAttribute('page.title',$this->__Title);
         $Model->setAttribute('page.description',trim($this->__Content));

       // end function
      }


      /**
      *  @public
      *
      *  Displays the <h2> heading.
      *
      *  @return string $Heading the <h2> heading
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 18.09.2008<br />
      */
      function transform(){
         return '<h2>'.$this->__Title.'</h2>';
       // end function
      }

    // end class
   }
?>