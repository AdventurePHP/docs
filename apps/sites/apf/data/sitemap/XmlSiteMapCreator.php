<?php
class XmlSiteMapCreator extends APFObject {

   private $baseUrl = 'http://adventure-php-framework.org';

   public function createSitemap() {

      $config = $this->getConfiguration('sites::apf::biz', 'fulltextsearch.ini');

      $cM = &$this->getServiceObject('core::database', 'ConnectionManager');
      $sql = &$cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      $select = 'SELECT PageID,URLName,Language,ModificationTimestamp,Title FROM search_articles ORDER BY PageID ASC';
      $result = $sql->executeTextStatement($select);

      $urlMan = &$this->getServiceObject('sites::apf::biz', 'UrlManager');
      $urlMan = new UrlManager();

      $buffer = (string)'<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
      $buffer .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
      while ($data = $sql->fetchData($result)) {
         if ($data['PageID'] != '404') {
            $buffer .= '   <url>' . PHP_EOL;
            $buffer .= '      <loc>' . $this->baseUrl . $urlMan->generateLink($data['PageID'], $data['Language']) . '</loc>' . PHP_EOL;
            $buffer .= '      <lastmod>' . date('c', strtotime($data['ModificationTimestamp'])) . '</lastmod>' . PHP_EOL;
            $buffer .= '      <changefreq>weekly</changefreq>' . PHP_EOL;
            $buffer .= '   </url>' . PHP_EOL;
         }
      }

      $buffer .= '</urlset>' . PHP_EOL;
      return $buffer;
   }

}
