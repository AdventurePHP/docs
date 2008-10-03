<?php
   /**
   *  @package sites::demosite::pres::documentcontroller
   *  @class form_antispam_example_controller
   *
   *  Implements the document controller for the anti spam form example.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 17.07.2008<br />
   */
   class form_antispam_example_controller extends baseController
   {

      function form_antispam_example_controller(){
      }


      /**
      *  @public
      *
      *  Implements the transformContent() method of the baseController class. Creates the document's html code.
      *
      *  @return string $DocCode code of the current document
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.07.2008<br />
      */
      function transformContent(){

         // obtain a reference on the desired form (depends on the language of the document!)
         $Form__AntiSpamExample = &$this->__getForm('AntiSpamExample_'.$this->__Language);

         // check if form is valid or not
         if($Form__AntiSpamExample->get('isValid') == true){

            // print "valid" state
            if($this->__Language == 'de'){
               $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid','valide');
             // end if
            }
            else{
               $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid','valid');
             // end if
            }

          // end if
         }
         else{

            // print "invalid" state
            if($this->__Language == 'de'){
               $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid','nicht valide');
             // end if
            }
            else{
               $Form__AntiSpamExample->setPlaceHolder('ValidOrInvalid','invalid');
             // end if
            }

          // end else
         }

         // display form on the place of definition
         $Form__AntiSpamExample->setAttribute('action','#antispam');
         $Form__AntiSpamExample->transformOnPlace();

       // end function
      }

    // end class
   }
?>