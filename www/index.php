<?php
// redirect madmind.net to avoid duplicate content with google!
if ($_SERVER['SERVER_NAME'] == 'madmind.net') {
   header('Location: http://adventure-php-framework.org', true, 301);
   exit(0);
}

//ini_set('session.cache_limiter','none');
date_default_timezone_set('Europe/Berlin');
ob_start();

// pre-define the root path of the root class loader (if necessary)
$apfClassLoaderRootPath = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/apps';
include('../apps/core/bootstrap.php');

use APF\core\benchmark\BenchmarkTimer;
use APF\core\filter\OutputFilterChain;
use APF\core\frontcontroller\Frontcontroller;
use APF\core\logging\Logger;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use APF\sites\apf\pres\filter\output\ScriptletOutputFilter;
use APF\sites\apf\pres\http\HttpCacheManager;

/* @var $l Logger */
$l = & Singleton::getInstance('APF\core\logging\Logger');
$l->setLogThreshold(Logger::$LOGGER_THRESHOLD_ALL);

// configure logger for database debug messages
$dbWriter = clone $l->getLogWriter(
   Registry::retrieve('APF\core', 'InternalLogTarget')
);
$l->addLogWriter('mysqlx', $dbWriter);

// configure page values
Registry::register('APF\core', 'URLRewriting', true);
Registry::register('APF\sites\apf', 'Releases.LocalDir', 'D:/Entwicklung/Dokumentation/Build/RELEASES');
Registry::register('APF\sites\apf', 'Releases.BaseURL', 'http://files.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'ForumBaseURL', 'http://forum.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'WikiBaseURL', 'http://wiki.adventure-php-framework.org');

// special script kiddie error handler ;)
/*import('sites::apf::biz::errorhandler', 'LiveErrorHandler');
import('sites::apf::biz::exceptionhandler', 'LiveExceptionHandler');
GlobalErrorHandler::registerErrorHandler(new LiveErrorHandler());
GlobalExceptionHandler::registerExceptionHandler(new LiveExceptionHandler());*/

// special output filter
OutputFilterChain::getInstance()->appendFilter(new ScriptletOutputFilter());

// register downloads environment
Registry::register('APF\sites\apf', 'sitemap.env', 'dev');

// send HTTP caching headers
HttpCacheManager::sendHtmlCacheHeaders();

$fC = Singleton::getInstance('APF\core\frontcontroller\Frontcontroller');
/* @var $fC Frontcontroller */
$fC->setContext('sites\apf');
$fC->setLanguage('de');

$fC->registerAction('APF\sites\apf\biz', 'setModel');

echo $fC->start('APF\sites\apf\pres\templates', 'main');

// display benchmark report on demand
/* @var $t BenchmarkTimer */
$t = Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
if (isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true') {
   echo $t->createReport();
}
echo '<!-- rendering time: ' . $t->getTotalTime() . 's -->';

ob_end_flush();