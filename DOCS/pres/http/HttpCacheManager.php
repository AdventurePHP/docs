<?php
namespace DOCS\pres\http;

use APF\core\http\HeaderImpl;
use APF\core\http\mixins\GetRequestResponse;

/**
 * This util class sends additional http headers, that allow
 * caching of the delivered resources.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 23.12.2009<br />
 * Version 0.2, 10.04.2010 (Reduced HTML caching to 2 days)<br />
 */
final class HttpCacheManager {

   use GetRequestResponse;

   /**
    * @var int One week in seconds.
    */
   private static $WEEK_IN_SECS = 604800;

   /**
    * @var int Two days in seconds.
    */
   private static $TWO_DAYS_IN_SECS = 86400;

   public static function sendHtmlCacheHeaders() {
      self::getResponse()->setHeader(new HeaderImpl('Content-Type', 'text/html; charset=utf-8'));
      self::sendCacheHeaders(self::$TWO_DAYS_IN_SECS);
   }

   public static function sendCssCacheHeaders() {
      self::getResponse()->setHeader(new HeaderImpl('Content-Type', 'text/css; charset=utf-8'));
      self::sendCacheHeaders(self::$WEEK_IN_SECS);
   }

   public static function sendJsCacheHeaders() {
      self::getResponse()->setHeader(new HeaderImpl('Content-Type', 'text/javascript; charset=utf-8'));
      self::sendCacheHeaders(self::$WEEK_IN_SECS);
   }

   private static function sendCacheHeaders($expiresTime) {
      $expires = time() + $expiresTime;
      $expiresDate = date('r', $expires);
      $lastModified = date('r', time());
      $response = self::getResponse();
      $response->setHeader(new HeaderImpl('Expires', $expiresDate));
      $response->setHeader(new HeaderImpl('Last-Modified', $lastModified));
      $response->setHeader(new HeaderImpl('Cache-Control', 'public; max-age=' . $expiresTime));
   }

}
