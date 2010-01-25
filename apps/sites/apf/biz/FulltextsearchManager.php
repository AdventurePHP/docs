<?php
   import('modules::pager::biz','PagerManagerFabric');
   import('sites::apf::biz','SearchResult');
   import('sites::apf::data','FulltextsearchMapper');
   import('core::logging','Logger');

   /**
    * @package sites::apf::biz
    * @class FulltextsearchManager
    *
    * Implementiert die Business-Schicht f�r die Volltextsuche.<br />
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.03.2008<br />
    */
   class FulltextsearchManager extends coreObject {

      function fulltextsearchManager() {
      }

      /**
       * @public
       *
       * L�d Ergebnis-Objekte gem�� einem Suchwort.<br />
       *
       * @param string $SearchString; Suchwort, oder mehrere W�rter per Space getrennt
       * @return array $SearchResults; Liste von Such-Ergebnis-Objekten
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 10.03.2008<br />
       */
      function loadSearchResult($SearchString) {
         $m = &$this->__getServiceObject('sites::apf::data','FulltextsearchMapper');

         // Suchwort protokollieren
         $l = &Singleton::getInstance('Logger');
         $l->logEntry('searchlog','SearchString: "'.$SearchString.'"','LOG');

         return $m->loadSearchResult($SearchString);

       // end function
      }

    // end class
   }
?>