<?php
use APF\core\frontcontroller\Frontcontroller;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;

include('../APF/core/bootstrap.php');

RootClassLoader::addLoader(new StandardClassLoader('DEV', dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/DEV'));

/* @var $fC Frontcontroller */
$fC = & \APF\core\singleton\Singleton::getInstance('APF\core\frontcontroller\Frontcontroller');
$fC->setContext('sites\apf');
$fC->setLanguage('de');

echo $fC->start('DEV\wizard\templates', 'main');
