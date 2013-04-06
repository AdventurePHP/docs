<?php
namespace APF\sites\apf\pres\controller\external;

use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;

/**
 * @package sites::apf::pres::controller::external
 * @class NewWikiArticlesController
 *
 * Lists new articles within the wiki.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 05.12.2009<br />
 */
class NewWikiArticlesController extends BaseDocumentController {

   public function transformContent() {

      // get wiki database connection
      /* @var $cM ConnectionManager */
      $cM = & $this->getServiceObject('core::database', 'ConnectionManager');
      $wikiConn = & $cM->getConnection('Wiki');

      // get configuration from the registry
      $wikiBaseURL = Registry::retrieve('sites::apf', 'WikiBaseURL');

      // build select
      $tablePrefix = 'wiki_' . $this->language . '_';
      $select = 'SELECT
            ' . $tablePrefix . 'page.page_id AS id,
            ' . $tablePrefix . 'page.page_title AS title,
            (SELECT
                rev_timestamp
                FROM ' . $tablePrefix . 'revision
                WHERE
                  ' . $tablePrefix . 'page.page_latest = ' . $tablePrefix . 'revision.rev_id)
            AS timestamp,
            (SELECT
                rev_id
                FROM ' . $tablePrefix . 'revision
                WHERE
                  ' . $tablePrefix . 'page.page_latest = ' . $tablePrefix . 'revision.rev_id)
            AS revision
         FROM ' . $tablePrefix . 'page
         WHERE ' . $tablePrefix . 'page.page_title NOT LIKE \'%:%\'
         ORDER BY timestamp DESC
         LIMIT 10;';
      $result = $wikiConn->executeTextStatement($select);

      // get template and prefill it
      $article = & $this->getTemplate('article_' . $this->language);

      // create article list
      $buffer = (string)'';

      while ($data = $wikiConn->fetchData($result)) {

         // fill template
         $article->setPlaceHolder('name',
               '<a href="' . $wikiBaseURL . '/' . $this->language . '/' . $data['title']
                     . '" title="' . $data['title'] . '">' . $data['title'] . '</a>'
         );
         $article->setPlaceHolder('date', date('d.m.Y H:i:s', strtotime($data['timestamp'])));
         $article->setPlaceHolder('revision', $data['revision']);

         // add current article to list
         $buffer .= $article->transformTemplate();

      }

      // add post list to content
      $this->setPlaceHolder('articles', $buffer);

   }

}
