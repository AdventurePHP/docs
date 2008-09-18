<?php
   import('tools::form::taglib','ui_element');


   /**
   *  @package sites::demosite::prea::taglib
   *  @class form_taglib_antispam
   *
   *  Implements an anti spam function, that marks a form as invalid, when it is posted within
   *  a configured amount of time.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 17.07.2008<br />
   */
   class form_taglib_antispam extends ui_element
   {

      function form_taglib_antispam(){
      }


      /**
      *  @public
      *
      *  Implements the transform() method of the coreObject class. Returns the field's html code.
      *
      *  @return string $TagCode code if the tag
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.07.2008<br />
      */
      function onAfterAppend(){

         // validate the given attributes
         if(!isset($this->__Attributes['minfilltime'])){
            $FormName = $this->__ParentObject->getAttribute('name');
            trigger_error('[form_taglib_antispam::onAfterAppend()] There is not attribute "minfilltime" given in the anti spam tag definition in form "'.$FormName.'"! Please provide the attribute mentioned containing the time in seconds the user needs to fill in the form.');
          // end if
         }

         // mark form as invalid, if minimum fill time was not reached
         $AntiSpamName = $this->__getAntiSpamName();

         if(isset($_REQUEST[$AntiSpamName])){

            // gather information
            $CurrentTime = date('U');
            $PreviousTime = $_REQUEST[$AntiSpamName];
            $MinFillTime = $this->__Attributes['minfilltime'];

            // mark the form as invalid, if time is not elapsed
            if(($CurrentTime - $MinFillTime) < $PreviousTime){
               $this->__ParentObject->set('isValid',false);
             // end if
            }

          // end if
         }

       // end function
      }


      /**
      *  @public
      *
      *  Implements the transform() method of the coreObject class. Returns the field's html code.
      *
      *  @return string $TagCode code if the tag
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.07.2008<br />
      */
      function transform(){
         return '<input type="hidden" name="'.$this->__getAntiSpamName().'" value="'.date('U').'" />';
       // end function
      }


      /**
      *  @private
      *
      *  Returns the name of the anti spam field.
      *
      *  @return string $AntiSpamName name of the anti spam field
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.07.2008<br />
      */
      function __getAntiSpamName(){
         return strtolower($this->__ParentObject->getAttribute('name')).'_antispam';
       // end function
      }

    // end class
   }
?>