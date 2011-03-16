<?php
   import('sites::apf::biz','APFModel');
   import('tools::request','RequestHandler');

   /**
    * @package sites::apf::biz::actions::setmodel
    * @class SetModelAction
    *
    * Represents the front controller action to initialize the page's model.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 22.08.2008<br />
    */
   class SetModelAction extends AbstractFrontcontrollerAction {

      private static $ABOUT_PAGEID = '073';

      /**
       * @public
       *
       * Implements the abstract run method. Initializes the model.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 22.08.2008<br />
       * Version 0.2, 17.09.2008 (Added functionality to write the content and quicknavi file names to the model)<br />
       * Version 0.3, 19.09.2008 (Title is now set by the &lt;doku:title /&gt;-Tag)<br />
       * Version 0.4, 31.01.2009 (Removed the variablenHandler)<br />
       */
      public function run(){

         $model = Singleton::getInstance('APFModel');

         // register request parameters
         $pageIndicatorNames = $model->getAttribute('page.indicator');
         $pageIndicatorNameDe = $pageIndicatorNames['de'];
         $pageIndicatorNameEn = $pageIndicatorNames['en'];
         $currentPageIndicators = RequestHandler::getValues(
                 array($pageIndicatorNameDe => null,$pageIndicatorNameEn => null)
         );

         // check request parameters and set current language
         if ($currentPageIndicators[$pageIndicatorNameDe] != null) {
            $language = 'de';
            $model->setLanguage($language);
            $this->getFrontController()->setLanguage($language);
            $this->setLanguage($language);
            $model->setAttribute('perspective.name', 'content');
         } elseif ($currentPageIndicators[$pageIndicatorNameEn] != null) {
            $language = 'en';
            $model->setLanguage($language);
            $this->getFrontController()->setLanguage($language);
            $this->setLanguage($language);
            $model->setAttribute('perspective.name', 'content');
         } else {

            // use default language of the front controller (maybe a problem, perhaps use
            // a session instance to store the language)
            $language = $this->getFrontController()->getLanguage();
            $this->setLanguage($language);
            $model->setLanguage($language);
            $model->setAttribute('perspective.name', 'start');
         }

         // fill current page id
         $pageName = $currentPageIndicators[$pageIndicatorNames[$model->getLanguage()]];
         $pageId = substr($pageName,0,3);
         if(empty($pageId)){
            $pageId = '001';
         }
         $model->setAttribute('page.id',$pageId);

         // fill the current content and quicknavi file name
         $contentFilePath = $model->getAttribute('content.filepath');
         $model->setAttribute('page.contentfilename',$this->__getFileName($contentFilePath.'/content','c',$language,$pageId));
         $model->setAttribute('page.quicknavifilename',$this->__getFileName($contentFilePath.'/quicknavi','n',$language,$pageId));

         // initialize sidebar status
         if($pageId === self::$ABOUT_PAGEID){
            $model->setDisplaySidebar(false);
         }

      }

      /**
       * @private
       *
       * Returns the file name for the content and quicknavi files.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 17.09.2008<br />
       */
      function __getFileName($contentFilePath,$prefix,$language,$pageId){

         // read files from given directory
         $contentFiles = glob($contentFilePath.'/'.$prefix.'_'.$language.'_'.$pageId.'*');

         // check, if appropriate file exists
         if (!isset($contentFiles[0])) {
            return $prefix . '_' . $language . '_404.html';
         } else {
            return basename($contentFiles[0]);
         }

      }

   }
?>