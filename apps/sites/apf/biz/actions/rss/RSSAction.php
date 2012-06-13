<?php
import('sites::apf::biz', 'APFModel');
import('tools::request', 'RequestHandler');

/**
 * @package sites::apf::biz::actions::rss
 * @class RSSAction
 *
 * Represents the front controller action to deliver the APF rss channel.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 28.01.2010<br />
 */
class RSSAction extends AbstractFrontcontrollerAction {

   private static $LANG = 'lang';
   private static $PROTOCOL_VERSION = 'proto';
   private static $CACHE_TIME_IN_MINUTES = 10080; // one week

   /**
    * @public
    *
    * Displays the RSS stream.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.01.2010<br />
    */
   public function run() {
      $lang = $this->getInput()->getAttribute(self::$LANG);
      $version = $this->getInput()->getAttribute(self::$PROTOCOL_VERSION);

      // generate items
      $page = new Page();
      $page->setContext($this->__Context);
      $page->setLanguage($lang);
      $page->loadDesign('sites::apf::pres::templates::news', 'rss');
      $items = $page->transform();

      $urlMgr = &$this->getServiceObject('sites::apf::biz', 'UrlManager');
      $link = 'http://adventure-php-framework.org' . $urlMgr->generateLink('124', $lang);
      echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="0.92">
   <channel>
      <title>Adventure PHP Framework (APF) News</title>
      <link>' . $link . '</link>
      <description>' . $this->getChannelDescription($lang) . '</description>
      <lastBuildDate>' . $this->getLastBuildDate($lang) . '</lastBuildDate>
      <pubDate>' . $this->getLastBuildDate($lang) . '</pubDate>
      <language>' . $lang . '</language>
      <ttl>' . self::$CACHE_TIME_IN_MINUTES . '</ttl>
      <image>
         <url>http://adventure-php-framework.org/apple-touch-icon.png</url>
         <title>Adventure PHP Framework (APF) News</title>
         <link>' . $link . '</link>
         <!-- Maximum value for width is 144, default value is 88.
         Maximum value for height is 400, default value is 31. -->
         <width>158</width>
         <height>158</height>
         <description>Adventure PHP Framework (APF) News</description>
      </image>
      <docs>http://blogs.law.harvard.edu/tech/rss</docs>
      <managingEditor>christian.achatz@adventure-php-framework.org (Christian Achatz)</managingEditor>
      <webMaster>christian.achatz@adventure-php-framework.org (Christian Achatz)</webMaster>'
            . $items
            . '</channel>
</rss>';
      exit(0);
      // end function
   }

   private function getChannelDescription($lang) {
      if ($lang === 'de') {
         return 'Dieser Channel liefert die aktuellen APF-News!';
      } else {
         return 'This channel servers the news of the APF!';
      }
   }

   /**
    * @return string An RFC822 date defining the last news notification.
    */
   private function getLastBuildDate($lang) {
      $file = APPS__PATH . '/sites/apf/pres/news/' . $lang . '_news.html';
      $time = filemtime($file);
      return date('D, d M Y H:i:s \G\M\T', $time);
   }

}
