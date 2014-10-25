<?php
namespace DOCS\pres\taglib;

use APF\core\pagecontroller\AppendNodeTag;

/**
 * Enhances the <em>&lt;core:appendnode /&gt;</em> taglib to be able
 * to include language dependent template fragments.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 29.01.2010<br />
 */
class NewsDisplayTag extends AppendNodeTag {

   /**
    * Includes the news template depending on the desired language.
    */
   public function onParseTime() {
      $this->setAttribute('namespace', 'DOCS\pres\news');
      $this->setAttribute('template', $this->getLanguage() . '_news');
      parent::onParseTime();
   }

}
