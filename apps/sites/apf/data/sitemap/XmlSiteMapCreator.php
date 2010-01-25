<?php
   class XmlSiteMapCreator extends coreObject {

      private $baseUrl = 'http://adventure-php-framework.org';
      
      public function createSitemap(){

         $config = &$this->__getConfiguration('sites::apf::biz','fulltextsearch');

         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $sql = &$cM->getConnection($config->getValue('Database','ConnectionKey'));

         $select = 'SELECT PageID,URLName,Language,ModificationTimestamp,Title FROM search_articles ORDER BY PageID ASC';
         $result = $sql->executeTextStatement($select);

         $urlMan = &$this->__getServiceObject('sites::apf::biz','UrlManager');
         $urlMan = new UrlManager();

         $buffer = (string)'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
         $buffer .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
         while($data = $sql->fetchData($result)){
            $buffer .= '   <url>'.PHP_EOL;
            $buffer .= '      <loc>'.$this->baseUrl.$urlMan->generateLink($data['PageID'],$data['Language']).'</loc>'.PHP_EOL;
            $buffer .= '      <lastmod>'.date('c',strtotime($data['ModificationTimestamp'])).'</lastmod>'.PHP_EOL;
            $buffer .= '      <changefreq>weekly</changefreq>'.PHP_EOL;
            $buffer .= '   </url>'.PHP_EOL;
         }

         $buffer .= '</urlset>'.PHP_EOL;
         return $buffer;
      }

    // end class
   }
?>