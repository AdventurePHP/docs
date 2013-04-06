<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;

/**
 * @package sites::apf::pres::taglib
 * @class DocumentationTitleTag
 *
 * Implements the title taglib. The tag informs the model about the page's title and
 * displays a <h2> heading.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 18.09.2008<br />
 */
class DocumentationTitleTag extends Document {

   /**
    * @private
    * Indicates the page's title.
    */
   private $title = null;

   /**
    * @public
    *
    * Analyzes the attributes and content and informs the model.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 18.09.2008<br />
    * Version 0.2, 19.09.2008 (Added meta tag and urlname handling; changed description output format)<br />
    * Version 0.3, 30.09.2008 (Removed double blanks in meta description)<br />
    */
   public function onParseTime() {

      // get page title
      $this->title = $this->getAttribute('title');
      if ($this->title === null) {
         throw new \InvalidArgumentException('[DocumentationTitleTag::onParseTime()] The attribute "title" is missing. Please provide the page title!', E_USER_ERROR);
      }

      // get page tags
      $tags = $this->getAttribute('tags');
      if ($tags === null) {
         throw new \InvalidArgumentException('[DocumentationTitleTag::onParseTime()] The attribute "tags" is missing. Please provide the page meta tags!', E_USER_ERROR);
      }

      // get urlname
      $urlName = $this->getAttribute('urlname');
      if ($urlName === null) {
         throw new \InvalidArgumentException('[DocumentationTitleTag::onParseTime()] The attribute "urlname" is missing. Please provide url name of the page!', E_USER_ERROR);
      }

      // get page description
      if (empty($this->content)) {
         throw new \InvalidArgumentException('[DocumentationTitleTag::onParseTime()] No page description given in the tag\'s content area. Please provide the page description!', E_USER_ERROR);
      }

      // get parent documentation page id
      $parentPageId = $this->getAttribute('parent');

      // inform model
      /* @var $model APFModel */
      $model = Singleton::getInstance('APFModel');
      $model->setTitle($this->title);
      $model->setAttribute('page.description', str_replace('  ', ' ', str_replace("\r", '', str_replace("\n", '', trim($this->content)))));
      $model->setAttribute('page.tags', $tags);
      $model->setAttribute('page.urlname', $urlName);
      $model->setParentPageId($parentPageId);

   }

   /**
    * @public
    *
    * Displays the <h2> heading.
    *
    * @return string $Heading the <h2> heading
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 18.09.2008<br />
    * Version 0.2, 03.10.2008 (Introduced the "display" attribute. If present and set to false, the title will not be displayed)<br />
    */
   public function transform() {
      $display = $this->getAttribute('display');
      if ($display == 'false') {
         return '';
      } else {
         return '<h2>' . $this->title . '</h2>';
      }

   }

}
