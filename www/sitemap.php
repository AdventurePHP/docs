<?php
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\ini\IniConfigurationProvider;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;
use APF\core\logging\Logger;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;

date_default_timezone_set('Europe/Berlin');

// pre-define the root path of the root class loader (if necessary)
$dir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
$apfClassLoaderRootPath = $dir . '/APF';
$apfClassLoaderConfigurationRootPath = $dir . '/config/APF';
include('../APF/core/bootstrap.php');

// Define class loader for documentation page resources
RootClassLoader::addLoader(new StandardClassLoader('DOCS', $dir . '/DOCS', $dir . '/config/DOCS'));

/* @var $l Logger */
$l = &Singleton::getInstance(Logger::class);
$l->setLogThreshold(Logger::$LOGGER_THRESHOLD_ALL);

// configure logger for database debug messages
$defaultWriter = $l->getLogWriter(
      Registry::retrieve('APF\core', 'InternalLogTarget')
);
$l->addLogWriter('mysqlx', clone $defaultWriter);
$l->addLogWriter('mysqli', clone $defaultWriter);
$l->addLogWriter('searchlog', clone $defaultWriter);

/* @var $iniProvider IniConfigurationProvider */
$iniProvider = ConfigurationManager::retrieveProvider('ini');
$iniProvider->setOmitConfigSubFolder(true);
$iniProvider->setOmitContext(true);

include('../DOCS/data/sitemap/sitemap.php');
