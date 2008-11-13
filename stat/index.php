<?php
   // set error messages to text only
   ini_set('html_errors','off');

   // include the page controller file (this is necessary to include the APF core layer)
   include_once('../apps/core/pagecontroller/pagecontroller.php');

   // import front controller
   import('core::frontcontroller','Frontcontroller');

   // create the front controller instance
   $fC = &Singleton::getInstance('Frontcontroller');

   // set current context and language
   $fC->set('Context','sites::apfdocupage');
   $fC->set('Language','de');

   // start the front controller
   $fC->start('3rdparty::statistics::pres::templates','statistics');

   // display benchmark report, if benchmarkreport=true is present in the URL
   if(isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true'){
      $T = &Singleton::getInstance('benchmarkTimer');
      echo $T->createReport();
    // end if
   }
?>