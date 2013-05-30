<?php
namespace APF\sites\apf\biz;

use APF\core\database\AbstractDatabaseHandler;
use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\APFObject;
use \DateTime;

/**
 * @package APF\sites\apf\biz
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

      $config = $this->getConfiguration('APF\sites\apf\biz', 'fulltextsearch.ini');

      /* @var $cM ConnectionManager */
      $cM = & $this->getServiceObject('APF\core\database\ConnectionManager');
      $SQL = & $cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      // make search string save (sql injection)
      $searchString = $SQL->escapeValue($searchTerm);

      // do synonym mapping
      $synonyms = $this->getConfiguration('APF\sites\apf\biz', 'fulltextsearch_synonyms.ini');
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

      return $results;
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

}
