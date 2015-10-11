<?php
namespace DOCS\data\sitemap;

use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\APFObject;
use DOCS\biz\UrlManager;

class XmlSiteMapCreator extends APFObject {

   private $baseUrl = 'http://adventure-php-framework.org';

   public function createSitemap() {

      $config = $this->getConfiguration('DOCS\biz', 'fulltextsearch.ini');

      /* @var $cM ConnectionManager */
      $cM = &$this->getServiceObject(ConnectionManager::class);
      $sql = &$cM->getConnection($config->getSection('Database')->getValue('ConnectionKey'));

      $select = 'SELECT * FROM search_articles ORDER BY PageID ASC';
      $result = $sql->executeTextStatement($select);

      /* @var $urlMan UrlManager */
      $urlMan = &$this->getServiceObject(UrlManager::class);

      $buffer = (string) '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
      $buffer .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

      while ($data = $sql->fetchData($result)) {
         if ($data['PageID'] != '404') {
            $buffer .= '   <url>' . PHP_EOL;
            $buffer .= '      <loc>' . $this->baseUrl . $urlMan->generateLink($data['PageID'], $data['Language'], $data['Version']) . '</loc>' . PHP_EOL;
            $buffer .= '      <lastmod>' . date('c', strtotime($data['ModificationTimestamp'])) . '</lastmod>' . PHP_EOL;
            $buffer .= '      <changefreq>weekly</changefreq>' . PHP_EOL;
            $buffer .= '   </url>' . PHP_EOL;
         }
      }

      $buffer .= '</urlset>' . PHP_EOL;

      return $buffer;
   }

}
