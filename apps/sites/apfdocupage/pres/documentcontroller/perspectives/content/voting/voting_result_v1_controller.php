<?php
   import('tools::variablen','variablenHandler');
   import('sites::apfdocupage::biz','APFModel');
   import('tools::link','frontcontrollerLinkHandler');
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content::voting','voting_base_controller');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content::voting
   *  @class voting_form_v1_controller
   *
   *  Documentcontroller für die Ausgabe.<br />
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 17.05.2008<br />
   */
   class voting_result_v1_controller extends voting_base_controller
   {

      function voting_result_v1_controller(){
      }


      /**
      *  @module transformContent()
      *  @public
      *
      *  Implementiert die abstrakte Methode transformContent().<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      */
      function transformContent(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // Aktuelle Seite holen
         $PageParams = $Model->getAttribute('ReqParamName');
         $RequestParameter = $PageParams[$this->__Language];
         $_LOCALS = variablenHandler::registerLocal(array($RequestParameter));
         $CurrentPage = $_LOCALS[$RequestParameter];

         // Voting-Ergebnis ausgeben
         $Voting = $this->__getVoting($CurrentPage.$this->__Language);

         // Ausgabe erzeugen
         if($Voting['VotingCount'] == 0){

            // Template holen
            $Template__NoResult = &$this->__getTemplate('NoResult_'.$this->__Language);

            // Bewertungslink einsetzen
            $Link = frontcontrollerLinkHandler::generateLink($_SERVER['REQUEST_URI'],array('voteview' => 'form'));
            $Template__NoResult->setPlaceHolder('RankIt',$Link);

            // Ausgabe erzeugen
            $this->setPlaceHolder('Content',$Template__NoResult->transformTemplate());

          // end if
         }
         else{

            // Bild erzeugen
            $Template__Image = &$this->__getTemplate('Image_'.$this->__Language);
            $Image = $Template__Image->transformTemplate();

            // Bewertungstemplate holen
            $Template__Result = &$this->__getTemplate('Result_'.$this->__Language);

            // Bewertungslink einsetzen
            $Link = frontcontrollerLinkHandler::generateLink($_SERVER['REQUEST_URI'],array('voteview' => 'form'));
            $Template__Result->setPlaceHolder('RankLink',$Link);

            // Ausgabe vorbereiten
            $VotingResultFull = (int)$Voting['VotingResult'];
            $VotingResultHalf = substr($Voting['VotingResult'],strpos($Voting['VotingResult'],'.'));
            $Template__Result->setPlaceHolder('RankingNumber',$Voting['VotingResult']);
            $Template__Result->setPlaceHolder('UserCount',$Voting['VotingCount']);

            $Ranking = (string)'';
            for($i = 0; $i < $VotingResultFull; $i++){
               $Ranking .= $Image;
             // end for
            }
            if(strpos($Voting['VotingResult'],'.') !== false){
               $Template__Image_Half = &$this->__getTemplate('Image_Half_'.$this->__Language);
               $Ranking .= $Template__Image_Half->transformTemplate();
             // end if
            }

            // Ausgabe erzeugen
            $Template__Result->setPlaceHolder('Ranking',$Ranking);
            $this->setPlaceHolder('Content',$Template__Result->transformTemplate());

          // end else
         }

       // end function
      }

    // end function
   }
?>