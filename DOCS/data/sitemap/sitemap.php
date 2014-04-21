<?php
/**
 * @file sitemap.php
 *
 * Script wrapper file for the sitemap service.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 25.01.2010<br />
 */

// configure some php params
ini_set('html_errors', 'off');
error_reporting(E_ALL);
ini_set('display_errors', 'On');
set_time_limit(0);
ini_set('memory_limit', '300M');

// include front controller
use DOCS\data\sitemap\XmlSiteMapCreator;

$xSC = new XmlSiteMapCreator();
$xSC->setContext('sites\apf');

$fH = fopen('sitemap.xml', 'w+');
fwrite($fH, $xSC->createSitemap());
fclose($fH);
