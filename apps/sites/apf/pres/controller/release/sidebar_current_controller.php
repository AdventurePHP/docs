<?php
   import('sites::apf::pres::controller::release','release_base_controller');

   /**
    * @package sites::apf::pres::controller::release
    * @class sidebar_current_controller
    *
    * Generates the sidebar links to the current stable and current
    * experimental release page.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    * Version 0.2, 16.01.2010 (Added behaviour to only display unstables greater than the last stable)<br />
    */
   class sidebar_current_controller extends release_base_controller {

      private static $STABLE = 'stable';
      private static $UNSTABLE = 'unstable';
      private static $STABLE_PAGEID = '008';
      private static $UNSTABLE_PAGEID = '125';

      /**
       * @var string[] Stores all releases (cache)!
       */
      private $allReleases;

      public function sidebar_current_controller(){
         parent::release_base_controller();
      }

      public function transformContent(){

         $stableRelease = $this->getCurrentStableRelease();
         $unstableRelease = $this->getCurrentUnstableRelease();

         // do only display unstable releases, that are greater than the current stable
         $normalizedUnstable = release_base_controller::normalizeVersionNumber($unstableRelease);
         $normalizedStable = release_base_controller::normalizeVersionNumber($stableRelease);
         if($normalizedUnstable > $normalizedStable){
            $tmpl = &$this->getTemplate(self::$UNSTABLE);
            $tmpl->setPlaceHolder('release',$this->buildLink($unstableRelease,self::$UNSTABLE_PAGEID));
            $tmpl->transformOnPlace();
         }

         // handle stable releases
         $tmpl = &$this->getTemplate(self::$STABLE);
         $tmpl->setPlaceHolder('release',$this->buildLink($stableRelease,self::$STABLE_PAGEID));
         $tmpl->transformOnPlace();

       // end  function
      }

      /**
       * @param string $release The release to link to.
       * @param string $pageId The page id to link to.
       * @return string The desired link to the release page.
       */
      private function buildLink($release,$pageId){
         $urlMan = &$this->__getServiceObject('sites::apf::biz','UrlManager');
         $link = $urlMan->generateLink($pageId,$this->__Language);
         return '<a href="'.$link.'" title="Get release '.$release.'!">APF '.$release.'</a>';
      }

      /**
       * @return string The current unstable release number.
       */
      private function getCurrentUnstableRelease(){
         $releases = $this->getAllReleasesCached();
         foreach($releases as $release){
            if(!$this->isStableRelease($release)){
               return $release;
            }
         }
         return null;
      }

      /**
       * @return string The current stable release number.
       */
      private function getCurrentStableRelease(){
         $releases = $this->getAllReleasesCached();
         foreach($releases as $release){
            if($this->isStableRelease($release)){
               return $release;
            }
         }
         return null;
      }

      /**
       * @return string[] All releases of the APF, but cached for this instance!
       */
      private function getAllReleasesCached(){
         if(count($this->allReleases) == 0){
            $this->allReleases = $this->getAllReleases();
         }
         return $this->allReleases;
      }

    // end class
   }
?>