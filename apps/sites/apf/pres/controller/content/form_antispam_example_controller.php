<?php
/**
 * @package sites::apf::pres::controller::content
 * @class form_antispam_example_controller
 *
 *  Implements the document controller for the anti spam form example.
 *
 * @author Christian Achatz
 * @version
 *  Version 0.1, 17.07.2008<br />
 */
class form_antispam_example_controller extends BaseDocumentController {

   /**
    * @public
    *
    *  Implements the transformContent() method of the BaseDocumentController class. Creates the document's html code.
    *
    * @return string $DocCode code of the current document
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 17.07.2008<br />
    */
   public function transformContent() {

      // obtain a reference on the desired form (depends on the language of the document!)
      $Form__AntiSpamExample = &$this->getForm('AntiSpamExample_' . $this->__Language);

      // check if form is valid or not
      if ($Form__AntiSpamExample->isValid() == true) {

         // print "valid" state
         if ($this->__Language == 'de') {
            $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid', 'valide');
         } else {
            $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid', 'valid');
         }

      } else {

         // print "invalid" state
         if ($this->__Language == 'de') {
            $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid', 'nicht valide');
         } else {
            $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid', 'invalid');
         }

      }

      // display form on the place of definition
      $Form__AntiSpamExample->setAttribute('action', '#Chapter-antispam');
      $Form__AntiSpamExample->transformOnPlace();

   }

}
