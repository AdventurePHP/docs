<?php
namespace DOCS\pres\taglib;

use APF\core\pagecontroller\ImportTemplateTag;
use APF\core\singleton\Singleton;
use DOCS\biz\APFModel;

/**
 * Displays the sidebar, depending on the model information.
 *
 * @author Christian Achatz
 * @version 0.1, 19.01.2010<br />
 */
class SidebarDisplayTag extends ImportTemplateTag {

   /**
    * @public
    *
    * Re-implements the <em>onParseTime()</em> method for this taglib. This is
    * necessary, because the pagepart behaviour is not needed here.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 06.02.2010<br />
    */
   public function onParseTime() {

      /* @var $model APFModel */
      $model = &Singleton::getInstance(APFModel::class);
      if ($model->getDisplaySidebar() == true) {

         // get content
         $this->loadContentFromFile('DOCS\pres\templates', 'quicknavi');

         // parse document controller statements
         $this->extractDocumentController();

         // extract further xml tags
         $this->extractTagLibTags();
      }

   }

}
