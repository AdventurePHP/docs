<?php
namespace APF\sites\apf\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;
use APF\sites\apf\biz\FulltextsearchManager;
use APF\sites\apf\biz\UrlManager;
use APF\tools\request\RequestHandler;

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
    * Version 0.6, 03.10.2008 (Addapted to the new page structure; removed deactivation)<br />
    * Version 0.7,
    */
   public function transformContent() {

      // register search content
      $searchTerm = RequestHandler::getValue('search', '');

      // display form
      $form = &$this->getForm('SearchV2');
      $form->transformOnPlace();

      // display results
      if (strlen($searchTerm) >= 3) {

         // get manager
         /* @var $m FulltextsearchManager */
         $m = &$this->getServiceObject('APF\sites\apf\biz\FulltextsearchManager');

         // load results
         $searchResults = $m->loadSearchResult($searchTerm);

         // load language config
         $config = $this->getConfiguration('sites::apf::biz', 'language.ini');

         // initialize buffer
         $buffer = (string)'';

         // get template
         $template = &$this->getTemplate('Result');

         $count = count($searchResults);
         /* @var $urlMan UrlManager */
         $urlMan = &$this->getServiceObject('APF\sites\apf\biz\UrlManager');
         $baseUrl = Registry::retrieve('APF\core', 'URLBasePath');
         for ($i = 0; $i < $count; $i++) {

            // gather common information
            $url = $urlMan->generateLink($searchResults[$i]->getPageId(), $searchResults[$i]->getLanguage());
            $title = $urlMan->getPageTitle($searchResults[$i]->getPageId(), $searchResults[$i]->getLanguage());
            $link = $baseUrl . $url;

            // display title
            $template->setPlaceHolder('Title', $title);

            // display content language
            $resultLang = $config->getSection($this->language)->getValue('DisplayName.' . $searchResults[$i]->getLanguage());
            $template->setPlaceHolder('Language', $resultLang);

            // display last modifying date --> refactor!
            $time = strtotime($searchResults[$i]->getLastModified());
            $template->setPlaceHolder('LastMod', date('d.m.Y, H:i:s', $time));

            // display permalink
            $template->setPlaceHolder('PermaLink', $link);

            // add current result to list
            $buffer .= $template->transformTemplate();

         }

         // display "nothing found" if result count is null
         if ($count < 1) {

            // get template
            $templateNoSearchResult = &$this->getTemplate('NoSearchResult_' . $this->language);

            // add message to buffer
            $buffer .= $templateNoSearchResult->transformTemplate();

         }

         // display buffer
         $this->setPlaceHolder('Result', $buffer);

      }

   }

}
