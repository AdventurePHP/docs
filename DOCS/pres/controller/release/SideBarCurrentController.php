<?php
namespace DOCS\pres\controller\release;

use APF\core\singleton\Singleton;
use DOCS\biz\APFModel;
use DOCS\biz\UrlManager;

/**
 * Generates the sidebar links to the current stable and current
 * experimental release page.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 30.12.2009<br />
 * Version 0.2, 16.01.2010 (Added behaviour to only display unstables greater than the last stable)<br />
 */
class SideBarCurrentController extends ReleaseBaseController {

   private static $STABLE = 'stable';
   private static $UNSTABLE = 'unstable';
   private static $STABLE_PAGEID = '008';
   private static $UNSTABLE_PAGEID = '125';

   /**
    * @var string[] Stores all releases (cache)!
    */
   private $allReleases;

   public function transformContent() {

      $stableRelease = $this->getCurrentStableRelease();
      $unstableRelease = $this->getCurrentUnstableRelease();

      // do only display unstable releases, that are greater than the current stable
      $normalizedUnstable = ReleaseBaseController::normalizeVersionNumber($unstableRelease);
      $normalizedStable = ReleaseBaseController::normalizeVersionNumber($stableRelease);
      if ($normalizedUnstable > $normalizedStable) {
         $tmpl = &$this->getTemplate(self::$UNSTABLE);
         $tmpl->setPlaceHolder('release', $this->buildLink($unstableRelease, self::$UNSTABLE_PAGEID));
         $tmpl->transformOnPlace();
      }

      // handle stable releases
      $tmpl = &$this->getTemplate(self::$STABLE);
      $tmpl->setPlaceHolder('release', $this->buildLink($stableRelease, self::$STABLE_PAGEID));
      $tmpl->transformOnPlace();

   }

   /**
    * @return string The current stable release number.
    */
   private function getCurrentStableRelease() {
      $releases = $this->getAllReleasesCached();
      foreach ($releases as $release) {
         if ($this->isStableRelease($release)) {
            return $release;
         }
      }

      return null;
   }

   /**
    * @return string[] All releases of the APF, but cached for this instance!
    */
   private function getAllReleasesCached() {
      if (count($this->allReleases) == 0) {
         $this->allReleases = $this->getAllReleases();
      }

      return $this->allReleases;
   }

   /**
    * @return string The current unstable release number.
    */
   private function getCurrentUnstableRelease() {
      $releases = $this->getAllReleasesCached();
      foreach ($releases as $release) {
         if (!$this->isStableRelease($release)) {
            return $release;
         }
      }

      return null;
   }

   /**
    * @param string $release The release to link to.
    * @param string $pageId The page id to link to.
    *
    * @return string The desired link to the release page.
    */
   private function buildLink($release, $pageId) {
      /* @var $urlMan UrlManager */
      $urlMan = &$this->getServiceObject(UrlManager::class);

      /* @var $model APFModel */
      $model = &Singleton::getInstance(APFModel::class);

      $link = $urlMan->generateLink($pageId, $this->language, $model->getDefaultVersionId());

      return '<a href="' . $link . '" title="Get release ' . $release . '!">APF ' . $release . '</a>';
   }

}
