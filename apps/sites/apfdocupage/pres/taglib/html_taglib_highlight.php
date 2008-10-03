<?php
   /**
   *  @package sites::apfdocupage::pres::taglib
   *  @class html_taglib_highlight
   *
   *  Implements the tag library for HTML code highlighting.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 08.04.2007<br />
   */
   class html_taglib_highlight extends Document
   {

      function html_taglib_highlight(){
      }


      /**
      *  @public
      *
      *  Displays the highlighted HTML content.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 21.08.2007<br />
      */
      function transform(){
         return '<pre class="tagexample">'.str_replace('<','&lt;',str_replace('>','&gt;',$this->__Content)).'</pre>';
       // end function
      }

    // end class
   }
?>