<?php
import('sites::apf::pres::taglib', 'InternalLinkTag');

class NewsItemTag extends Document {

   private static $IMAGE_PATTERN = '/<img src="([A-Za-z0-9\.\-\/:]+)" alt="([^"]+)" \/>/i';
   private static $TITLE_PATTERN = '/<h3>(.*)<\/h3>/i';
   private static $DATE_PATTERN = '/<div class="date">(.*)<\/div>/i';
   private static $CONTENT_PATTERN = '/<p>(.*)<\/p>/ims';

   /**
    * @var string The news mode.
    */
   public static $MODE_NEWS = 'news';

   /**
    * @var string The rss mode.
    */
   public static $MODE_RSS = 'rss';

   private $transformOnPlace = false;

   /**
    * @var string The mode defines, whether the output is rendered as XML or as HTML.
    */
   private $mode;

   public function __construct() {
      $this->__TagLibs[] = new TagLib('sites::apf::pres::taglib', 'InternalLinkTag', 'int', 'link');
      $this->mode = self::$MODE_NEWS;
   }

   public function onParseTime() {
      $this->__extractTagLibTags();
   }

   public function transformOnPlace() {
      $this->transformOnPlace = true;
   }

   public function setDisplayMode($mode) {
      $this->mode = $mode;
   }

   public function transform() {
      if ($this->transformOnPlace === true) {
         if ($this->mode === self::$MODE_NEWS) {
            $prefix = '<div class="newsitem">';
            $suffix = '</div>';
         } else {
            $prefix = '<item>';
            $suffix = '</item>' . PHP_EOL;
            $this->__Content = $this->transform2RSS($this->__Content);
         }
         return trim($prefix . parent::transform() . $suffix);
      }
      return '';
   }

   private function transform2RSS($content) {

      $startPos = strpos($content, '<p>');
      $endPos = stripos($content, '</p>');
      $endPosLength = strlen('</p>');
      $description = substr($content, $startPos, $endPos - $startPos + $endPosLength);
      $content = substr_replace($content, '<description>' . $description . '</description>', $startPos, $endPos - $startPos + $endPosLength);

      // map images
      $content = preg_replace(
         self::$IMAGE_PATTERN,
         '<image>
     <url>$1</url>
     <title>Adventure PHP Framework (APF) News</title>
     <description>$2</description>
   </image>',
         $content);

      // map headlines
      $content = preg_replace(self::$TITLE_PATTERN, '<title>$1</title>', $content);

      // map date
      $content = preg_replace(self::$DATE_PATTERN, '<pubDate>$1</pubDate>', $content);

      return $content;
   }
}
