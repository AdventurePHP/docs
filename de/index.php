<?php
   // set error messages to text only
   ini_set('html_errors','off');

   // include the page controller file (this is necessary to include the APF core layer)
   include_once('../apps/core/pagecontroller/pagecontroller.php');

   // get registry to adapt the standard values
   $Reg = &Singleton::getInstance('Registry');
   $Reg->register('apf::core','URLBasePath','http://de.adventure-php-framework.org');
   $Reg->register('apf::core','URLRewriting',true);

   // define environment for the sitemap
   $Reg->register('sites::apfdocupage','sitemap.env','dev');

   // define forum base url for the home page's forum info box
   $Reg->register('sites::apfdocupage','ForumBaseURL','http://forum.adventure-php-framework.org');

   // define the release location
   $Reg->register('sites::apfdocupage','Releases.LocalDir','D:/Entwicklung/Dokumentation/Build/RELEASES');
   $Reg->register('sites::apfdocupage','Releases.BaseURL','http://files.adventure-php-framework.org');

   // import front controller
   import('core::frontcontroller','Frontcontroller');

   // create the front controller instance
   $fC = &Singleton::getInstance('Frontcontroller');

   // set current context and language
   $fC->set('Context','sites::apfdocupage');
   $fC->set('Language','de');

   // register the setModel action
   $fC->registerAction('sites::apfdocupage::biz','setModel');

   // register the stat action
   $fC->registerAction('sites::apfdocupage::biz','Stat');

   // start the front controller
   $fC->start('sites::apfdocupage::pres::templates','apfdocupage');

   // display benchmark report, if benchmarkreport=true is present in the URL
   if(isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true'){
      $T = &Singleton::getInstance('benchmarkTimer');
      echo $T->createReport();
    // end if
   }
?>