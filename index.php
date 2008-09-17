<?php
   // Fehlermeldung konfigurieren (für Livebetrieb)
   ini_set('html_errors','off');

   // PageController einbinden (muss initial immer gemacht werden)
   include_once('./apps/core/pagecontroller/pagecontroller.php');

   // Registry ziehen und Standardwerte korrigieren
   $Reg = &Singleton::getInstance('Registry');
   $Reg->register('sites::apfdocupage','sitemap.env','dev');

   //$Reg->register('apf::core','Environment','TESTSERVER');
   //$Reg->register('apf::core','URLRewriting',true);
   //$Reg->register('apf::core','LogDir','D:/Apache2/htdocs/www/adventure-php-framework.org/logs');
   //$Reg->register('apf::core','URLBasePath','http://localhost/adventure-php-framework.org');

   // FrontController einbinden
   import('core::frontcontroller','Frontcontroller');

   // Frontcontroller erzeugen
   $fC = &Singleton::getInstance('Frontcontroller');

   // Context und Sprache setzen
   $fC->set('Context','sites::apfdocupage');
   $fC->set('Language','de');

   // Sprachumschalter-Action registrieren
   $fC->registerAction('sites::apfdocupage::biz','setModel');

   // mapOldRequests-Action registrieren
   //$fC->registerAction('sites::demosite::biz','mapOldRequests');

   // Frontcontroller starten
   $fC->start('sites::apfdocupage::pres::templates','apfdocupage');

   // Benchmark-Report ausgeben, falls benchmarkreport=true in der URL enthalten ist
   if(isset($_REQUEST['benchmarkreport'])){
      if($_REQUEST['benchmarkreport'] == 'true'){
         $T = &Singleton::getInstance('benchmarkTimer');
         echo $T->createReport();
       // end if
      }
    // end if
   }
?>