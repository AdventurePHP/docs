<?php
namespace DEV\wizard\controller;

use APF\core\filter\OutputFilterChain;
use APF\core\logging\Logger;
use APF\core\pagecontroller\BaseDocumentController;
use APF\core\pagecontroller\Document;
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use APF\tools\form\taglib\SelectBoxTag;
use DOCS\data\indexer\FulltextsearchIndexer;
use DOCS\pres\filter\output\ScriptletOutputFilter;
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
use Exception;

class SearchController extends BaseDocumentController {

   const CREATE_INDEX = 'index';
   const IMPORT_ARTICLES = 'articles';

   public function transformContent() {

      $form = &$this->getForm('index-maintenance');

      if ($form->isSent() && $form->isValid()) {

         /* @var $control SelectBoxTag */
         $control = $form->getFormElementByName('part');

         $value = $control->getValue()->getValue();
         if ($value == 'all' || $value == 'articles') {
            try {
               $this->executeIndexer(self::IMPORT_ARTICLES);
            } catch (Exception $e) {
               $tmpl = $this->getTemplate('error');
               $tmpl->setPlaceHolder('exception-message', $e->getMessage());
               $tmpl->transformOnPlace();

               return;
            }
         }

         if ($value == 'all' || $value == 'index') {
            try {
               $this->executeIndexer(self::CREATE_INDEX);
            } catch (Exception $e) {
               $tmpl = $this->getTemplate('error');
               $tmpl->setPlaceHolder('exception-message', $e->getMessage());
               $tmpl->transformOnPlace();

               return;
            }
         }

         $this->getTemplate('success')->transformOnPlace();

      } else {
         $form->transformOnPlace();
      }

   }

   private function executeIndexer($job) {

      /* @var $l Logger */
      $l = &Singleton::getInstance('APF\core\logging\Logger');
      $l->setLogThreshold(Logger::$LOGGER_THRESHOLD_ALL);

      $stdWriter = $l->getLogWriter(
            Registry::retrieve('APF\core', 'InternalLogTarget')
      );
      $l->addLogWriter('fulltextsearchindexer', clone $stdWriter);
      $l->addLogWriter('mysqli', clone $stdWriter);
      $l->addLogWriter('mysqlx', clone $stdWriter);
      $l->addLogWriter('searchlog', clone $stdWriter);

      // set page configuration to production values
      Registry::register('DOCS', 'Releases.LocalDir', './files');
      Registry::register('DOCS', 'FilesBaseURL', '/files');
      Registry::register('DOCS', 'ForumBaseURL', '/forum');
      Registry::register('DOCS', 'WikiBaseURL', '/wiki');
      Registry::register('DOCS', 'TrackerBaseURL', '/tracker');

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

      // special output filter (to filter scriptlet tags out of the index!)
      OutputFilterChain::getInstance()->appendFilter(new ScriptletOutputFilter());

      // configure php params to allow indexer to run a couple of minutes
      ini_set('html_errors', 'off');
      error_reporting(E_ALL);
      ini_set('display_errors', 'On');
      set_time_limit(0);
      ini_set('memory_limit', '300M');

      $indexer = new FulltextsearchIndexer();
      $indexer->setContext($this->getContext());
      $indexer->setContentFolder('./DOCS/pres/content');

      // execute desired job
      if ($job == self::CREATE_INDEX) {
         $indexer->createIndex();
      } else {
         $indexer->importArticles();
      }

   }

} 