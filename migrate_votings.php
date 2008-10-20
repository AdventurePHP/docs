<?php
   include_once('./apps/core/pagecontroller/pagecontroller.php');
   import('core::database','connectionManager');

   $cM = new connectionManager();
   $cM->set('Context','sites::apfdocupage');
   $SQL = &$cM->getConnection('FulltextSearch');

   $select = 'SELECT PageID,Language,MD5(CONCAT(CONCAT_WS(\'-\',PageID,URLName),Language)) as VotingKey FROM search_articles;';
   $result = $SQL->executeTextStatement($select);

   $voting_base_name = 'article_voting_';
   $voting_extension = '.txt';
   $files_dir = 'D:/Apache2/htdocs/www/apfdocupage/media/voting';

   while($data = $SQL->fetchData($result)){

      $VotingKey = $data['VotingKey'];
      $PageID = $data['PageID'];
      $Language = $data['Language'];

      if(file_exists($files_dir.'/'.$voting_base_name.$VotingKey.$voting_extension)){
         echo '<br />migrate file with old id "'.$VotingKey.'" to new file...';
         $NewVotingKey = md5($PageID.$Language);
         //echo '<br />copy('.$files_dir.'/'.$voting_base_name.$VotingKey.$voting_extension.','.$files_dir.'/'.$Language.'/'.$voting_base_name.$NewVotingKey.$voting_extension.');';
         copy($files_dir.'/'.$voting_base_name.$VotingKey.$voting_extension,$files_dir.'/'.$Language.'/'.$voting_base_name.$NewVotingKey.$voting_extension);
       // end if
      }
      else{
         echo '<br />file with voting key "'.$VotingKey.'" does not exist';
       // end else
      }

    // end while
   }
?>