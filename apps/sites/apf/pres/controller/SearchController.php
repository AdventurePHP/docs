<?php
namespace APF\sites\apf\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;
use APF\sites\apf\biz\ApfSearchManager;
use APF\sites\apf\biz\ForumSearchResult;
use APF\sites\apf\biz\PageSearchResult;
use APF\sites\apf\biz\SearchResult;
use APF\sites\apf\biz\UrlManager;
use APF\sites\apf\biz\WikiSearchResult;
use APF\tools\link\LinkGenerator;
use APF\tools\link\Url;
use APF\tools\request\RequestHandler;
use APF\core\logging\LogEntry;
use APF\core\singleton\Singleton;
use APF\core\logging\Logger;

/**
 * @package APF\sites\apf\pres\controller
 * @class SearchController
 *
 * Implements the document controller for the search.html template.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 08.03.2008<br />
 * Version 0.2, 03.10.2008 (Ported the controller to the new site structure)<br />
 */
class SearchController extends BaseDocumentController {

   /**
    * @public
    *
    * Displays the search result.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 08.03.2008<br />
    * Version 0.2, 09.03.2008 (introduced business layer)<br />
    * Version 0.3, 16.03.2008 (introduced deactivation for unknown hosts)<br />
    * Version 0.4, 02.04.2008 (changed target of the search page. hence, this module is no more independent)<br />
    * Version 0.5, 13.06.2008 (changed target link for english results on german pages)<br />
    * Version 0.6, 03.10.2008 (Adapted to the new page structure; removed deactivation)<br />
    * Version 0.7,
    */
   public function transformContent() {

      // register search content
      $searchTerm = RequestHandler::getValue('search', '');

      // display form
      $form = & $this->getForm('SearchV2');
      $form->transformOnPlace();

      /* @var $l Logger */
      $l = & Singleton::getInstance('APF\core\logging\Logger');
      $l->logEntry('searchlog', 'SearchString: "' . $searchTerm . '"', LogEntry::SEVERITY_INFO);

      // display results
      if (strlen($searchTerm) >= 3) {

         /* @var $m ApfSearchManager */
         $m = & $this->getServiceObject('APF\sites\apf\biz\ApfSearchManager');

         $this->displayResultList($m->loadSearchResult($searchTerm), 'WebsiteResult');
         $this->displayResultList($m->loadWikiSearchResults($searchTerm), 'WikiResult');
         $this->displayResultList($m->loadForumSearchResults($searchTerm), 'ForumResult');

      }

   }

   /**
    * @param SearchResult[] $list The search results to display.
    * @param string $placeHolderName The name of the place holder to set the result list to.
    */
   private function displayResultList(array $list, $placeHolderName) {

      // load language config
      $config = $this->getConfiguration('APF\sites\apf\biz', 'language.ini');

      // initialize buffer
      $buffer = (string)'';

      // get template
      $template = & $this->getTemplate('Result');

      $count = count($list);

      for ($i = 0; $i < $count; $i++) {

         // display title
         $template->setPlaceHolder('Title', $this->getTitle($list[$i]));

         // display content language
         $resultLang = $config->getSection($this->language)->getValue('DisplayName.' . $list[$i]->getLanguage());
         $template->setPlaceHolder('Language', $resultLang);

         // display last modifying date
         $time = $list[$i]->getLastModified();
         $template->setPlaceHolder('LastMod', $time->format('d.m.Y, H:i:s'));

         // display permalink
         $template->setPlaceHolder('PermaLink', $this->getUrl($list[$i]));

         // add current result to list
         $buffer .= $template->transformTemplate();

      }

      // display "nothing found" if result count is null
      if ($count < 1) {

         // get template
         $templateNoSearchResult = & $this->getTemplate('NoSearchResult_' . $this->getLanguage());

         // add message to buffer
         $buffer .= $templateNoSearchResult->transformTemplate();

      }

      // display buffer
      $this->setPlaceHolder($placeHolderName, $buffer);

   }

   private function getUrl(SearchResult $result) {
      if ($result instanceof PageSearchResult) {
         /* @var $urlMan UrlManager */
         $urlMan = & $this->getServiceObject('APF\sites\apf\biz\UrlManager');
         $currentUrl = Url::fromCurrent(true);
         $baseUrl = $currentUrl->getScheme() . '://' . $currentUrl->getHost();
         $url = $urlMan->generateLink($result->getPageId(), $result->getLanguage());
         return $baseUrl . $url;
      } else if ($result instanceof WikiSearchResult) {
         return Registry::retrieve('APF\sites\apf', 'WikiBaseURL') . '/' . $result->getLanguage() . '/' . $result->getPageId();
      } else {
         /* @var $result ForumSearchResult */
         return Registry::retrieve('APF\sites\apf', 'ForumBaseURL') . '/viewtopic.php?f=' . $result->getForumId() . '&amp;t=' . $result->getTopicId();
      }
   }

   private function getTitle(SearchResult $result) {
      if ($result instanceof PageSearchResult) {
         /* @var $urlMan UrlManager */
         $urlMan = & $this->getServiceObject('APF\sites\apf\biz\UrlManager');
         return $urlMan->getPageTitle($result->getPageId(), $result->getLanguage());
      } else if ($result instanceof WikiSearchResult) {
         return $result->getTitle();
      } else {
         /* @var $result ForumSearchResult */
         return $result->getTitle();
      }
   }

}
