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
      *  @module transform()
      *  @public
      *
      *  Implements the transform() method. Displays the colorized source code.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 08.04.2007<br />
      *  Version 0.2, 05.05.2007 (PHP tags are not displayed any more)<br />
      *  Version 0.3, 01.07.2007 (Fixed bug with source code display. Now, the css class "div.phpcode" is used to format the code box)<br />
      *  Version 0.4, 21.08.2007 (Added rudiementary PHP5 support, because highlight_string() is some kind of different there)<br />
      *  Version 0.5, 16.09.2007 (Improved PHP 5 support)<br />
      *  Version 0.6, 02.01.2008 (Limited code box height to 400px)<br />
      */
      function transform(){

         // count lines
         $LineCount = substr_count($this->__Content,"\n") - 1;

         // highlight source code
         // - Remove new lines at the beginning
         // - Remove new lines and blanks at the end
         // - Remove new lines and blanks around the whole text
         $HighlightedContent = highlight_string(trim('<?php '.ltrim(rtrim($this->__Content),"\x0A..\x0D").' ?>'),true);

         // replace php start tags
         $HighlightedContent = str_replace('<font color="#007700">&lt;?</font>','',$HighlightedContent);
         $HighlightedContent = str_replace('<font color="#0000BB">&lt;?php&nbsp;','<font color="#0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<font color="#0000BB">php','<font color="#0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<font color="#0000BB">&nbsp;</font>','',$HighlightedContent);

         // enhancement to the PHP5 support
         $HighlightedContent = str_replace('<span style="color: #0000BB">&lt;?php&nbsp;','<span style="color: #0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<span style="color: #0000BB">&lt;?php','<span style="color: #0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<span style="color: #0000BB">?&gt;</span>','',$HighlightedContent);

         // replace php end tags
         $HighlightedContent = str_replace('<font color="#0000BB">?&gt;</font>','',$HighlightedContent);

         // return div enclodes source code with height limit if necessary
         if($LineCount > 27){
            return '<div class="phpcode" style="height: 400px; overflow: auto;">'.$HighlightedContent.'</div>';
          // end if
         }
         else{
            return '<div class="phpcode">'.$HighlightedContent.'</div>';
          // end else
         }

       // end function
      }

    // end class
   }
?>