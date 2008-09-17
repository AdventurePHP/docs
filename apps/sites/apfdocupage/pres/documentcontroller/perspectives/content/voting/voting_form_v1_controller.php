<?php
   import('tools::variablen','variablenHandler');
   import('sites::apfdocupage::biz','APFModel');
   import('tools::link','frontcontrollerLinkHandler');
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content::voting','voting_base_controller');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content::voting
   *  @class voting_form_v1_controller
   *
   *  Documentcontroller für das Voting-Formular.<br />
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
      *  Implementiert die abstrakte Methode transformContent().<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      */
      function transformContent(){

         // Formular holen
         $Form__Vote = &$this->__getForm('Vote_'.$this->__Language);

         // Link generieren
         $Link = frontcontrollerLinkHandler::generateLink($_SERVER['REQUEST_URI'],array('voteview' => ''));

         // Speichern
         if($Form__Vote->get('isSent') == true){

            // Model holen
            $Model = &$this->__getServiceObject('sites::demosite::biz','DemositeModel');

            // Aktuelle Seite holen
            $PageParams = $Model->getAttribute('ReqParamName');
            $RequestParameter = $PageParams[$this->__Language];
            $_LOCALS = variablenHandler::registerLocal(array($RequestParameter,'Vote'));
            $CurrentPage = $_LOCALS[$RequestParameter];

            // Voting speichern
            $this->__saveVoting($CurrentPage.$this->__Language,$_LOCALS['Vote']);

            // Auf AnzeigeSeite weiterleiten
            header('Location: '.$Link);

          // end if
         }
         else{

            // Formular einsetzen
            $this->setPlaceHolder('Content',$Form__Vote->transformForm());

            // Preface anzeigen
            $Template__Preface = &$this->__getTemplate('Preface_'.$this->__Language);
            $this->setPlaceHolder('Preface',$Template__Preface->transformTemplate());

            // Artikellink anzeigen
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