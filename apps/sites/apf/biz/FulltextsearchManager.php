<?php
namespace APF\sites\apf\biz;

use APF\core\pagecontroller\APFObject;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\SearchResult;
use APF\core\logging\Logger;

/**
 * @package APF\sites\apf\biz
 * @class FulltextsearchManager
 *
 * Business component of the full text search feature.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.03.2008<br />
 */
class FulltextsearchManager extends APFObject {

   /**
    * @public
    *
    * L�d Ergebnis-Objekte gem�� einem Suchwort.<br />
    *
    * @param string $SearchString Suchwort, oder mehrere W�rter per Space getrennt
    * @return SearchResult[] List of search results for the given search term.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.03.2008<br />
    */
   public function loadSearchResult($SearchString) {
      /* @var $m FulltextsearchManager */
      $m = & $this->getServiceObject('APF\sites\apf\data\FulltextsearchMapper');

      /* @var $l Logger */
      $l = & Singleton::getInstance('APF\core\logging\Logger');
      $l->logEntry('searchlog', 'SearchString: "' . $SearchString . '"', 'LOG');

      return $m->loadSearchResult($SearchString);

   }

}
