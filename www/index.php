<?php
use APF\core\benchmark\BenchmarkTimer;
use APF\core\benchmark\HtmlReport;
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\ini\IniConfigurationProvider;
use APF\core\database\config\StatementConfigurationProvider;
use APF\core\errorhandler\GlobalErrorHandler;
use APF\core\exceptionhandler\GlobalExceptionHandler;
use APF\core\filter\ChainedUrlRewritingInputFilter;
use APF\core\filter\ChainedUrlRewritingOutputFilter;
use APF\core\filter\InputFilterChain;
use APF\core\filter\OutputFilterChain;
use APF\core\frontcontroller\FrontController;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;
use APF\core\logging\Logger;
use APF\core\pagecontroller\Document;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use APF\tools\link\LinkGenerator;
use APF\tools\link\RewriteLinkScheme;
use DOCS\biz\errorhandler\LiveErrorHandler;
use DOCS\biz\exceptionhandler\LiveExceptionHandler;
use DOCS\pres\filter\output\ScriptletOutputFilter;
use DOCS\pres\http\HttpCacheManager;
use DOCS\pres\taglib\AntiSpamFormTag;
use DOCS\pres\taglib\ContentDisplayTag;
use DOCS\pres\taglib\DocumentationLinkTag;
use DOCS\pres\taglib\DocumentationTitleTag;
use DOCS\pres\taglib\GenericHighlightTag;
use DOCS\pres\taglib\InternalLinkTag;
use DOCS\pres\taglib\NewsDisplayTag;
use DOCS\pres\taglib\QuickNaviContentTag;
use DOCS\pres\taglib\SidebarDisplayTag;
use DOCS\pres\taglib\TrackingTag;
use DOCS\pres\taglib\VersionSelectionTag;

date_default_timezone_set('Europe/Berlin');
ob_start();

// pre-define the root path of the root class loader (if necessary)
$dir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
$apfClassLoaderRootPath = $dir . '/APF';
$apfClassLoaderConfigurationRootPath = $dir . '/config/APF';
include('../APF/core/bootstrap.php');

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

/* @var $l Logger */
$l = Singleton::getInstance(Logger::class);
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
InputFilterChain::getInstance()->prependFilter(new ChainedUrlRewritingInputFilter());
OutputFilterChain::getInstance()->appendFilter(new ChainedUrlRewritingOutputFilter());

// 2. link scheme
LinkGenerator::setLinkScheme(new RewriteLinkScheme());

// configure page values
Registry::register('DOCS', 'Releases.LocalDir', '../files');
Registry::register('DOCS', 'FilesBaseURL', '/files');
Registry::register('DOCS', 'ForumBaseURL', '/forum');
Registry::register('DOCS', 'WikiBaseURL', '/wiki');
Registry::register('DOCS', 'TrackerBaseURL', '/tracker');

// special script kiddie error handler ;)
GlobalErrorHandler::registerErrorHandler(new LiveErrorHandler());
GlobalExceptionHandler::registerExceptionHandler(new LiveExceptionHandler());

// special output filter
OutputFilterChain::getInstance()->appendFilter(new ScriptletOutputFilter());

// send HTTP caching headers
HttpCacheManager::sendHtmlCacheHeaders();

// Register tags to avoid performance overhead
Document::addTagLib(DocumentationLinkTag::class, 'doku', 'link');
Document::addTagLib(DocumentationTitleTag::class, 'doku', 'title');
Document::addTagLib(GenericHighlightTag::class, 'gen', 'highlight');
Document::addTagLib(InternalLinkTag::class, 'int', 'link');
Document::addTagLib(VersionSelectionTag::class, 'version', 'selector');
Document::addTagLib(QuickNaviContentTag::class, 'html', 'quicknavi');
Document::addTagLib(ContentDisplayTag::class, 'html', 'content');
Document::addTagLib(SidebarDisplayTag::class, 'sidebar', 'importdesign');
Document::addTagLib(NewsDisplayTag::class, 'news', 'appendnode');
Document::addTagLib(AntiSpamFormTag::class, 'form', 'antispam');
Document::addTagLib(TrackingTag::class, 'tracking', 'pixel');

Document::addTemplateExpression(\DOCS\pres\expression\BaseUrlExpression::class);

/* @var $fC FrontController */
$fC = Singleton::getInstance(FrontController::class);
$fC->setContext('dummy');
$fC->setLanguage('de');

$fC->addAction('DOCS\biz', 'setModel');

echo $fC->start('DOCS\pres\templates', 'main');

// display benchmark report on demand
/* @var $t BenchmarkTimer */
$t = Singleton::getInstance(BenchmarkTimer::class);
if (isset($_REQUEST['benchmarkreport']) && $_REQUEST['benchmarkreport'] == 'true') {
   $report = new HtmlReport();
   $report->setCriticalTime(0.15);
   echo $t->createReport($report);
}
echo '<!-- rendering time: ' . $t->getTotalTime() . 's -->';

ob_end_flush();