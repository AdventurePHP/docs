<?php
   import('sites::apfdocupage::pres::documentcontroller::perspectives::content','releases_controller');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content
   *  @class releases_quicknavi_controller
   *
   *  Implements the document controller for the releases quicknavi.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 19.09.2008<br />
   */
   class releases_quicknavi_controller extends releases_controller
   {


      /**
      *  @public
      *
      *  Calls the parent contructor.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 05.10.2008<br />
      */
      function releases_quicknavi_controller(){
         parent::releases_controller();
       // end function
      }


      /**
      *  @public
      *
      *  Displays the list of available releases.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 19.09.2008<br />
      */
      function transformContent(){

         // ead releases
         $Releases = array_reverse(FilesystemManager::getFolderContent($this->__ReleasesLocalDir));
         usort($Releases,array('releases_quicknavi_controller','sortReleases'));

         // generate release list
         if(count($Releases) > 0){

            // initialize buffer
            $Buffer = (string)'';

            // get template
            $Template__Release = &$this->__getTemplate('Release_'.$this->__Language);

            // generate list
            for($i = 0; $i < count($Releases); $i++){
               $Template__Release->setPlaceHolder('ReleaseName',$Releases[$i]);
               $Buffer .= $Template__Release->transformTemplate();
             // end for
            }

            // display list
            $this->setPlaceHolder('Releases',$Buffer);

          // end if
         }

       // end function
      }

    // end class
   }
?>