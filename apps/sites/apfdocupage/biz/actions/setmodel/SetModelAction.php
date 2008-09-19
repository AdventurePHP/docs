<?php
   import('sites::apfdocupage::biz','APFModel');
   import('tools::variablen','variablenHandler');


   /**
   *  @package sites::apfdocupage::biz::actions::setmodel
   *  @class SetModelAction
   *
   *  Represents the front controller action to initialize the page's model.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 22.08.2008<br />
   */
   class SetModelAction extends AbstractFrontcontrollerAction
   {

      function SetModelAction(){
      }


      /**
      *  @public
      *
      *  Implements the abstract run method. Initializes the model.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.08.2008<br />
      *  Version 0.2, 17.09.2008 (Added functionality to write the content and quicknavi file names to the model)<br />
      *  Version 0.3, 19.09.2008 (Title is now set by the &lt;doku:title /&gt;-Tag)<br />
      */
      function run(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // register request parameters
         $PageIndicatorNames = $Model->getAttribute('page.indicator');
         $PageIndicatorNameDE = $PageIndicatorNames['de'];
         $PageIndicatorNameEN = $PageIndicatorNames['en'];
         $CurrentPageIndicators = variablenHandler::registerLocal(array($PageIndicatorNameDE => null,$PageIndicatorNameEN => null));

         // check request parameters and set current language
         if($CurrentPageIndicators[$PageIndicatorNameDE] != null){
            $Language = 'de';
            $Model->setAttribute('page.language',$Language);
            $this->__ParentObject->set('Language',$Language);
            $this->__Language = $Language;
            $Model->setAttribute('perspective.name','content');
          // end if
         }
         elseif($CurrentPageIndicators[$PageIndicatorNameEN] != null){
            $Language = 'en';
            $Model->setAttribute('page.language',$Language);
            $this->__ParentObject->set('Language',$Language);
            $this->__Language = $Language;
            $Model->setAttribute('perspective.name','content');
          // end if
         }
         else{

            // use default language of the front controller (maybe a problem, perhaps use
            // a session instance to store the language)
            $Language = $this->__ParentObject->get('Language');
            $Model->setAttribute('page.language',$Language);
            $Model->setAttribute('perspective.name','start');

          // end else
         }

         // fill current page id
         $PageName = $CurrentPageIndicators[$PageIndicatorNames[$Model->getAttribute('page.language')]];
         $PageID = substr($PageName,0,3);
         $Model->setAttribute('page.id',$PageID);

         // fill the current content and quicknavi file name
         $ContentFilePath = $Model->getAttribute('content.filepath');
         $Model->setAttribute('page.contentfilename',$this->__getFileName($ContentFilePath.'/content','c',$Language,$PageID));
         $Model->setAttribute('page.quicknavifilename',$this->__getFileName($ContentFilePath.'/quicknavi','n',$Language,$PageID));

       // end function
      }


      /**
      *  @private
      *
      *  Returns the file name for the content and quicknavi files.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 17.09.2008<br />
      */
      function __getFileName($ContentFilePath,$Prefix,$Language,$PageID){

         // read files from given directory
         $ContentFiles = glob($ContentFilePath.'/'.$Prefix.'_'.$Language.'_'.$PageID.'*');

         // check, if appropriate file exists
         if(!isset($ContentFiles[0])){
            return $Prefix.'_'.$Language.'_404.html';
          // end if
         }
         else{
            return basename($ContentFiles[0]);
          // end else
         }

       // end function
      }

    // end class
   }
?>