<?php
   /**
   *  @package sites::apfdocupage::pres
   *  @class quicknavi_controller
   *
   *  Implements the document controller for the quicknavi view.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 30.08.2008<br />
   */
   class quicknavi_controller extends baseController
   {

      function quicknavi_controller(){
      }


      /**
      *  @public
      *
      *  Implements the transformContent() method. Displays the quicknavi content.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 30.08.2008<br />
      */
      function transformContent(){

         // define quicknavi-path
         $QuickNaviFilesPath = 'd:/Apache2/htdocs/www/demosite.de/frontend/quicknavi';

         // gather file name
         $FileName = $this->__getQuickNaviFileName($QuickNaviFilesPath);

         // insert content
         $this->__Content .= file_get_contents($QuickNaviFilesPath.'/'.$FileName);

       // end function
      }


      /**
      *  @private
      *
      *  Returns the name of the current quicknavi content file.
      *
      *  @param string $QuickNaviFilesPath folder, where the quicknavi files reside
      *  @return string $FileName name of the current quicknavi content file
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 30.08.2008<br />
      */
      function __getQuickNaviFileName($QuickNaviFilesPath){

         // get model object
         $Model = &Singleton::getInstance('APFModel');

         // get current language and page id
         $Lang = $Model->getAttribute('page.language');
         $PageID = $Model->getAttribute('page.id');

         // glob file name
         $Files = glob($QuickNaviFilesPath.'/n_'.$this->__Language.'_'.$PageID.'*');
         if(!isset($Files[0])){
            return 'n_'.$this->__Language.'_404.html';
          // end if
         }
         else{
            return basename($Files[0]);
          // end else
         }

       // end function
      }

    // end class
   }
?>