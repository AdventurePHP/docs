<?php
namespace DOCS\pres\controller\content;

use APF\core\pagecontroller\BaseDocumentController;

/**
 * Implements the document controller for the captcha form example.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 20.07.2008<br />
 */
class FormCaptchaExampleController extends BaseDocumentController {

   /**
    * @public
    *
    * Implements the transformContent() method of the BaseDocumentController class. Creates the document's html code.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 20.07.2008<br />
    */
   public function transformContent() {

      // obtain a reference on the desired form (depends on the language of the document!)
      $language = $this->getDocument()->getLanguage();
      $form = $this->getForm('CaptchaExample_' . $language);

      // check if form is valid or not
      if ($form->isValid() == true) {

         // print "valid" state
         if ($language == 'de') {
            $form->setPlaceHolder('ValidOrInvalid', 'valide');
         } else {
            $form->setPlaceHolder('ValidOrInvalid', 'valid');
         }

      } else {

         // print "invalid" state
         if ($language == 'de') {
            $form->setPlaceHolder('ValidOrInvalid', 'nicht valide');
         } else {
            $form->setPlaceHolder('ValidOrInvalid', 'invalid');
         }

      }

      // display form on the place of definition
      $form->setAttribute('action', '#Chapter-captcha');
      $form->transformOnPlace();

   }

}
