<?php
namespace DOCS\biz\actions\stat;

use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use APF\core\http\HeaderImpl;
use DOCS\biz\statistics\StatManager;

/**
 * Represents the front controller action to log the access statistics.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 14.10.2008<br />
 */
class StatAction extends AbstractFrontcontrollerAction {

   private static $REQUEST_URL_INDICATOR = '/url/';
   private static $REFERER_INDICATOR = '/referer/';

   /**
    * @public
    *
    * Implements the abstract run method. Logs the page access.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 14.10.2008<br />
    * Version 0.2, 15.12.2008 (Introduced new StatManager)<br />
    * Version 0.3, 08.02.2010 (Switched to introduce a tracking pixel to avoid missing hits)<br />
    */
   public function run() {

      // gather input values
      /* @var $input StatInput */
      $input = $this->getInput();
      $pageLang = $input->getLanguage();
      $pageName = $input->getTitle();
      $pageId = $input->getPageId();
      $requestUrl = $this->getRequestUrl();
      $referer = $this->getReferer();

      // fakes *must* be done after param extraction, or we get unexpected results!
      $_SERVER['REQUEST_URI'] = $requestUrl; // fake request url
      $_SERVER['HTTP_REFERER'] = $referer; // fake referer

      // write statistic entry
      /* @var $sM StatManager */
      $sM = &$this->getServiceObject(StatManager::class);
      $sM->writeStatistic($pageId . ' - ' . $pageName, $pageLang);

      // deliver non-cachable image
      $expiresDate = date('r', 0);
      $lastModified = date('r', time());
      $response = $this->getResponse();
      $response->setHeader(new HeaderImpl('Content-Type', 'image/gif'));
      $response->setHeader(new HeaderImpl('Cache-Control', 'no-cache,no-store,must-revalidate'));
      $response->setHeader(new HeaderImpl('Pragma', 'no-cache'));
      $response->setHeader(new HeaderImpl('Expires', '' . $expiresDate));
      $response->setHeader(new HeaderImpl('Last-Modified', '' . $lastModified));
      $response->setBody(sprintf('%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%', 71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33, 249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59));
      $response->send();
   }

   /**
    * Evaluates and returnes the request url in a special way, because the
    * tracking pixel has a different request url.
    *
    * @return string The request url.
    *
    * @author Christian Achatz
    * @version
    * Version 0.2, 08.02.2010<br />
    */
   private function getRequestUrl() {
      return $this->getParameter(self::$REQUEST_URL_INDICATOR);
   }

   private function getParameter($name) {
      $start = strpos($_SERVER['REQUEST_URI'], $name);
      $length = strlen($name);
      $delimiter = strpos($_SERVER['REQUEST_URI'], '/', $start + $length);
      $foo = substr($_SERVER['REQUEST_URI'], $start + $length, $delimiter - $start - $length);

      return urldecode(urldecode($foo));
   }

   /**
    * Evaluates and returnes the referer in a special way, because the
    * tracking pixel has a different referer.
    *
    * @return string The referer.
    *
    * @author Christian Achatz
    * @version
    * Version 0.2, 12.04.2010<br />
    */
   private function getReferer() {
      return $this->getParameter(self::$REFERER_INDICATOR);
   }

}
