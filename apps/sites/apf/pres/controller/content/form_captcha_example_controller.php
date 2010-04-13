<?php
   /**
    *  @package sites::apf::pres::controller::content
    *  @class form_captcha_example_controller
    *
    *  Implements the document controller for the captcha form example.
    *
    *  @author Christian Achatz
    *  @version
    *  Version 0.1, 20.07.2008<br />
    */
   class form_captcha_example_controller extends base_controller {

      function form_captcha_example_controller() {
      }

      /**
       *  @public
       *
       *  Implements the transformContent() method of the base_controller class. Creates the document's html code.
       *
       *  @author Christian Achatz
       *  @version
       *  Version 0.1, 20.07.2008<br />
       */
      function transformContent() {

         // obtain a reference on the desired form (depends on the language of the document!)
         $Form__CaptchaExample = &$this->__getForm('CaptchaExample_'.$this->__Language);

         // check if form is valid or not
         if($Form__CaptchaExample->isValid() == true) {

            // print "valid" state
            if($this->__Language == 'de') {
               $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid','valide');
               // end if
            }
            else {
               $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid','valid');
               // end if
            }

            // end if
         }
         else {

            // print "invalid" state
            if($this->__Language == 'de') {
               $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid','nicht valide');
               // end if
            }
            else {
               $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid','invalid');
               // end if
            }

            // end else
         }

         // display form on the place of definition
         $Form__CaptchaExample->setAttribute('action','#Chapter-captcha');
         $Form__CaptchaExample->transformOnPlace();

         // end function
      }

      // end class
   }
?>