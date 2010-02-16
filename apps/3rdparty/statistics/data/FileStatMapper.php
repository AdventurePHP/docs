<?php
   class FileStatMapper extends APFObject {

      function createStatEntry($pageName,$pageLang,$requestURI,$day,$month,$year,$hour,$minute,$second,$userName,$sessionID,$browser,$clientLanguage,$os,$ipAddress,$dnsAddress,$referer,$userAgent){

         $file = './stat/'.date('Y_m_d').'_statistics.log';
         if(file_exists($file)){
            $fp = fopen();
         } else {

         }
         $insert = 'INSERT INTO statistics
                         (
                          PageName,
                          PageLanguage,
                          RequestURI,
                          Day,
                          Month,
                          Year,
                          Hour,
                          Minute,
                          Second,
                          UserName,
                          SessionID,
                          Browser,
                          ClientLanguage,
                          OS,
                          IPAddress,
                          DNSAddress,
                          Referer,
                          UserAgent
                         )
                         VALUES
                         (
                          \''.$pageName.'\',
                          \''.$pageLang.'\',
                          \''.$requestURI.'\',
                          \''.$day.'\',
                          \''.$month.'\',
                          \''.$year.'\',
                          \''.$hour.'\',
                          \''.$minute.'\',
                          \''.$second.'\',
                          \''.$userName.'\',
                          \''.$sessionID.'\',
                          \''.$Browser.'\',
                          \''.$clientLanguage.'\',
                          \''.$os.'\',
                          \''.$ipAddress.'\',
                          \''.$dnsAddress.'\',
                          \''.$referer.'\',
                          \''.$userAgent.'\'
                         );';

      }

   }
?>