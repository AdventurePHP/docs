<?php
   import('tools::filesystem','FilesystemManager');
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content','releases_controller');


   /**
   *  @namespace sites::apfdocupage::pres::documentcontroller::perspectives::content::api
   *  @class api_controller
   *
   *  Implements the document controller of the API documentation listing.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 10.01.2009<br />
   */
   class api_controller extends releases_controller
   {

      function api_controller(){
      }


      /**
      *  @public
      *
      *  Displays the list of available API documentations.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 10.01.2009<br />
      */
      function transformContent(){

         // get desired config values
         $reg = &Singleton::getInstance('Registry');
         $releaseDir = $reg->retrieve('sites::apfdocupage','Releases.LocalDir');
         $releaseUrl = $reg->retrieve('sites::apfdocupage','Releases.BaseURL');

         // load the releases
         $releases = FilesystemManager::getFolderContent($releaseDir);
         usort($releases,array('api_controller','sortReleases'));

         // prefill the template
         $tmpl = &$this->__getTemplate('Release');
         $tmpl->setPlaceHolder('ReleaseURL',$releaseUrl);
         $tmpl_version = &$this->__getTemplate('Version_'.$this->__Language);
         $tmpl->setPlaceHolder('Version',$tmpl_version->transformTemplate());

         $tmpl_linkname1 = &$this->__getTemplate('LinkName1_'.$this->__Language);
         $tmpl->setPlaceHolder('LinkName1',$tmpl_linkname1->transformTemplate());
         $tmpl_linkname2 = &$this->__getTemplate('LinkName2_'.$this->__Language);
         $tmpl->setPlaceHolder('LinkName2',$tmpl_linkname2->transformTemplate());
         $tmpl_linkname3 = &$this->__getTemplate('LinkName3_'.$this->__Language);
         $tmpl->setPlaceHolder('LinkName3',$tmpl_linkname3->transformTemplate());

         $tmpl_linktitle = &$this->__getTemplate('LinkTitle_'.$this->__Language);
         $tmpl->setPlaceHolder('LinkTitle',$tmpl_linktitle->transformTemplate());

         // display available API documentations
         $buffer = (string)'';

         for($i = 0; $i < count($releases); $i++){
            $tmpl->setPlaceHolder('ReleaseName',$releases[$i]);
            $buffer .= $tmpl->transformTemplate();
          // end for
         }

         $this->setPlaceHolder('Releases',$buffer);

       // end function
      }

    // end class
   }
?>