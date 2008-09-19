<?php
   // set error messages to text only
   ini_set('html_errors','off');

   // include the page controller file (this is necessary to include the APF core layer)
   include_once('./apps/core/pagecontroller/pagecontroller.php');

   // get registry to adapt the standard values
   $Reg = &Singleton::getInstance('Registry');
   $Reg->register('sites::apfdocupage','sitemap.env','dev');

   // define the release location
   $Reg->register('sites::apfdocupage','Releases.LocalDir','D:/Entwicklung/Dokumentation/Build/RELEASES');
   $Reg->register('sites::apfdocupage','Releases.BaseURL','http://dev.adventure-php-framework.org/frontend/media/releases');

   // import front controller
   import('core::frontcontroller','Frontcontroller');

   // create the front controller instance
   $fC = &Singleton::getInstance('Frontcontroller');

   // set current context and language
   $fC->set('Context','sites::apfdocupage');
   $fC->set('Language','de');

   // register the setModel action
   $fC->registerAction('sites::apfdocupage::biz','setModel');

   // start the front controller
   $fC->start('sites::apfdocupage::pres::templates','apfdocupage');

   // display benchmark report, if benchmarkreport=true is present in the URL
   if(isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true'){
      $T = &Singleton::getInstance('benchmarkTimer');
      echo $T->createReport();
    // end if
   }
?>