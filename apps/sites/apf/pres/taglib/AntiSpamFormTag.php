<?php
import('tools::form::taglib', 'form_control');

/**
 * @package sites::demosite::prea::taglib
 * @class AntiSpamFormTag
 *
 *  Implements an anti spam function, that marks a form as invalid, when it is posted within
 *  a configured amount of time.
 *
 * @author Christian Achatz
 * @version
 *  Version 0.1, 17.07.2008<br />
 */
class AntiSpamFormTag extends form_control {

   public function onAfterAppend() {

      // validate the given attributes
      if ($this->getAttribute('minfilltime') === null) {
         $formName = $this->getParentObject()->getAttribute('name');
         throw new InvalidArgumentException('[AntiSpamFormTag::onAfterAppend()] There is not attribute "minfilltime" given in the anti spam tag definition in form "' . $formName . '"! Please provide the attribute mentioned containing the time in seconds the user needs to fill in the form.');
      }

      $antiSpamName = $this->getAntiSpamName();

      if (isset($_REQUEST[$antiSpamName])) {

         $currentTime = date('U');
         $previousTime = $_REQUEST[$antiSpamName];
         $minFillTime = $this->getAttribute('minfilltime');

         // mark the form as invalid, if time is not elapsed
         if (($currentTime - $minFillTime) < $previousTime) {
            $this->markAsInvalid();
         }
      }
   }

   /**
    * @public
    *
    * Implements the transform() method of the APFObject class. Returns the field's html code.
    *
    * @return string $TagCode code if the tag
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 17.07.2008<br />
    */
   public function transform() {
      return '<input type="hidden" name="' . $this->getAntiSpamName() . '" value="' . date('U') . '" />';
   }

   /**
    * @private
    *
    *  Returns the name of the anti spam field.
    *
    * @return string $AntiSpamName name of the anti spam field
    *
    * @author Christian Achatz
    * @version
    *  Version 0.1, 17.07.2008<br />
    */
   private function getAntiSpamName() {
      return strtolower($this->getParentObject()->getAttribute('name')) . '_antispam';
   }

   protected function __presetValue() {
   }

}
