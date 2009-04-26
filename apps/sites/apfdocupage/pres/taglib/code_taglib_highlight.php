<?php
   /**
   *  @package sites::demosite::pres::taglib
   *  @class code_taglib_highlight
   *
   *  Implements the code code highlighting library.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 26.04.2009<br />
   */
   class code_taglib_highlight extends Document
   {

      function code_taglib_highlight(){
      }


      /**
      *  @public
      *
      *  Displays the colorized tag source code.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 24.04.2009<br />
      */
      function transform(){
         return '<pre name="code" class="css">'.$this->__Content.'</pre>';
       // end function
      }

    // end class
   }
?>