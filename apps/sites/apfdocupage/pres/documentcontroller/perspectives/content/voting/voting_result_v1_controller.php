<?php
   import('sites::apfdocupage::biz','APFModel');
   import('tools::link','frontcontrollerLinkHandler');
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content::voting','voting_base_controller');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content::voting
   *  @class voting_form_v1_controller
   *
   *  Implements the document controller for the voting result output.
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
      *  @public
      *
      *  Shows the voting result.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      *  Version 0.2, 18.09.2008 (Changed to new model behaviour)<br />
      */
      function transformContent(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // get the id and language of the current page
         $PageID = $Model->getAttribute('page.id');
         $Language = $Model->getAttribute('page.language');

         // get voting result for current page
         $Voting = $this->__getVoting($PageID.$Language);

         // create output
         if($Voting['VotingCount'] == 0){

            // get template
            $Template__NoResult = &$this->__getTemplate('NoResult_'.$this->__Language);

            // insert voting link
            $Link = frontcontrollerLinkHandler::generateLink($_SERVER['REQUEST_URI'],array('voteview' => 'form'));
            $Template__NoResult->setPlaceHolder('RankIt',$Link);

            // create output
            $this->setPlaceHolder('Content',$Template__NoResult->transformTemplate());

          // end if
         }
         else{

            // create image template
            $Template__Image = &$this->__getTemplate('Image_'.$this->__Language);
            $Image = $Template__Image->transformTemplate();

            // create voting template
            $Template__Result = &$this->__getTemplate('Result_'.$this->__Language);

            // insert voting link
            $Link = frontcontrollerLinkHandler::generateLink($_SERVER['REQUEST_URI'],array('voteview' => 'form'));
            $Template__Result->setPlaceHolder('RankLink',$Link);

            // prepare output
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

            // display output
            $Template__Result->setPlaceHolder('Ranking',$Ranking);
            $this->setPlaceHolder('Content',$Template__Result->transformTemplate());

          // end else
         }

       // end function
      }

    // end function
   }
?>