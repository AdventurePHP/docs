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
         $Content = trim($this->__Content);

         // initialize return value
         $Link = (string)'';

         // check, if content is present
         if(strlen($Content) > 0){

            // add link tag to the buffer
            $Link .= '<a ';

            // add link to the buffer
            $Link .= 'href="'.trim($this->__Content).'" ';

            // add title the buffer
            $Link .= 'title="'.trim($this->__Content).'" ';

            // add target
            if(isset($this->__Attributes['target'])){
               $Link .= 'target="'.$this->__Attributes['target'].'" ';
             // end if
            }
            else{
               $Link .= 'target="_blank" ';
             // end else
            }

            // add css class
            if(isset($this->__Attributes['class'])){
               $Link .= 'class="'.$this->__Attributes['class'].'" ';
             // end if
            }

            // add css style
            if(isset($this->__Attributes['style'])){
               $Link .= 'style="'.$this->__Attributes['style'].'" ';
             // end if
            }

            // add link rewrite protection
            $Link .= 'linkrewrite="false"';

            // close link attribute list
            $Link .= '>';

            // add link text:
            // behaviour like PHPBB. Links are limited to a certain number of letters and are
            // displayed as {PART1}..{10 letters from the end}
            if(strlen($Content) > $this->__MaxLinkLength){
               $Link .= substr($Content,0,$this->__MaxLinkLength - 20).'...'.substr($Content,strlen($Content) - 10 ,10);
             // end if
            }
            else{
               $Link .= $Content;
             // end else
            }

            // close link
            $Link .= '</a>';

          // end if
         }

         // return link string
         return $Link;

       // end function
      }

    // end class
   }
?>