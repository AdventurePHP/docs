#!/cygdrive/d/PHP5/php
<?php
   // get files
   $Files = `ls content`;
   $FilesArray = split("\n",$Files);
   unset($FilesArray[count($FilesArray)-1]);

   // get categorykey
   $count = count($FilesArray);
   $fH = fopen('comment_mapping.txt','w+');
   for($i = 0; $i < $count; $i++){
      echo $FilesArray[$i]."\n";
      $CatKEY = `grep -i "categorykey=\"" content/$FilesArray[$i]`;
      $CatKEY = trim($CatKEY);
      if(empty($CatKEY)){
         echo 'no categorykey found!'."\n";
       // end if
      }
      else{
         preg_match('/categorykey="([a-z0-9_]+)"/i',$CatKEY,$Matches);

         if(isset($Matches[1])){
            echo 'categorykey: '.$Matches[1];
            $PageLang = substr($FilesArray[$i],2,2);
            $PageID = substr($FilesArray[$i],5,3);
            echo ', pageid: '.$PageID.', lang: '.$PageLang."\n";
            echo 'new categorykey: '.$PageLang.'_'.$PageID."\n";
            $select = 'UPDATE article_comments SET CategoryKey = \''.$PageLang.'_'.$PageID.'\' WHERE CategoryKey = \''.$Matches[1].'\';';
            fwrite($fH,$select."\r\n");
          // end if
         }
         else{
            echo $CatKEY."\n";
          // end else
         }

       // end else
      }
      echo '------------------------------'."\n";
      echo "\n";
    // end for
   }
   fclose($fH);
?>