<?php
   // redirect madmind.net to avoid duplicate content with google!
   if($_SERVER['SERVER_NAME'] == 'madmind.net'){
      header('Location: http://adventure-php-framework.org',true,301);
      exit(0);
   }

   //ini_set('session.cache_limiter','none');
   date_default_timezone_set('Europe/Berlin');
   if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') > 0 || substr_count($_SERVER['HTTP_ACCEPT_ENCODING'],'deflate') > 0)){
      ob_start('ob_gzhandler');
   }
   else {
      ob_start();
   }
   ini_set('html_errors','off');
   include('../apps/core/pagecontroller/pagecontroller.php');
   import('core::frontcontroller','Frontcontroller');

   // configure page values
   $reg = &Singleton::getInstance('Registry');
   $reg->register('apf::core','URLRewriting',true);
   $reg->register('sites::apf','Releases.LocalDir','D:/Entwicklung/Dokumentation/Build/RELEASES');
   $reg->register('sites::apf','Releases.BaseURL','http://files.adventure-php-framework.org');
   $reg->register('sites::apf','ForumBaseURL','http://forum.adventure-php-framework.org');
   $reg->register('sites::apf','WikiBaseURL','http://wiki.adventure-php-framework.org');

   // special script kiddie error handler ;)
   /*$reg->register(
      'apf::core',
      'ErrorHandler',
      new ErrorHandlerDefinition(
         'sites::apf::biz::errorhandler',
         'LiveErrorHandler'
      )
   );*/

   // special output filter
   $reg->register(
      'apf::core::filter',
      'OutputFilter',
      new FilterDefinition('sites::apf::pres::filter::output','ScriptletOutputFilter')
   );

   // register downloads environment
   $reg->register('sites::apf','sitemap.env','dev');

   // send HTTP caching headers
   import('sites::apf::pres::http','HttpCacheManager');
   HttpCacheManager::sendHtmlCacheHeaders();

   $fC = Singleton::getInstance('Frontcontroller');
   $fC->setContext('sites::apf');
   $fC->setLanguage('de');

   $fC->registerAction('sites::apf::biz','setModel');

   $fC->start('sites::apf::pres::templates','main');

   // display benchmark report on demand
   if(isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true'){
      $t = &Singleton::getInstance('BenchmarkTimer');
      echo $t->createReport();
   }

   ob_end_flush();
?>