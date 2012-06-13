<?php
/**
 * @package sites::apf::pres::controller::content
 * @class form_captcha_example_controller
 *
 *  Implements the document controller for the captcha form example.
 *
 * @author Christian Achatz
 * @version
 *  Version 0.1, 20.07.2008<br />
 */
class form_captcha_example_controller extends base_controller {

   /**
    * @public
    *
    *  Implements the transformContent() method of the base_controller class. Creates the document's html code.
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 20.07.2008<br />
    */
   public function transformContent() {

      // obtain a reference on the desired form (depends on the language of the document!)
      $Form__CaptchaExample = &$this->getForm('CaptchaExample_' . $this->__Language);

      // check if form is valid or not
      if ($Form__CaptchaExample->isValid() == true) {

         // print "valid" state
         if ($this->__Language == 'de') {
            $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid', 'valide');
         } else {
            $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid', 'valid');
         }

      } else {

         // print "invalid" state
         if ($this->__Language == 'de') {
            $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid', 'nicht valide');
         } else {
            $Form__CaptchaExample->setPlaceHolder('ValidOrInvalid', 'invalid');
         }

      }

      // display form on the place of definition
      $Form__CaptchaExample->setAttribute('action', '#Chapter-captcha');
      $Form__CaptchaExample->transformOnPlace();

   }

}
