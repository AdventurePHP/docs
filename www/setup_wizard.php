<?php
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\ini\IniConfigurationProvider;
use APF\core\database\config\StatementConfigurationProvider;
use APF\core\frontcontroller\Frontcontroller;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;

// pre-define the root path of the root class loader (if necessary)
$dir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
$apfClassLoaderRootPath = $dir . '/APF';
$apfClassLoaderConfigurationRootPath = $dir . '/config/APF';
include('../APF/core/bootstrap.php');

RootClassLoader::addLoader(new StandardClassLoader('DEV', $dir . '/DEV', $dir . '/config/DEV'));

// Define class loader for documentation page resources
RootClassLoader::addLoader(new StandardClassLoader('DOCS', $dir . '/DOCS', $dir . '/config/DOCS'));

/* @var $iniProvider IniConfigurationProvider */
$iniProvider = ConfigurationManager::retrieveProvider('ini');
$iniProvider->setOmitConfigSubFolder(true);
$iniProvider->setOmitContext(true);

// Configure statement configuration provider (required for pager etc.)
$sqlProvider = new StatementConfigurationProvider();
$sqlProvider->setOmitConfigSubFolder(true);
$sqlProvider->setOmitContext(true);
ConfigurationManager::registerProvider('sql', $sqlProvider);

/* @var $fC Frontcontroller */
$fC = & \APF\core\singleton\Singleton::getInstance('APF\core\frontcontroller\Frontcontroller');
$fC->setContext(null);
$fC->setLanguage('de');

echo $fC->start('DEV\wizard\templates', 'main');
