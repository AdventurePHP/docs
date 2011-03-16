<?php
   import('sites::apf::biz','APFModel');

   /**
    * @package sites::apf::biz
    * @name UrlManager
    *
    * This tool let's you generate a APF docu page link by a
    * given page id (e.g. "072") and provides multilanguage
    * page titles.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.12.2009<br />
    */
   final class UrlManager extends APFObject {

      private $linkCache = array();
      private $titleCache = array();

      /**
       * @public
       *
       * Generates a APF docu page link.
       *
       * @param string $pageId The id of the target page.
       * @param string $lang The desired target language.
       * @return The desired link.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 22.12.2009<br />
       */
      public function generateLink($pageId,$lang){

         $t = &Singleton::getInstance('BenchmarkTimer');
         $id = 'UrlManager::generateLink('.$pageId.','.$lang.')';
         $t->start($id);

         // deliver cached content, if possible
         $hash = md5($pageId.$lang);
         if(isset($this->linkCache[$hash])){
            $t->stop($id);
            return $this->linkCache[$hash];
         }

         // setup the basic part of the link
         $pageIdent = (string)$pageId;
         $model = &Singleton::getInstance('APFModel');
         $urlLangIdent = $model->getUrlIdentifier($lang);

         // fetch the url name from the database using the fulltextsearch
         $sql = &$this->getConnection();
         $pageId = $sql->escapeValue($pageId); // avoid injections!
         $select = 'SELECT URLName
                    FROM search_articles
                    WHERE PageID = \''.$pageId.'\' AND Language = \''.$lang.'\'';
         $result = $sql->executeTextStatement($select);
         $data = $sql->fetchData($result);
         $urlName = $data['URLName'];

         if(!empty($urlName)) {
            $pageIdent .= '-'.$urlName;
         }

         $this->linkCache[$hash] = '/'.$urlLangIdent.'/'.$pageIdent;
         $t->stop($id);
         return $this->linkCache[$hash];
         
       // end function
      }

      /**
       * @public
       *
       * Returns the title of the page described by the given params.
       *
       * @param string $pageId The id of the target page.
       * @param string $lang The desired target language.
       * @return The page title
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 22.12.2009<br />
       */
      public function getPageTitle($pageId,$lang){

         $t = &Singleton::getInstance('BenchmarkTimer');
         $id = 'UrlManager::getPageTitle('.$pageId.','.$lang.')';
         $t->start($id);

         // deliver cached content, if possible
         $hash = md5($pageId,$lang);
         if(isset($this->titleCache[$hash])){
            $t->stop($id);
            return $this->titleCache[$hash];
         }

         // select title from the database
         $sql = &$this->getConnection();
         $pageId = $sql->escapeValue($pageId); // avoid injections!
         $select = 'SELECT Title
                    FROM search_articles
                    WHERE PageID = \''.$pageId.'\' AND Language = \''.$lang.'\'';
         $result = $sql->executeTextStatement($select);
         $data = $sql->fetchData($result);
         $title = $data['Title'];

         $this->linkCache[$hash] = $title;
         $t->stop($id);
         return $this->linkCache[$hash];
         
       // end function
      }

      /**
       * Initialize database connection for current usage.
       *
       * @return AbstractDatabaseHandler The database connection.
       */
      private function &getConnection(){
         $cM = &$this->getServiceObject('core::database','ConnectionManager');
         return $cM->getConnection('FulltextSearch');
      }

   }
?>