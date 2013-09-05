<?php
ini_set('session.cache_limiter', 'none');
date_default_timezone_set('Europe/Berlin');

$apfClassLoaderRootPath = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . '/APF';
require('../../APF/core/bootstrap.php');

use APF\core\frontcontroller\Frontcontroller;
use APF\core\singleton\Singleton;

$fC = Singleton::getInstance('APF\core\frontcontroller\Frontcontroller');
/* @var $fC Frontcontroller */
$fC->setContext('sites\apf');
$fC->start(null, null); // no template, because we do not need one!
