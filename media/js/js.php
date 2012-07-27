<?php
ini_set('session.cache_limiter', 'none');
date_default_timezone_set('Europe/Berlin');

require('../../apps/core/pagecontroller/pagecontroller.php');
import('core::frontcontroller', 'Frontcontroller');

$fC = Singleton::getInstance('Frontcontroller');
/* @var $fC Frontcontroller */
$fC->setContext('sites::apf');
$fC->start('foo::bar', 'baz'); // pseudo template, because we do not need one!
