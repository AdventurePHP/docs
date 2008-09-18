<?php
   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content::voting
   *  @class voting_base_controller
   *
   *  Basic document controller for the voting module.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 17.05.2008<br />
   */
   class voting_base_controller extends baseController
   {

      function voting_base_controller(){
      }


      /**
      *  @private
      *
      *  Gibt das aktuelle Ergebnis des Votings zurück.<br />
      *
      *  @param string $ArticleKey; Schlüssel des Artikels
      *  @return string $ArticleVoting; Assoziatives Array mit den aktuellen Voting-Ergebnissen
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      */
      function __getVoting($ArticleKey){

         // Dateinamen erzeugen
         $File = $this->__getVotingFileName($ArticleKey);

         // Voting zurückliefern
         if(!file_exists($File)){
            return array('VotingCount' => 0,
                         'VotingResult' => 0
                         );
          // end if
         }
         else{
            $Voting = explode('|',file_get_contents($File));
            return array('VotingCount' => trim($Voting[0]),
                         'VotingResult' => trim($Voting[1])
                         );
          // end else
         }

       // end function
      }


      /**
      *  @private
      *
      *  Gibt den Namen des aktuellen Storage-Files für das Voting zurück.<br />
      *
      *  @param string $ArticleKey; Schlüssel des Artikels
      *  @param string $Vote; Aktuelles Voting
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      */
      function __saveVoting($ArticleKey,$Vote){

         // Voting ziehen
         $Voting = $this->__getVoting($ArticleKey);

         // Neues Voting berechnen
         $VotingCount = (float)$Voting['VotingCount'] + 1;
         if((int)$Voting['VotingCount'] == 0){
            $VotingResult = floatval($Vote);
          // end if
         }
         else{
            $VotingResult = round(floatval(((float)$Voting['VotingResult'] + (float)$Vote) / 2),2);
          // end else
         }

         // Ergebnis speichern
         $File = $this->__getVotingFileName($ArticleKey);
         $fH = fopen($File,'w+');
         fwrite($fH,strval($VotingCount).'|'.strval($VotingResult));
         fclose($fH);

       // end function
      }


      /**
      *  @private
      *
      *  Gibt den Namen des aktuellen Storage-Files für das Voting zurück.<br />
      *
      *  @param string $ArticleKey; Schlüssel des Artikels
      *  @return string $ArticleFileName; Datei, in der die Votes gespeichert sind
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.05.2008<br />
      *  Version 0.2, 21.06.2008 (Replaced APPS__LOG_PATH with a value from the registry)<br />
      */
      function __getVotingFileName($ArticleKey){

         // initialize log directory
         $Reg = &Singleton::getInstance('Registry');
         $LogDir = $Reg->retrieve('apf::core','LogDir');

         // return file name
         return $LogDir.'/article_voting_'.md5($ArticleKey).'.txt';

       // end function
      }

    // end function
   }
?>