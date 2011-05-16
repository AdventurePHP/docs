<?php
// redirect madmind.net to avoid duplicate content with google!
if ($_SERVER['SERVER_NAME'] == 'madmind.net') {
   header('Location: http://adventure-php-framework.org', true, 301);
   exit(0);
}

//ini_set('session.cache_limiter','none');
date_default_timezone_set('Europe/Berlin');
ob_start('ob_gzhandler');

ini_set('html_errors', 'off');
include('../apps/core/pagecontroller/pagecontroller.php');
import('core::frontcontroller', 'Frontcontroller');

// configure page values
Registry::register('apf::core', 'URLRewriting', true);
Registry::register('sites::apf', 'Releases.LocalDir', 'D:/Entwicklung/Dokumentation/Build/RELEASES');
Registry::register('sites::apf', 'Releases.BaseURL', 'http://files.adventure-php-framework.org');
Registry::register('sites::apf', 'ForumBaseURL', 'http://forum.adventure-php-framework.org');
Registry::register('sites::apf', 'WikiBaseURL', 'http://wiki.adventure-php-framework.org');

// special script kiddie error handler ;)
/* Registry::register(
  'apf::core',
  'ErrorHandler',
  new ErrorHandlerDefinition(
  'sites::apf::biz::errorhandler',
  'LiveErrorHandler'
  )
  );
  Registry::register(
  'apf::core',
  'ExceptionHandler',
  new ExceptionHandlerDefinition(
  'sites::apf::biz::exceptionhandler',
  'LiveExceptionHandler'
  )
  ); */

// special output filter
import('sites::apf::pres::filter::output', 'ScriptletOutputFilter');
OutputFilterChain::getInstance()->addFilter(new ScriptletOutputFilter());

// register downloads environment
Registry::register('sites::apf', 'sitemap.env', 'dev');

// send HTTP caching headers
import('sites::apf::pres::http', 'HttpCacheManager');
HttpCacheManager::sendHtmlCacheHeaders();

$fC = Singleton::getInstance('Frontcontroller');
$fC->setContext('sites::apf');
$fC->setLanguage('de');

$fC->registerAction('sites::apf::biz', 'setModel');

echo $fC->start('sites::apf::pres::templates', 'main');

// display benchmark report on demand
if (isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true') {
   echo Singleton::getInstance('BenchmarkTimer')->createReport();
}

ob_end_flush();
?>