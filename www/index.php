<?php
date_default_timezone_set('Europe/Berlin');
ob_start();

// pre-define the root path of the root class loader (if necessary)
$apfClassLoaderRootPath = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/APF';
include('../APF/core/bootstrap.php');

use APF\core\benchmark\BenchmarkTimer;
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
$defaultWriter = $l->getLogWriter(
   Registry::retrieve('APF\core', 'InternalLogTarget')
);
$l->addLogWriter('mysqlx', clone $defaultWriter);
$l->addLogWriter('mysqli', clone $defaultWriter);
$l->addLogWriter('searchlog', clone $defaultWriter);

// configure url rewriting feature
// 1. input and output filter
use APF\core\filter\InputFilterChain;
use APF\core\filter\ChainedUrlRewritingInputFilter;

InputFilterChain::getInstance()->prependFilter(new ChainedUrlRewritingInputFilter());

use APF\core\filter\OutputFilterChain;
use APF\core\filter\ChainedUrlRewritingOutputFilter;

OutputFilterChain::getInstance()->appendFilter(new ChainedUrlRewritingOutputFilter());

// 2. link scheme
use APF\tools\link\RewriteLinkScheme;
use APF\tools\link\LinkGenerator;

LinkGenerator::setLinkScheme(new RewriteLinkScheme());

// configure page values
Registry::register('APF\sites\apf', 'Releases.LocalDir', '...');
Registry::register('APF\sites\apf', 'Releases.BaseURL', 'http://files.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'ForumBaseURL', 'http://forum.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'WikiBaseURL', 'http://wiki.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'TrackerBaseURL', 'http://tracker.adventure-php-framework.org');

// special script kiddie error handler ;)
use APF\sites\apf\biz\errorhandler\LiveErrorHandler;
use APF\core\errorhandler\GlobalErrorHandler;

GlobalErrorHandler::registerErrorHandler(new LiveErrorHandler());

use APF\core\exceptionhandler\GlobalExceptionHandler;
use APF\sites\apf\biz\exceptionhandler\LiveExceptionHandler;

GlobalExceptionHandler::registerExceptionHandler(new LiveExceptionHandler());

// special output filter
OutputFilterChain::getInstance()->appendFilter(new ScriptletOutputFilter());

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