<?php
   import('sites::apf::biz','APFModel');

   /**
    * @package sites::apf::pres::controller
    * @class breadcrumb_controller
    * 
    * Document controller that displays the breadcrumb when navigating
    * to the second stage.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 18.12.2009<br />
    * Version 0.2, 22.12.2009 (Introduced link generating facility)<br />
    */
   class breadcrumb_controller extends base_controller {

      public function transformContent(){
         
         $model = &Singleton::getInstance('APFModel');
         /* @var $model APFModel */
         $parent = $model->getParentPageId();
         $lang = $model->getLanguage();
         if($parent !== '0'){
            $linkGen = &$this->__getServiceObject('sites::apf::biz','UrlManager');
            $docuLink = $linkGen->generateLink($parent,$lang);
            $docuTitle = $linkGen->getPageTitle($parent,$lang);

            $breadcrumb = $this->getTemplate('breadcrumb');
            $breadcrumb->setPlaceHolder('title',$model->getTitle());
            $breadcrumb->setPlaceHolder(
                    'mainpage',
                    '<a href="'.$docuLink.'" title="'.$docuTitle.'">'.$docuTitle.'</a>'
            );
            $breadcrumb->transformOnPlace();
         }

      }

   }
?>