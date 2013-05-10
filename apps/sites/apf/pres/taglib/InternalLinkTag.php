<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\sites\apf\biz\UrlManager;

/**
 * @package APF\sites\apf\pres\taglib
 * @class InternalLinkTag
 *
 * This taglib let's you easily create internal links. Depending on
 * the attributes given, the link is generated using the title of the
 * target page or a custom one.
 * <p/>
 * Tag signature:
 * <pre>
 * &lt;int:link pageid=""[ anchor=""][ title=""][ lang=""][ target=""][ id=""][ class=""][ params=""]/&gt;
 * &lt;int:link pageid=""[ anchor=""][ title=""][ lang=""][ target=""][ id=""][ class=""][ params=""]&gt;Link text&lt;/int:link&gt;
 * </pre>
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 23.12.2009<br />
 */
class InternalLinkTag extends Document {

   public function transform() {

      $pageId = $this->getAttribute('pageid');
      if ($pageId === null) {
         throw new \InvalidArgumentException('[InternalLinkTag::transform()] Attribute "pageid" must not be null!', E_USER_ERROR);
      }

      // setup language
      $lang = $this->getAttribute('lang');
      if ($lang === null) {
         $lang = $this->language;
      }

      // setup link text
      /* @var $urlMan UrlManager */
      $urlMan = & $this->getServiceObject('APF\sites\apf\biz\UrlManager');
      $linkText = $this->getLinkText();
      if ($linkText === null) {
         $linkText = $urlMan->getPageTitle($pageId, $lang);
      }

      // setup link title
      $title = $this->getAttribute('title');
      if ($title === null) {
         $title = $linkText;
      }

      // setup version
      $version = $this->getAttribute('version');

      // generate link
      $link = $urlMan->generateLink($pageId, $lang, $version);
      $params = $this->getAttribute('params');
      if ($params !== null) {
         $link .= $params;
      }
      $anchor = $this->getAttribute('anchor');
      if ($anchor !== null) {
         $link .= '#' . $anchor;
      }

      // resolve and add target if desired
      $target = $this->getAttribute('target');
      if ($target !== null) {
         $target = ' target="' . $target . '"';
      } else {
         $target = '';
      }

      // enable id and class attributes
      $id = $this->getAttribute('id');
      $class = $this->getAttribute('class');
      if ($id !== null) {
         $id = ' id="' . $id . '"';
      }
      if ($class !== null) {
         $class = ' class="' . $class . '"';
      }

      return '<a href="' . $link . '" title="' . $title . '"' . $target . $id . $class . '>' . $linkText . '</a>';

   }

   private function getLinkText() {
      if (empty($this->content)) {
         return null;
      }
      return $this->content;
   }

}
