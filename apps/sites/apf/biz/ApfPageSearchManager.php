<?php
namespace APF\sites\apf\biz;

use APF\core\database\AbstractDatabaseHandler;
use APF\core\logging\LogEntry;
use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\APFObject;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\SearchResult;
use APF\core\logging\Logger;
use \DateTime;

/**
 * @package APF\sites\apf\biz
 * @class ApfPageSearchManager
 *
 * Business component of the full text search feature.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.03.2008<br />
 */
class ApfPageSearchManager extends APFObject {

   /**
    * @param string $searchTerm Search term.
    * @return SearchResult[] List of search results for the given search term.
    */
   public function loadSearchResult($searchTerm) {

      /* @var $m ApfPageSearchManager */
      $m = & $this->getServiceObject('APF\sites\apf\data\FulltextsearchMapper');

      /* @var $l Logger */
      $l = & Singleton::getInstance('APF\core\logging\Logger');
      $l->logEntry('searchlog', 'SearchString: "' . $searchTerm . '"', LogEntry::SEVERITY_INFO);

      return array_merge(
         $m->loadSearchResult($searchTerm),
         $this->loadWikiSearchResults($searchTerm),
         $this->loadForumSearchResults($searchTerm)
      );

   }

   public function loadForumSearchResults($searchTerm) {

      $conn = $this->getForumConnection();

      $parts = explode(' ', $searchTerm);
      foreach ($parts as $key => $part) {
         $parts[$key] = trim($conn->escapeValue($part));
      }

      // 1) execute statement to gather search index ids
      $statement = 'SELECT
    `word_id`, `word_text`, `word_common`
FROM
    `de_phpbb3_search_wordlist`
WHERE
    `word_text` IN (\'' . implode(' ', $parts) . '\')
ORDER BY `word_count` ASC;';
      $result = $conn->executeTextStatement($statement);

      $wordsIds = array();
      while ($data = $conn->fetchData($result)) {
         $wordsIds[] = $data['word_id'];
      }

      // only go ahead if we have matching words
      if (count($wordsIds) === 0) {
         return array();
      }

      // 2) search matching words in posts
      // TODO introduce UNION SELECT
      $statement = 'SELECT
    p.`post_id`
FROM
    (`de_phpbb3_search_wordmatch` m0)
        LEFT JOIN
    `de_phpbb3_posts` p ON (m0.`post_id` = p.`post_id`)
WHERE
    m0.`word_id` IN(' . implode(', ', $wordsIds) . ')
GROUP BY p.`post_id` , p.`post_time`
ORDER BY p.`post_time` DESC;';
      $result = $conn->executeTextStatement($statement);

      $postIds = array();
      while ($data = $conn->fetchData($result)) {
         $postIds[] = $data['post_id'];
      }

      // only go ahead if we have matching posts
      if (count($postIds) === 0) {
         return array();
      }

      // 3) load post and forum data to display
      $statement = 'SELECT
    p.`post_id`,
    p.`topic_id`,
    f.`forum_id`,
    f.`forum_name`,
    t.`topic_title`,
    t.`topic_last_post_time`
FROM
    `de_phpbb3_posts` p
        LEFT JOIN
    `de_phpbb3_topics` t ON (p.`topic_id` = t.`topic_id`)
        LEFT JOIN
    `de_phpbb3_forums` f ON (p.`forum_id` = f.`forum_id`)
WHERE
    p.`post_id` IN (' . implode(', ', $postIds) . ')
ORDER BY p.`post_time` DESC;';
      $result = $conn->executeTextStatement($statement);

      $list = array();
      while ($data = $conn->fetchData($result)) {
         $list[] = $this->mapForumResultToDomainObject($data);
      }

      return $list;
   }

   public function loadWikiSearchResults($searchTerm) {

      $conn = $this->getWikiConnection();

      // build binary search term for MySQL
      $parts = explode(' ', $searchTerm);
      foreach ($parts as $key => $part) {
         $parts[$key] = trim($conn->escapeValue($part));
      }

      $statement = 'SELECT `page_id`, `page_touched`, `page_latest`, `page_title`
FROM `wiki_de_page`, `wiki_de_searchindex`
WHERE
   `page_id` = `si_page`
   AND
   MATCH(`si_text`) AGAINST (\'' . implode(' ', $parts) . '\' IN BOOLEAN MODE)
LIMIT 20';

      $result = $conn->executeTextStatement($statement);

      $list = array();

      while ($data = $conn->fetchData($result)) {
         $list[] = $this->mapWikiResultToDomainObject($data);
      }

      return $list;
   }

   /**
    * @return AbstractDatabaseHandler
    */
   private function getWikiConnection() {
      /* @var $cm \APF\core\database\ConnectionManager */
      $cm = $this->getServiceObject('APF\core\database\ConnectionManager');
      return $cm->getConnection('Wiki');
   }

   /**
    * @return AbstractDatabaseHandler
    */
   private function getForumConnection() {
      /* @var $cm \APF\core\database\ConnectionManager */
      $cm = $this->getServiceObject('APF\core\database\ConnectionManager');
      return $cm->getConnection('Forum');
   }

   /**
    * @param array $data The database call return value.
    * @return WikiSearchResult The result object.
    */
   protected function mapWikiResultToDomainObject(array $data) {
      $result = new WikiSearchResult();
      $result->setTitle(str_replace('_', ' ', $data['page_title']));
      $result->setLastModified(new \DateTime($data['page_touched']));
      $result->setPageId($data['page_title']);
      return $result;
   }

   /**
    * @param array $data The database call return value.
    * @return ForumSearchResult The result object.
    */
   protected function mapForumResultToDomainObject(array $data) {
      $result = new ForumSearchResult();
      $result->setTitle($data['forum_name'] . ' - ' . strlen($data['topic_title']) > 30 ? substr($data['topic_title'], 0, 30) . ' ...' : $data['topic_title']);
      $result->setLastModified(DateTime::createFromFormat('U', $data['topic_last_post_time']));
      $result->setForumId($data['forum_id']);
      $result->setTopicId($data['topic_id']);
      $result->setPostId($data['post_id']);
      return $result;
   }

}
