<?php
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\ini\IniConfigurationProvider;
use APF\core\database\config\StatementConfigurationProvider;
use APF\core\frontcontroller\Frontcontroller;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;
use APF\core\logging\Logger;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;

// pre-define the root path of the root class loader (if necessary)
$apfClassLoaderRootPath = __DIR__ . './APF';
$apfClassLoaderConfigurationRootPath = __DIR__ . '/config/APF';
include('./APF/core/bootstrap.php');

RootClassLoader::addLoader(new StandardClassLoader('DEV', __DIR__ . '/DEV', __DIR__ . '/config/DEV'));

// Define class loader for documentation page resources
RootClassLoader::addLoader(new StandardClassLoader('DOCS', __DIR__ . '/DOCS', __DIR__ . '/config/DOCS'));

/* @var $iniProvider IniConfigurationProvider */
$iniProvider = ConfigurationManager::retrieveProvider('ini');
$iniProvider->setOmitConfigSubFolder(true);
$iniProvider->setOmitContext(true);

// Configure statement configuration provider (required for pager etc.)
$sqlProvider = new StatementConfigurationProvider();
$sqlProvider->setOmitConfigSubFolder(true);
$sqlProvider->setOmitContext(true);
ConfigurationManager::registerProvider('sql', $sqlProvider);

/* @var $l Logger */
$l = Singleton::getInstance(Logger::class);
$l->setLogThreshold(Logger::$LOGGER_THRESHOLD_ALL);

// configure logger for database debug messages
$defaultWriter = $l->getLogWriter(
      Registry::retrieve('APF\core', 'InternalLogTarget')
);
$l->addLogWriter('mysqli', clone $defaultWriter);
$l->addLogWriter('searchlog', clone $defaultWriter);

/* @var $fC Frontcontroller */
$fC = Singleton::getInstance(Frontcontroller::class);
$fC->setContext('dummy');
$fC->setLanguage('de');

echo $fC->start('DEV\wizard\templates', 'main');
