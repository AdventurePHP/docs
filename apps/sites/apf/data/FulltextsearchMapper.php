<?php
namespace APF\sites\apf\data;

use APF\core\benchmark\BenchmarkTimer;
use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\APFObject;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\PageSearchResult;

/**
 * @package APF\sites\apf\data
 * @class FulltextsearchMapper
 *
 * Implements the data layer of the APF docu page's fulltext search.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.03.2008<br />
 */
class FulltextsearchMapper extends APFObject {

   /**
    * @public
    *
    * Loads a list of search result objects according to the given search string.
    *
    * @param string $searchString one or more search strings.
    * @return PageSearchResult[] List of search result objects.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.03.2008<br />
    * Version 0.2, 19.10.2008 (Introduced synonym mapping)<br />
    * Version 0.3, 05.11.2008 (Added value escaping for the search string to avoid sql injections)<br />
    */
   public function loadSearchResult($searchString) {

      // start timer
      /* @var $t BenchmarkTimer */
      $t = & Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $t->start('fulltextsearchMapper::loadSearchResult()');

      // get configuration
      $config = $this->getConfiguration('APF\sites\apf\biz', 'fulltextsearch.ini');

      // get database connection
      /* @var $cM ConnectionManager */
      $cM = & $this->getServiceObject('APF\core\database\ConnectionManager');
      $SQL = & $cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      // make search string save (sql injection)
      $searchString = $SQL->escapeValue($searchString);

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
      $Results = array();

      while ($data = $SQL->fetchData($result)) {
         $Results[] = $this->mapSearchResult2DomainObject($data);
      }

      $t->stop('fulltextsearchMapper::loadSearchResult()');

      return $Results;
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
   private function mapSearchResult2DomainObject($resultSet) {

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

}
