<?php
date_default_timezone_set('Europe/Berlin');
ob_start();

// pre-define the root path of the root class loader (if necessary)
$apfClassLoaderRootPath = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/APF';
include('../APF/core/bootstrap.php');

use APF\core\singleton\Singleton;
use APF\core\logging\Logger;
use APF\core\registry\Registry;

/* @var $l Logger */
$l = & Singleton::getInstance('APF\core\logging\Logger');
$l->setLogThreshold(Logger::$LOGGER_THRESHOLD_ALL);

$stdWriter = $l->getLogWriter(
   Registry::retrieve('APF\core', 'InternalLogTarget')
);
$l->addLogWriter('fulltextsearchindexer', clone $stdWriter);
$l->addLogWriter('mysqlx', clone $stdWriter);

// configure page values (to avoid rendering errors during index creation!)
Registry::register('APF\sites\apf', 'Releases.LocalDir', 'D:/Entwicklung/Dokumentation/Build/RELEASES');
Registry::register('APF\sites\apf', 'Releases.BaseURL', 'http://files.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'ForumBaseURL', 'http://forum.adventure-php-framework.org');
Registry::register('APF\sites\apf', 'WikiBaseURL', 'http://wiki.adventure-php-framework.org');

// special output filter (to filter scriptlet tags out of the index!)
use APF\core\filter\OutputFilterChain;
use APF\sites\apf\pres\filter\output\ScriptletOutputFilter;

OutputFilterChain::getInstance()->appendFilter(new ScriptletOutputFilter());

use APF\core\loader\RootClassLoader;

$loader = RootClassLoader::getLoaderByVendor('APF');

include($loader->getRootPath() . '/sites/apf/data/indexer/indexer.php');
