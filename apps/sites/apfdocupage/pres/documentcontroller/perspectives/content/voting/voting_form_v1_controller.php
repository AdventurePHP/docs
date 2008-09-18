<?php
   import('tools::variablen','variablenHandler');
   import('sites::apfdocupage::biz','APFModel');
   import('tools::link','frontcontrollerLinkHandler');
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content::voting','voting_base_controller');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content::voting
   *  @class voting_form_v1_controller
   *
   *  Implements the document controller for the voting form.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 17.05.2008<br />
   */
   class voting_form_v1_controller extends voting_base_controller
   {

      function voting_form_v1_controller(){
      }


      /**
      *  @public
      *
      *  Displays and manages the voting form.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      *  Version 0.2, 18.09.2008 (Changed model behavior)<br />
      */
      function transformContent(){

         // get form
         $Form__Vote = &$this->__getForm('Vote_'.$this->__Language);

         // generate link
         $Link = frontcontrollerLinkHandler::generateLink($_SERVER['REQUEST_URI'],array('voteview' => ''));

         // save if form is sent
         if($Form__Vote->get('isSent') == true){

            // get model
            $Model = &Singleton::getInstance('APFModel');

            // get the id and language of the current page
            $PageID = $Model->getAttribute('page.id');
            $Language = $Model->getAttribute('page.language');

            // save voting result
            $_LOCALS = variablenHandler::registerLocal(array('Vote'));
            $this->__saveVoting($PageID.$Language,$_LOCALS['Vote']);

            // refere to result page
            header('Location: '.$Link);

          // end if
         }
         else{

            // insert form
            $this->setPlaceHolder('Content',$Form__Vote->transformForm());

            // display preface
            $Template__Preface = &$this->__getTemplate('Preface_'.$this->__Language);
            $this->setPlaceHolder('Preface',$Template__Preface->transformTemplate());

            // show article link
            $Template__ArticleLink = &$this->__getTemplate('ArticleLink_'.$this->__Language);
            $Template__ArticleLink->setPlaceHolder('ArticleLink',$Link);
            $this->setPlaceHolder('ArticleLink',$Template__ArticleLink->transformTemplate());

          // end else
         }

       // end function
      }

    // end function
   }
?>