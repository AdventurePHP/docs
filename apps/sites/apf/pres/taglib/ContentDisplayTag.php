<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\core\pagecontroller\TagLib;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;
use APF\sites\apf\pres\taglib\GenericHighlightTag;

/**
 * @package APF\sites\apf\pres\taglib
 * @class ContentDisplayTag
 *
 * Implements the "html:content" tag.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 28.03.2008<br />
 * Version 0.2, 17.09.2008 (Changed function to fit new model structure)<br />
 */
class ContentDisplayTag extends Document {

   /**
    * @public
    *
    * Initializes the known taglibs to start from this DOM node with.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.03.2008<br />
    * Version 0.2, 19.09.2008(Added several taglibs)<br />
    */
   public function __construct() {

      parent::__construct();

      // include the additional tag libs
      $this->tagLibs[] = new TagLib('sites::apf::pres::taglib', 'GenericHighlightTag', 'gen', 'highlight');
      $this->tagLibs[] = new TagLib('sites::apf::pres::taglib', 'DocumentationLinkTag', 'doku', 'link');
      $this->tagLibs[] = new TagLib('sites::apf::pres::taglib', 'InternalLinkTag', 'int', 'link');
      $this->tagLibs[] = new TagLib('sites::apf::pres::taglib', 'DocumentationTitleTag', 'doku', 'title');
   }

   /**
    * @public
    *
    * Reads and parses the content from the appropriate content file.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 28.03.2008<br />
    * Version 0.2, 17.09.2008 (Changed function to fit new model structure)<br />
    */
   public function onParseTime() {

      // get model
      $model = & Singleton::getInstance('APFModel');

      // include the content of the model's content file in the current object
      $this->content = file_get_contents(
         $model->getAttribute('content.filepath')
               . '/content/'
               . $model->getAttribute('page.contentfilename')
      );

      // extract tag libs included in the content
      $this->extractTagLibTags();

      // extract document controller statements
      $this->extractDocumentController();
   }

}