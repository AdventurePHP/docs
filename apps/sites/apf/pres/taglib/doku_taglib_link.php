<?php
   /**
    * @package sites::apf::pres::taglib
    * @class doku_taglib_link
    *
    * Implements a tag library for creating HTML links out of normal URLs.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 15.08.2007<br />
    */
   class doku_taglib_link extends Document {

      /**
       *  @private
       *  Defines the maximum length of a link name.
       */
      var $__MaxLinkLength = 50;

      function doku_taglib_link(){
      }

      /**
       * @public
       *
       * Creates the link output.
       *
       * @return $Link the desired link sting
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 15.08.2007<br />
       * Version 0.2, 18.09.2008 (Translation and refactoring for new docu page)<br />
       */
      function transform(){

         // remove blanks
         $content = trim($this->__Content);

         // initialize return value
         $link = (string)'';

         // check, if content is present
         if(strlen($content) > 0){

            // add link tag to the buffer
            $link .= '<a ';

            // add link to the buffer
            $link .= 'href="'.$content.'" ';

            // add title the buffer
            $link .= 'title="'.$content.'" ';

            // add css class
            if(isset($this->__Attributes['class'])){
               $link .= 'class="'.$this->__Attributes['class'].'" ';
             // end if
            }

            // add css style
            if(isset($this->__Attributes['style'])){
               $link .= 'style="'.$this->__Attributes['style'].'" ';
             // end if
            }

            // add link rewrite protection
            $link .= 'linkrewrite="false"';

            // close link attribute list
            $link .= '>';

            // add link text:
            // behaviour like PHPBB. Links are limited to a certain number of letters and are
            // displayed as {PART1}..{10 letters from the end}
            if(strlen($content) > $this->__MaxLinkLength){
               $link .= substr($content,0,$this->__MaxLinkLength - 20).'...'.substr($content,strlen($content) - 10 ,10);
             // end if
            }
            else{
               $link .= $content;
             // end else
            }

            // close link
            $link .= '</a>';

          // end if
         }

         return $link;

       // end function
      }

    // end class
   }
?>