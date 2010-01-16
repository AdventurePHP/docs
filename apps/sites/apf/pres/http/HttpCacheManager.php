<?php
   import('tools::http','HeaderManager');

   /**
    * @package sites::apf::pres::http
    * @class HttpCacheManager
    *
    * This util class sends additional http headers, that allow
    * caching of the delivered resources.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 23.12.2009<br />
    */
   final class HttpCacheManager {

      /**
       * @var int One week in seconds.
       */
      private static $WEEK_IN_SECS = 604800;

      public static function sendHtmlCacheHeaders(){
         HeaderManager::send('Content-Type: text/html; charset=utf-8');
         //self::sendCacheHeaders();
      }

      public static function sendCssCacheHeaders(){
         HeaderManager::send('Content-Type: text/css; charset=utf-8');
         self::sendCacheHeaders();
      }

      public static function sendJsCacheHeaders(){
         HeaderManager::send('Content-Type: text/javascript; charset=utf-8');
         self::sendCacheHeaders();
      }

      private static function sendCacheHeaders(){
         $expires = time() + self::$WEEK_IN_SECS;
         $expiresDate = date('r',$expires);
         $lastModified = date('r',time());
         HeaderManager::send('Expires: '.$expiresDate);
         HeaderManager::send('Last-Modified: '.$lastModified);
         HeaderManager::send('Cache-Control: public; max-age='.self::$WEEK_IN_SECS);
      }
      
    // end class
   }
?>