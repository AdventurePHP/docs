<?php
import('sites::apf::biz', 'APFModel');

/**
 * @package sites::apf::pres::controller
 * @class SidebarDisplayTag
 *
 * Displays the sidebar, depending on the model information.
 *
 * @author Christian Achatz
 * @version 0.1, 19.01.2010<br />
 */
class SidebarDisplayTag extends ImportTemplateTag {

   public function __construct() {
      parent::__construct();
   }

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

      /* @var $t BenchmarkTimer */
      $t = &Singleton::getInstance('BenchmarkTimer');
      $id = '(' . get_class($this) . ') ' . $this->__ObjectID . '::onParseTime()';
      $t->start($id);

      /* @var $model APFModel */
      $model = &Singleton::getInstance('APFModel');
      if ($model->getDisplaySidebar() == true) {

         // get content
         $this->loadContentFromFile('sites::apf::pres::templates', 'quicknavi');

         // parse document controller statements
         $this->extractDocumentController();

         // extract further xml tags
         $this->extractTagLibTags();
      }

      $t->stop($id);

   }

}
