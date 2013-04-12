<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;
use APF\sites\apf\biz\UrlManager;

class VersionSelectionTag extends Document {

   public function transform() {

      /* @var $model APFModel */
      $model = & Singleton::getInstance('APF\sites\apf\biz\APFModel');

      $currentVersion = $model->getVersionId();
      $pageVersions = $model->getPageVersions();

      $defaultVersionId = $model->getDefaultVersionId();

      if (count($pageVersions) < 2) {
         return '';
      }

      $lang = $model->getLanguage();
      $pageId = $model->getPageId();

      $label = $this->getLanguage() == 'de' ? 'APF-Version:' : 'APF version:';
      $buttonLabel = $this->getLanguage() == 'de' ? 'Ändern' : 'Change';

      $html = '<div id="VersionBox">'
            . '<div id="Versionswitch" data-lang="' . $lang . '" data-page-id="' . $pageId . '" data-button-label="' . $buttonLabel . '" data-default-version-id="' . $defaultVersionId . '">'
            . '<span>' . $label . '</span>'
            . '<ul>';

      /* @var $urlMan UrlManager */
      $urlMan = & $this->getServiceObject('APF\sites\apf\biz\UrlManager');

      // Options without version identifier are considered the default option. This is because of SEO reasons to not
      // generate duplicate content with pages having the same content but reachable under 2 urls (with and without
      // version identifier!
      $count = 1;
      foreach ($pageVersions as $pageVersion) {

         $html .= $count === 0 ? '<li class="first">' : '<li>';

         if ($currentVersion === $pageVersion) {
            $url = $urlMan->generateLink($pageId, $lang);
         } else {
            $url = $urlMan->generateLink($pageId, $lang, $pageVersion);
         }

         $html .= '<a data-version-id="' . $pageVersion . '" href="' . $url . '"';

         $html .= $currentVersion === $pageVersion ? ' class="current">' : '>';

         $html .= 'Version ' . $pageVersion . '</a>';

         $html .= '</li>';

         $count++;

      }

      return $html . '</ul>'
            . '</div>'
            . '</div>';
   }

}