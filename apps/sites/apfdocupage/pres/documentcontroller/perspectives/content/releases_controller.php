<?php
   import('tools::filesystem','FilesystemManager');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content
   *  @class releases_controller
   *
   *  Implements the document controller for the releases view.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 16.08.2007<br />
   *  Version 0.2, 19.09.2008 (Refactoring for the new documenation page)<br />
   */
   class releases_controller extends baseController
   {

      /**
      *  @private
      *  Defines, where the releases reside on the filesystem.
      */
      var $__ReleasesLocalDir = null;


      /**
      *  @private
      *  Defines the base URL, where the releases can be accessed via the HTTP protocol.
      */
      var $__ReleasesBaseURL = null;


      /**
      *  @public
      *
      *  Initializes the filesystem and url location of the downloads.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 12.06.2008<br />
      *  Version 0.2, 19.09.2008 (Refactoring for the new documenation page)<br />
      */
      function releases_controller(){

         $Reg = &Singleton::getInstance('Registry');
         $this->__ReleasesLocalDir = $Reg->retrieve('sites::apfdocupage','Releases.LocalDir');
         $this->__ReleasesBaseURL = $Reg->retrieve('sites::apfdocupage','Releases.BaseURL');

       // end function
      }


      /**
      *  @public
      *
      *  Displays the releases available in the release folder. The directory structure is
      *  /<release_name>/[downloads|doku]/. The downloads folder contains all files, the doku folder
      *  includes the online and offlien documentation.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 16.08.2007<br />
      *  Version 0.2, 18.08.2007 (Release files are sorted by name now)<br />
      *  Version 0.3, 19.09.2008 (Refactoring for the new documenation page)<br />
      */
      function transformContent(){

         // initialize output buffer
         $Buffer_Releases = (string)'';

         // display message, if no release is available
         if(!is_dir($this->__ReleasesLocalDir)){
            $Template__NoContent = &$this->__getTemplate('NoContent_'.$this->__Language);
            $this->setPlaceHolder('Content',$Template__NoContent->transformTemplate());
            return;
          // end if
         }

         // read releases
         $releases = array_reverse(FilesystemManager::getFolderContent($this->__ReleasesLocalDir));
         usort($releases,array('releases_controller','sortReleases'));
         //echo printObject($releases);

         // display releases
         if(count($releases) > 0){

            // get templates
            $Template__ReleaseHead = &$this->__getTemplate('ReleaseHead_'.$this->__Language);
            $Template__ReleaseFile = &$this->__getTemplate('ReleaseFile');

            for($i = 0; $i < count($releases); $i++){

               // gather version -------------------------------------------------------------------
               $dashOffset = strpos($releases[$i],'-');
               if($dashOffset !== false){
                  $rawVersion = substr($releases[$i],0,$dashOffset);
               }
               else{
                  $rawVersion = $releases[$i];
               }
               $version = releases_controller::normalizeVersionNumber($rawVersion);
               // ----------------------------------------------------------------------------------

               // fill release number
               $Template__ReleaseHead->setPlaceHolder('ReleaseNumber',$releases[$i]);

               // fetch files
               $Files = FilesystemManager::getFolderContent($this->__ReleasesLocalDir.'/'.$releases[$i].'/download');

               // sort files
               sort($Files);

               // fill release description
               $ReleaseDescriptionFile = $this->__ReleasesLocalDir.'/'.$releases[$i].'/'.$this->__Language.'_release_description.html';
               if(file_exists($ReleaseDescriptionFile)){
                  $Template__ReleaseHead->setPlaceHolder('ReleaseDescription',file_get_contents($ReleaseDescriptionFile));
                // end if
               }
               else{
                  $Template__ReleaseDescriptionPlaceHolder = &$this->__getTemplate('ReleaseDescriptionPlaceHolder_'.$this->__Language);
                  $Template__ReleaseHead->setPlaceHolder('ReleaseDescription',$Template__ReleaseDescriptionPlaceHolder->transformTemplate());
                // end else
               }

               // fill offline documentation
               //echo '<br />$version: '.$version.', raw: '.$rawVersion.', $releases[$i]: '.$releases[$i];
               if($version >= 110 && substr_count($releases[$i],'RC') == 0){
                  $docsFolder = 'docs';
               }
               else {
                  $docsFolder = 'doku';
               }
               $DokuFiles = FilesystemManager::getFolderContent($this->__ReleasesLocalDir.'/'.$releases[$i].'/'.$docsFolder);

               // choose new template for versions > 1.10
               if($version >= 110){
                  $Template__OfflineDoku = &$this->__getTemplate('OfflineDoku_110_'.$this->__Language);
                // end if
               }
               else {
               $Template__OfflineDoku = &$this->__getTemplate('OfflineDoku_'.$this->__Language);
                // end else
               }

               $Template__OfflineDoku->setPlaceHolder('ReleaseVersion',$releases[$i]);
               $Buffer_OfflineDoku = (string)'';

               for($k = 0; $k < count($DokuFiles); $k++){

                  if(!is_dir($this->__ReleasesLocalDir.'/'.$releases[$i].'/'.$docsFolder.'/'.$DokuFiles[$k])){

                     if($version >= 110){

                        if($this->__Language == 'de'){
                           $LibType = 'Gepackte HTML-Seiten';
                           $DokuType = 'Komplette Dokumentation';
                         // end if
                        }
                        else {
                           $LibType = 'Packed html files';
                           $DokuType = 'Complete docs';
                         // end else
                        }

                      // end if
                     }
                     else {

                        // gather file type
                        switch(substr($DokuFiles[$k],strrpos($DokuFiles[$k],'.') + 1)){

                           case 'chm':
                              $LibType = 'chm';
                              break;
                           default:
                              $LibType = 'html + zip';
                              break;

                         // end switch
                        }

                        // gather docu type
                        if(substr_count($DokuFiles[$k],'-core-') > 0){

                           if($this->__Language == 'de'){
                                 $DokuType = 'Core';
                               // end if
                              }
                              else{
                                 $DokuType = 'core';
                               // end else
                              }

                         // end if
                        }
                        elseif(substr_count($DokuFiles[$k],'-modules-') > 0){

                           if($this->__Language == 'de'){
                                 $DokuType = 'Modules';
                               // end if
                              }
                              else{
                                 $DokuType = 'module';
                               // end else
                              }

                         // end elseif
                        }
                        else{

                           if($this->__Language == 'de'){
                                 $DokuType = 'Tools';
                               // end if
                              }
                              else{
                                 $DokuType = 'tool';
                               // end else
                              }

                         // end else
                        }

                      // end else
                     }

                     // extract build date
                     preg_match('/-([0-9]{2}\.[0-9]{2}\.[0-9]{4})-/',$DokuFiles[$k],$Matches);
                     if(isset($Matches[1])){
                       $BuildDate = $Matches[1];
                      // end if
                     }
                     else{

                        preg_match('/-([0-9]{4}-[0-9]{2}-[0-9]{2})-/',$DokuFiles[$k],$Matches);
                        if(isset($Matches[1])){
                           $BuildDate = date('d.m.Y',strtotime($Matches[1]));
                         // end if
                        }
                        else{

                           if($this->__Language == 'de'){
                              $BuildDate = 'unbekannt';
                            // end if
                           }
                           else{
                              $BuildDate = 'unknown';
                            // end else
                           }

                         // end else
                        }

                      // end else
                     }

                     if($version < 110){
                     $Template__OfflineDoku->setPlaceHolder('LibType',$LibType);
                     $Template__OfflineDoku->setPlaceHolder('DokuType',$DokuType);
                      // end if
                     }
                     $Template__OfflineDoku->setPlaceHolder('BuildDate',$BuildDate);
                     $Template__OfflineDoku->setPlaceHolder('DokuFile',$DokuFiles[$k]);
                     $Template__OfflineDoku->setPlaceHolder('ReleasesBaseURL',$this->__ReleasesBaseURL);
                     $Buffer_OfflineDoku .= $Template__OfflineDoku->transformTemplate();

                   // end if
                  }

                // end for
               }

               // -- check version to be greater than 1.10, than display only one online api doku
               if($version >= 110){
                  $Template__Documentation = &$this->__getTemplate('Documentation_new_'.$this->__Language);
                  $Template__Documentation->setPlaceHolder('DocsFolder',$docsFolder);
                // end if
               }
               else{
                  $Template__Documentation = &$this->__getTemplate('Documentation_'.$this->__Language);
                // end else
               }
               $Template__Documentation->setPlaceHolder('ReleaseVersion',$releases[$i]);
               $Template__Documentation->setPlaceHolder('OfflineDoku',$Buffer_OfflineDoku);
               $Template__Documentation->setPlaceHolder('ReleasesBaseURL',$this->__ReleasesBaseURL);
               $Template__ReleaseHead->setPlaceHolder('Documentation',$Template__Documentation->transformTemplate());

               // display SVN link
               $Template__ReleaseHead->setPlaceHolder('SVNWebURL','http://adventurephpfra.svn.sourceforge.net/viewvc/adventurephpfra/tags/'.$releases[$i]);
               $Template__ReleaseHead->setPlaceHolder('SVNURL','https://adventurephpfra.svn.sourceforge.net/svnroot/adventurephpfra/tags/'.$releases[$i]);

               // display release files
               $Buffer_Files = (string)'';

               for($j = 0; $j < count($Files); $j++){

                  if(!is_link($this->__ReleasesLocalDir.'/'.$releases[$i].'/download/'.$Files[$j]) && !is_dir($this->__ReleasesLocalDir.'/'.$releases[$i].'/download/'.$Files[$j])){

                     // gather file attributes
                     $FileAttributes = FilesystemManager::getFileAttributes($this->__ReleasesLocalDir.'/'.$releases[$i].'/download/'.$Files[$j]);
                     //echo printObject($FileAttributes);

                     // fill template
                     $Template__ReleaseFile->setPlaceHolder('Link',$this->__ReleasesBaseURL.'/'.$releases[$i].'/download/'.$Files[$j]);
                     $Template__ReleaseFile->setPlaceHolder('Name',$Files[$j]);
                     $Template__ReleaseFile->setPlaceHolder('Date',$FileAttributes['modificationdate']);
                     $Template__ReleaseFile->setPlaceHolder('Size',round((int)$FileAttributes['size'] / 1000,1));
                     $Template__ReleaseFile->setPlaceHolder('Type',$FileAttributes['extension']);

                     // add file to files buffer
                     $Buffer_Files .= $Template__ReleaseFile->transformTemplate();

                   // end if
                  }

                // end for
               }

               // generate file list
               $Template__ReleaseHead->setPlaceHolder('ReleaseFiles',$Buffer_Files);

               // generate whole release block
               $Buffer_Releases .= $Template__ReleaseHead->transformTemplate();

             // end for
            }

          // end if
         }

         // display content
         $this->setPlaceHolder('Content',$Buffer_Releases);

       // end function
      }


      /**
      *  @public
      *  @static
      *
      *  Implementation of the release file sort function.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 23.10.2007<br />
      *  Version 0.2, 15.01.2008 (Update to the sort algorithm)<br />
      */
      function sortReleases($OffsetOne,$OffsetTwo){

         //echo '<br />"'.$OffsetOne.'" | "'.$OffsetTwo.'" | Ergebnis:';

         if(substr_count($OffsetOne,'-') > 0 && substr_count($OffsetTwo,'-') > 0){

            $dashPosOneValue = strpos($OffsetOne,'-');
            $dashPosTwoValue = strpos($OffsetTwo,'-');
            $offsetOneValue = releases_controller::normalizeVersionNumber(substr($OffsetOne,0,$dashPosOneValue));
            $offsetTwoValue = releases_controller::normalizeVersionNumber(substr($OffsetTwo,0,$dashPosTwoValue));

            if($offsetOneValue == $offsetTwoValue){

               /*echo */$OffsetOneSecondValue = substr($OffsetOne,$dashPosOneValue + 1);
               /*echo ':'.*/$OffsetTwoSecondValue = substr($OffsetTwo,$dashPosTwoValue + 1);

               $IdenticalValuesArray = array();
               $IdenticalValuesArray[] = $OffsetOneSecondValue;
               $IdenticalValuesArray[] = $OffsetTwoSecondValue;
               natsort($IdenticalValuesArray);
               $IdenticalValuesArray = array_reverse($IdenticalValuesArray);

               if($IdenticalValuesArray[1] === $OffsetOneSecondValue){
                  $return = 1;
                // end if
               }
               else{
                  $return = -1;
                // end else
               }

             // end if
            }
            else{
               $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 1)';

          // end if
         }

         if(substr_count($OffsetOne,'-') > 0 && substr_count($OffsetTwo,'-') == 0){

            $dashPosOneValue = strpos($OffsetOne,'-');
            $offsetOneValue = releases_controller::normalizeVersionNumber(substr($OffsetOne,0,$dashPosOneValue));
            $offsetTwoValue = releases_controller::normalizeVersionNumber($OffsetTwo);

            if($offsetOneValue == $offsetTwoValue){

               /*echo */$OffsetOneSecondValue = substr($OffsetOne,$dashPosOneValue + 1);
               /*echo ':'.*/$OffsetTwoSecondValue = $OffsetTwo;

               $IdenticalValuesArray = array();
               $IdenticalValuesArray[] = $OffsetOneSecondValue;
               $IdenticalValuesArray[] = $OffsetTwoSecondValue;
               natsort($IdenticalValuesArray);

               if($IdenticalValuesArray[0] === $OffsetOneSecondValue){
                  $return = 1;
                // end if
               }
               else{
                  $return = -1;
                // end else
               }

             // end if
            }
            else{
               $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 2)';

          // end if
         }

         if(substr_count($OffsetOne,'-') == 0 && substr_count($OffsetTwo,'-') > 0){

            $dashPosTwoValue = strpos($OffsetTwo,'-');
            $offsetOneValue = releases_controller::normalizeVersionNumber($OffsetOne);
            $offsetTwoValue = releases_controller::normalizeVersionNumber(substr($OffsetTwo,0,$dashPosTwoValue));

            if($offsetOneValue == $offsetTwoValue){
               $return = 0;
             // end if
            }
            else{
               $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 3)';

          // end if
         }

         if(substr_count($OffsetOne,'-') == 0 && substr_count($OffsetTwo,'-') == 0){

            $offsetOneValue = releases_controller::normalizeVersionNumber($OffsetOne);
            $offsetTwoValue = releases_controller::normalizeVersionNumber($OffsetTwo);

            if($offsetOneValue == $offsetTwoValue){
               $return = 0;
             // end if
            }
            else{
               $return = ($offsetOneValue < $offsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 4)';

          // end if
         }

         return $return;

       // end function
      }

      /**
       * @private
       * @static 
       * @param string $version The version number extracted by the folder.
       * @return int The normalized version number.
       */
      function normalizeVersionNumber($version){
         $version = str_replace('.', '',$version);
         return (int)$version;
      }

    // end class
   }
?>