<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\benchmark\BenchmarkTimer;
use APF\core\pagecontroller\ImportTemplateTag;
use APF\core\singleton\Singleton;
use APF\sites\apf\biz\APFModel;

/**
 * @package APF\sites\apf\pres\controller
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
      $t = & Singleton::getInstance('APF\core\benchmark\BenchmarkTimer');
      $id = '(' . get_class($this) . ') ' . $this->getObjectId() . '::onParseTime()';
      $t->start($id);

      /* @var $model APFModel */
      $model = & Singleton::getInstance('APF\sites\apf\biz\APFModel');
      if ($model->getDisplaySidebar() == true) {

         // get content
         $this->loadContentFromFile('APF\sites\apf\pres\templates', 'quicknavi');

         // parse document controller statements
         $this->extractDocumentController();

         // extract further xml tags
         $this->extractTagLibTags();
      }

      $t->stop($id);

   }

}
