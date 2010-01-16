<?php
   import('tools::filesystem','FilesystemManager');
   import('sites::apf::pres::controller::release','release_base_controller');

   /**
    * @package sites::apf::pres::controller::external
    * @class api_controller
    *
    * Implements the document controller of the API documentation listing.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.01.2009<br />
    */
   class api_controller extends release_base_controller {

      function api_controller() {
         parent::release_base_controller();
      }

      /**
       * @public
       *
       * Displays the list of available API documentations.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 10.01.2009<br />
       */
      function transformContent() {

         // get the desired releaes
         $releases = $this->getAllReleases();

         // prefill the template
         $tmpl = &$this->__getTemplate('Release');
         $tmpl->setPlaceHolder('ReleaseURL',$this->__ReleasesBaseURL);
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

         // prefill new template
         $tmpl_new = &$this->__getTemplate('Release_1.10');
         $tmpl_new->setPlaceHolder('ReleaseURL',$this->__ReleasesBaseURL);
         $tmpl_new->setPlaceHolder('Version',$tmpl_version->transformTemplate());

         $tmpl_linkname = &$this->__getTemplate('LinkName_'.$this->__Language);
         $tmpl_new->setPlaceHolder('LinkName',$tmpl_linkname->transformTemplate());

         $tmpl_new->setPlaceHolder('LinkTitle',$tmpl_linktitle->transformTemplate());

         // display available API documentations
         $buffer = (string)'';

         for($i = 0; $i < count($releases); $i++) {

            // gather version -------------------------------------------------------------------
            $dashOffset = strpos($releases[$i],'-');
            if($dashOffset !== false) {
               $rawVersion = substr($releases[$i],0,$dashOffset);
            }
            else {
               $rawVersion = $releases[$i];
            }
            $version = release_base_controller::normalizeVersionNumber($rawVersion);
            // ----------------------------------------------------------------------------------

            if($version >= 110) {
               $tmpl_new->setPlaceHolder('ReleaseName',$releases[$i]);
               $buffer .= $tmpl_new->transformTemplate();
               // end if
            }
            else {
               $tmpl->setPlaceHolder('ReleaseName',$releases[$i]);
               $buffer .= $tmpl->transformTemplate();
               // end else
            }

            // end for
         }

         $this->setPlaceHolder('Releases',$buffer);

       // end function
      }

    // end class
   }
?>