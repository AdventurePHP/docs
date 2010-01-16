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

      /**
       * @public
       *
       * L�d eine Liste der in der Seite vorhandenen Seiten.<br />
       *
       * @return array $Pages; Liste von Seiten-Objekten
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 24.03.2008<br />
       * Version 0.2, 20.10.2008 (Removed sitemap logging)<br />
       */
      function loadPages() {
         $m = &$this->__getServiceObject('sites::apfdocupage::data','fulltextsearchMapper');
         return $m->loadPages($this->__Language);
       // end function
      }

    // end class
   }
?>