<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;
use APF\core\pagecontroller\TagLib;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;

/**
 * @package sites::apf::pres::taglib
 * @class QuickNaviContentTag
 *
 * Implements the taglib, that displays the quicknavi content.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 28.12.2009<br />
 */
class QuickNaviContentTag extends Document {

   public function __construct() {

      parent::__construct();

      // include the additional tag libs
      $this->tagLibs[] = new TagLib('sites::apf::pres::taglib', 'InternalLinkTag', 'int', 'link');
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
      $model = &Singleton::getInstance('APFModel');

      // include the content of the model's content file in the current object
      $this->content .= file_get_contents(
         $model->getAttribute('content.filepath')
               . '/quicknavi/'
               . $model->getAttribute('page.quicknavifilename')
      );

      // extract tag libs included in the content
      $this->extractTagLibTags();

      // extract document controller statements
      $this->extractDocumentController();
   }

}
