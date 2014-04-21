<?php
namespace DOCS\biz;

use APF\core\benchmark\BenchmarkTimer;
use APF\core\database\AbstractDatabaseHandler;
use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\APFObject;
use APF\core\singleton\Singleton;
use DateTime;

/**
 * @package DOCS\biz
 * @class ApfSearchManager
 *
 * Business component of the full text search feature.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.03.2008<br />
 */
class ApfSearchManager extends APFObject {

   /**
    * @public
    *
    * Loads a list of search result objects according to the given search string.
    *
    * @param string $searchTerm one or more search strings.
    * @return PageSearchResult[] List of search result objects.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.03.2008<br />
    * Version 0.2, 19.10.2008 (Introduced synonym mapping)<br />
    * Version 0.3, 05.11.2008 (Added value escaping for the search string to avoid sql injections)<br />
    */
   public function loadSearchResult($searchTerm) {

      /* @var $t BenchmarkTimer */
      $t = Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'ApfSearchManager::loadSearchResult(' . $searchTerm . ')';
      $t->start($id);

      $config = $this->getConfiguration('DOCS\biz', 'fulltextsearch.ini');

      /* @var $cM ConnectionManager */
      $cM = & $this->getServiceObject('APF\core\database\ConnectionManager');
      $SQL = & $cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      // make search string save (sql injection)
      $searchString = $SQL->escapeValue($searchTerm);

      // do synonym mapping
      $synonyms = $this->getConfiguration('DOCS\biz', 'fulltextsearch_synonyms.ini');
      $section = $synonyms->getSection($this->language);

      foreach ($section->getValueNames() as $name) {
         $searchString = str_replace($name, $section->getValue($name), $searchString);
      }

      // split search strings
      $SearchStringArray = explode(' ', $searchString);

      // create where statement
      $count = count($SearchStringArray);
      $WHERE = array();
      if ($count > 1) {

         for ($i = 0; $i < $count; $i++) {
            $WHERE[] = 'search_word.Word LIKE \'%' . strtolower($SearchStringArray[$i]) . '%\'';
         }

      } else {
         $WHERE[] = 'search_word.Word LIKE \'%' . strtolower($searchString) . '%\'';
      }

      // create search statement
      $select = 'SELECT search_articles.*, search_index.*, search_word.* FROM search_articles
                       INNER JOIN search_index ON search_articles.ArticleID = search_index.ArticleID
                       INNER JOIN search_word ON search_index.WordID = search_word.WordID
                       WHERE ' . implode('OR ', $WHERE) . '
                       GROUP BY search_articles.ArticleID
                       ORDER BY search_index.WordCount DESC, search_articles.ModificationTimestamp DESC
                       LIMIT 20';

      // execute search statement
      $result = $SQL->executeTextStatement($select);

      // map results to domain objects
      $results = array();

      while ($data = $SQL->fetchData($result)) {
         $results[] = $this->mapPageResult2DomainObject($data);
      }

      $t->stop($id);
      return $results;
   }

   public function loadForumSearchResults($searchTerm) {

      /* @var $t BenchmarkTimer */
      $t = Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'ApfSearchManager::loadForumSearchResults(' . $searchTerm . ')';
      $t->start($id);

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
      $statement = 'SELECT
    p.`post_id`
FROM
    (`de_phpbb3_search_wordmatch` m0)
        LEFT JOIN
    `de_phpbb3_posts` p ON (m0.`post_id` = p.`post_id`)
WHERE
    m0.`word_id` IN(' . implode(', ', $wordsIds) . ')
GROUP BY p.`post_id` , p.`post_time`
ORDER BY p.`post_time` DESC';
      $result = $conn->executeTextStatement($statement);

      $postIds = array();
      while ($data = $conn->fetchData($result)) {
         if(!empty($data['post_id'])){
            $postIds[] = $data['post_id'];
         }
      }

      // only go ahead if we have matching posts
      if (count($postIds) === 0) {
         return array();
      }

      // 3) load post and forum data to display
      //    --> grouping by "p.`topic_id`" does the trick to display one topic only once
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
GROUP BY p.`topic_id`
ORDER BY p.`post_time` DESC
LIMIT 20';
      $result = $conn->executeTextStatement($statement);

      $list = array();
      while ($data = $conn->fetchData($result)) {
         $list[] = $this->mapForumResultToDomainObject($data);
      }

      $t->stop($id);
      return $list;
   }

   public function loadWikiSearchResults($searchTerm) {

      /* @var $t BenchmarkTimer */
      $t = Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'ApfSearchManager::loadWikiSearchResults(' . $searchTerm . ')';
      $t->start($id);

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

      $t->stop($id);
      return $list;
   }

   public function loadTrackerSearchResults($searchTerm) {

      /* @var $t BenchmarkTimer */
      $t = Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = 'ApfSearchManager::loadTrackerSearchResults(' . $searchTerm . ')';
      $t->start($id);

      $conn = $this->getTrackerConnection();

      $where = array();
      $parts = explode(' ', $searchTerm);
      foreach ($parts as $part) {
         $value = trim($conn->escapeValue($part));
         $where[] = 'summary LIKE \'%' . $value . '%\'';
         $where[] = 'mantis_bug_text_table.description LIKE \'%' . $value . '%\'';
         $where[] = 'mantis_bug_text_table.additional_information LIKE \'%' . $value . '%\'';
         $where[] = 'mantis_bugnote_text_table.note LIKE \'%' . $value . '%\'';
      }

      $select = 'SELECT DISTINCT mantis_bug_table.*
FROM mantis_bug_table
JOIN mantis_project_table ON mantis_project_table.id = mantis_bug_table.project_id
JOIN mantis_bug_text_table ON mantis_bug_table.bug_text_id = mantis_bug_text_table.id
LEFT JOIN mantis_bugnote_table ON mantis_bug_table.id = mantis_bugnote_table.bug_id
LEFT JOIN mantis_bugnote_text_table ON mantis_bugnote_table.bugnote_text_id = mantis_bugnote_text_table.id
WHERE
    mantis_project_table.enabled = 1
    AND (' . implode('OR ', $where) . ')
ORDER BY
    mantis_bug_table.sticky DESC,
    mantis_bug_table.last_updated DESC,
    mantis_bug_table.date_submitted DESC
LIMIT 20;';

      $result = $conn->executeTextStatement($select);

      $list = array();
      while ($data = $conn->fetchData($result)) {
         $list[] = $this->mapTrackerResultToDomainObject($data);
      }

      $t->stop($id);
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
    * @return AbstractDatabaseHandler
    */
   private function getTrackerConnection() {
      /* @var $cm \APF\core\database\ConnectionManager */
      $cm = $this->getServiceObject('APF\core\database\ConnectionManager');
      return $cm->getConnection('Tracker');
   }

   /**
    * @private
    *
    * Maps a database result set to a search result object.
    *
    * @param string[] $resultSet The database result set.
    * @return PageSearchResult The search result object.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.03.2008<br />
    * Version 0.2, 02.10.2008 (Added some new domain object attributes)<br />
    */
   private function mapPageResult2DomainObject(array $resultSet) {

      $searchResult = new PageSearchResult();

      if (isset($resultSet['PageID'])) {
         $searchResult->setPageId($resultSet['PageID']);
      }
      if (isset($resultSet['Title'])) {
         $searchResult->setTitle($resultSet['Title']);
      }
      if (isset($resultSet['Language'])) {
         $searchResult->setLanguage($resultSet['Language']);
      }
      if (isset($resultSet['ModificationTimestamp'])) {
         $searchResult->setLastModified(new \DateTime($resultSet['ModificationTimestamp']));
      }
      if (isset($resultSet['WordCount'])) {
         $searchResult->setWordCount($resultSet['WordCount']);
      }
      if (isset($resultSet['Word'])) {
         $searchResult->setIndexedWord($resultSet['Word']);
      }
      if (isset($resultSet['Version'])) {
         $searchResult->setVersionId($resultSet['Version']);
      }

      return $searchResult;
   }

   /**
    * @param array $data The database call return value.
    * @return WikiSearchResult The result object.
    */
   private function mapWikiResultToDomainObject(array $data) {
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
   private function mapForumResultToDomainObject(array $data) {
      $result = new ForumSearchResult();
      $result->setTitle($data['forum_name'] . ' - ' . strlen($data['topic_title']) > 30 ? substr($data['topic_title'], 0, 30) . ' ...' : $data['topic_title']);
      $result->setLastModified(DateTime::createFromFormat('U', $data['topic_last_post_time']));
      $result->setForumId($data['forum_id']);
      $result->setTopicId($data['topic_id']);
      $result->setPostId($data['post_id']);
      return $result;
   }

   private function mapTrackerResultToDomainObject(array $data) {
      $result = new TrackerSearchResult();
      $result->setTitle($data['summary']);
      $result->setLastModified(DateTime::createFromFormat('U', $data['last_updated']));
      $result->setPageId($data['id']);
      return $result;
   }

}
