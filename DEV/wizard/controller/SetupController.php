<?php
namespace DEV\wizard\controller;

use APF\core\database\ConnectionManager;
use APF\core\loader\RootClassLoader;
use APF\core\pagecontroller\BaseDocumentController;
use APF\tools\form\taglib\SelectBoxTag;

class SetupController extends BaseDocumentController {

   public function transformContent() {

      $form = &$this->getForm('database-setup');

      if ($form->isSent() && $form->isValid()) {

         /* @var $database SelectBoxTag */
         $database = &$form->getFormElementByName('database');
         $value = $database->getValue()->getValue();

         if ($value == 'all' || $value == 'comments') {
            $this->setupComments();
         }

         if ($value == 'all' || $value == 'fulltext') {
            $this->setupFullText();
         }

         if ($value == 'all' || $value == 'stat') {
            $this->setupStat();
         }

         $this->getTemplate('success')->transformOnPlace();

      } else {
         $form->transformOnPlace();
      }

   }

   private function setupComments() {
      $conn = $this->getConnection('Comments');

      $loader = RootClassLoader::getLoaderByVendor('APF');
      $statement = file_get_contents($loader->getRootPath() . '/modules/comments/data/scripts/init_comments.sql');

      $conn->executeTextStatement($statement);
   }

   /**
    * @param string $name The name of the database connection.
    *
    * @return \APF\core\database\AbstractDatabaseHandler
    */
   private function getConnection($name) {
      /* @var $cM ConnectionManager */
      $cM = &$this->getServiceObject(ConnectionManager::class);

      return $cM->getConnection($name);
   }

   private function setupFullText() {
      $conn = $this->getConnection('FulltextSearch');

      $conn->executeTextStatement('CREATE TABLE IF NOT EXISTS `search_articles` (
  `ArticleID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) NOT NULL DEFAULT \'\',
  `PageID` char(3) NOT NULL DEFAULT \'\',
  `Version` varchar(3) NOT NULL,
  `ParentPage` char(3) NOT NULL DEFAULT \'\',
  `URLName` varchar(100) NOT NULL DEFAULT \'\',
  `Language` char(2) NOT NULL DEFAULT \'\',
  `FileName` varchar(100) NOT NULL DEFAULT \'\',
  `ModificationTimestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ArticleID`),
  KEY `Title` (`Title`),
  KEY `Language` (`Language`),
  KEY `Name` (`FileName`),
  KEY `ModificationTimestamp` (`ModificationTimestamp`),
  KEY `URLName` (`URLName`),
  KEY `PageID` (`PageID`),
  KEY `ParentPage` (`ParentPage`)
) DEFAULT CHARSET=utf8;');

      $conn->executeTextStatement('CREATE TABLE IF NOT EXISTS `search_index` (
  `IndexID` int(5) NOT NULL AUTO_INCREMENT,
  `WordID` int(5) NOT NULL DEFAULT \'0\',
  `ArticleID` int(5) NOT NULL DEFAULT \'0\',
  `WordCount` int(5) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`IndexID`),
  KEY `WordCount` (`WordCount`),
  KEY `WordID` (`WordID`,`ArticleID`)
) DEFAULT CHARSET=utf8;');

      $conn->executeTextStatement('CREATE TABLE IF NOT EXISTS `search_word` (
  `WordID` int(5) NOT NULL AUTO_INCREMENT,
  `Word` varchar(100) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`WordID`),
  UNIQUE KEY `Word` (`Word`)
) DEFAULT CHARSET=utf8;');

   }

   private function setupStat() {
      $conn = $this->getConnection('Stat');
      $conn->executeTextStatement('CREATE TABLE IF NOT EXISTS `statistics` (
  `PageName` varchar(60) NOT NULL DEFAULT \'\',
  `PageLanguage` varchar(2) NOT NULL DEFAULT \'\',
  `RequestURI` text NOT NULL,
  `Day` int(2) NOT NULL DEFAULT \'0\',
  `Month` int(2) NOT NULL DEFAULT \'0\',
  `Second` int(2) NOT NULL DEFAULT \'0\',
  `Year` int(4) NOT NULL DEFAULT \'0\',
  `Minute` int(2) NOT NULL DEFAULT \'0\',
  `Hour` int(2) NOT NULL DEFAULT \'0\',
  `UserName` varchar(40) NOT NULL DEFAULT \'\',
  `SessionID` varchar(40) NOT NULL DEFAULT \'\',
  `Browser` varchar(60) NOT NULL DEFAULT \'\',
  `ClientLanguage` varchar(40) NOT NULL DEFAULT \'\',
  `OS` varchar(60) NOT NULL DEFAULT \'\',
  `IPAddress` varchar(15) NOT NULL DEFAULT \'\',
  `UserAgent` text NOT NULL,
  `DNSAddress` varchar(40) NOT NULL DEFAULT \'\',
  `Referer` varchar(100) NOT NULL DEFAULT \'\',
  `STATIndex` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`STATIndex`),
  KEY `Day` (`Day`),
  KEY `Month` (`Month`),
  KEY `Year` (`Year`),
  KEY `Hour` (`Hour`),
  KEY `Minute` (`Minute`),
  KEY `Second` (`Second`),
  KEY `UserName` (`UserName`),
  KEY `SessionID` (`SessionID`),
  KEY `IPAddress` (`IPAddress`),
  KEY `DNSAdresse` (`DNSAddress`),
  KEY `DNSAddress` (`Browser`),
  KEY `OS` (`OS`),
  KEY `Referer` (`Referer`),
  KEY `PageLanguage` (`PageLanguage`)
) DEFAULT CHARSET=utf8;');
   }

} 