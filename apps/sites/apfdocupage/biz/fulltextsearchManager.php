<?php
   import('modules::pager::biz','pagerManager');
   import('sites::apfdocupage::biz','searchResult');
   import('sites::apfdocupage::data','fulltextsearchMapper');
   import('core::logging','Logger');


   /**
   *  @package sites::apfdocupage::biz
   *  @class fulltextsearchManager
   *
   *  Implementiert die Business-Schicht für die Volltextsuche.<br />
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 10.03.2008<br />
   */
   class fulltextsearchManager extends coreObject
   {

      function fulltextsearchManager(){
      }


      /**
      *  @public
      *
      *  Läd Ergebnis-Objekte gemäß einem Suchwort.<br />
      *
      *  @param string $SearchString; Suchwort, oder mehrere Wörter per Space getrennt
      *  @return array $SearchResults; Liste von Such-Ergebnis-Objekten
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 10.03.2008<br />
      */
      function loadSearchResult($SearchString){

         // Mapper laden
         $M = &$this->__getServiceObject('sites::apfdocupage::data','fulltextsearchMapper');

         // Suchwort protokollieren
         $L = &Singleton::getInstance('Logger');
         $L->logEntry('searchlog','SearchString: "'.$SearchString.'"','LOG');

         // Ergebnisse laden
         return $M->loadSearchResult($SearchString);

       // end function
      }


      /**
      *  @public
      *
      *  Läd eine Liste der in der Seite vorhandenen Seiten.<br />
      *
      *  @return array $Pages; Liste von Seiten-Objekten
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 24.03.2008<br />
      *  Version 0.2, 20.10.2008 (Removed sitemap logging)<br />
      */
      function loadPages(){

         // Mapper laden
         $M = &$this->__getServiceObject('sites::apfdocupage::data','fulltextsearchMapper');

         // Suchwort protokollieren
         //$L = &Singleton::getInstance('Logger');
         //$L->logEntry('sitemap','Sitemap in language '.$this->__Language.' displayed!','LOG');

         // Ergebnisse laden
         return $M->loadPages($this->__Language);

       // end function
      }

    // end class
   }
?>