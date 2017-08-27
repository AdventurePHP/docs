<?php
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\ini\IniConfigurationProvider;
use APF\core\frontcontroller\Frontcontroller;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;
use APF\core\singleton\Singleton;

ini_set('session.cache_limiter', 'none');
date_default_timezone_set('Europe/Berlin');

$dir = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
$apfClassLoaderRootPath = $dir . '/APF';
$apfClassLoaderConfigurationRootPath = $dir . '/config/APF';
include('../../APF/core/bootstrap.php');

// Define class loader for documentation page resources
RootClassLoader::addLoader(new StandardClassLoader('DOCS', $dir . '/DOCS', $dir . '/config/DOCS'));

/* @var $iniProvider IniConfigurationProvider */
$iniProvider = ConfigurationManager::retrieveProvider('ini');
$iniProvider->setOmitConfigSubFolder(true);
$iniProvider->setOmitContext(true);

$fC = Singleton::getInstance(Frontcontroller::class);
/* @var $fC Frontcontroller */
$fC->setContext('dummy');
$fC->start(null, null); // no template, because we do not need one!
