<?php
   /**
   *  @package sites::demosite::pres::taglib
   *  @class php_taglib_highlight
   *
   *  Implements the php code highlighting library.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 08.04.2007<br />
   *  Version 0.2, 17.09.2008 (Changed documentation to english language)<br />
   */
   class php_taglib_highlight extends Document
   {

      function php_taglib_highlight(){
      }

      /**
      *  @public
      *
      *  Displays the colorized source code.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 08.04.2007<br />
      *  Version 0.2, 05.05.2007 (PHP tags are not displayed any more)<br />
      *  Version 0.3, 01.07.2007 (Fixed bug with source code display. Now, the css class "div.phpcode" is used to format the code box)<br />
      *  Version 0.4, 21.08.2007 (Added rudiementary PHP5 support, because highlight_string() is some kind of different there)<br />
      *  Version 0.5, 16.09.2007 (Improved PHP 5 support)<br />
      *  Version 0.6, 02.01.2008 (Limited code box height to 400px)<br />
      *  Version 0.7, 15.10.2008 (Changed css behavior)<br />
      *  Version 0.8, 05.01.2009 (Introduced the dp.SyntaxHighlighter js highlighter (testing only))<br />
      *  Version 0.9, 24.04.2009 (Finalized js code highlighting)<br />
      */
      function transform(){
         return '<pre name="code" class="php">'.$this->__Content.'</pre>';
       // end function
      }

    // end class
   }
?>