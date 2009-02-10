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
         $Releases = array_reverse(FilesystemManager::getFolderContent($this->__ReleasesLocalDir));
         usort($Releases,array('releases_controller','sortReleases'));

         // display releases
         if(count($Releases) > 0){

            // get templates
            $Template__ReleaseHead = &$this->__getTemplate('ReleaseHead_'.$this->__Language);
            $Template__ReleaseFile = &$this->__getTemplate('ReleaseFile');

            for($i = 0; $i < count($Releases); $i++){

               // fill release number
               $Template__ReleaseHead->setPlaceHolder('ReleaseNumber',$Releases[$i]);

               // fetch files
               $Files = FilesystemManager::getFolderContent($this->__ReleasesLocalDir.'/'.$Releases[$i].'/download');

               // sort files
               sort($Files);

               // fill eleaseDescription
               $ReleaseDescriptionFile = $this->__ReleasesLocalDir.'/'.$Releases[$i].'/'.$this->__Language.'_release_description.html';
               if(file_exists($ReleaseDescriptionFile)){
                  $Template__ReleaseHead->setPlaceHolder('ReleaseDescription',file_get_contents($ReleaseDescriptionFile));
                // end if
               }
               else{
                  $Template__ReleaseDescriptionPlaceHolder = &$this->__getTemplate('ReleaseDescriptionPlaceHolder_'.$this->__Language);
                  $Template__ReleaseHead->setPlaceHolder('ReleaseDescription',$Template__ReleaseDescriptionPlaceHolder->transformTemplate());
                // end else
               }

               // fill Documentation
               $DokuFiles = FilesystemManager::getFolderContent($this->__ReleasesLocalDir.'/'.$Releases[$i].'/doku');
               $Template__OfflineDoku = &$this->__getTemplate('OfflineDoku_'.$this->__Language);
               $Template__OfflineDoku->setPlaceHolder('ReleaseVersion',$Releases[$i]);
               $Buffer_OfflineDoku = (string)'';

               for($k = 0; $k < count($DokuFiles); $k++){

                  if(!is_dir($this->__ReleasesLocalDir.'/'.$Releases[$i].'/doku/'.$DokuFiles[$k])){

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

                     $Template__OfflineDoku->setPlaceHolder('BuildDate',$BuildDate);
                     $Template__OfflineDoku->setPlaceHolder('LibType',$LibType);
                     $Template__OfflineDoku->setPlaceHolder('DokuType',$DokuType);
                     $Template__OfflineDoku->setPlaceHolder('DokuFile',$DokuFiles[$k]);
                     $Template__OfflineDoku->setPlaceHolder('ReleasesBaseURL',$this->__ReleasesBaseURL);
                     $Buffer_OfflineDoku .= $Template__OfflineDoku->transformTemplate();

                   // end if
                  }

                // end for
               }
               $Template__Documentation = &$this->__getTemplate('Documentation_'.$this->__Language);
               $Template__Documentation->setPlaceHolder('ReleaseVersion',$Releases[$i]);
               $Template__Documentation->setPlaceHolder('OfflineDoku',$Buffer_OfflineDoku);
               $Template__Documentation->setPlaceHolder('ReleasesBaseURL',$this->__ReleasesBaseURL);
               $Template__ReleaseHead->setPlaceHolder('Documentation',$Template__Documentation->transformTemplate());


               // display SVN link
               $Template__ReleaseHead->setPlaceHolder('SVNWebURL','http://adventurephpfra.svn.sourceforge.net/viewvc/adventurephpfra/tags/'.$Releases[$i]);
               $Template__ReleaseHead->setPlaceHolder('SVNURL','https://adventurephpfra.svn.sourceforge.net/svnroot/adventurephpfra/tags/'.$Releases[$i]);


               // display release files
               $Buffer_Files = (string)'';

               for($j = 0; $j < count($Files); $j++){

                  if(!is_link($this->__ReleasesLocalDir.'/'.$Releases[$i].'/download/'.$Files[$j]) && !is_dir($this->__ReleasesLocalDir.'/'.$Releases[$i].'/download/'.$Files[$j])){

                     // gather file attributes
                     $FileAttributes = FilesystemManager::getFileAttributes($this->__ReleasesLocalDir.'/'.$Releases[$i].'/download/'.$Files[$j]);
                     //echo printObject($FileAttributes);

                     // fill template
                     $Template__ReleaseFile->setPlaceHolder('Link',$this->__ReleasesBaseURL.'/'.$Releases[$i].'/download/'.$Files[$j]);
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

            $OffsetOneValue = (float) substr($OffsetOne,0,3);
            $OffsetTwoValue = (float) substr($OffsetTwo,0,3);

            if($OffsetOneValue == $OffsetTwoValue){

               /*echo */$OffsetOneSecondValue = substr($OffsetOne,4);
               /*echo ':'.*/$OffsetTwoSecondValue = substr($OffsetTwo,4);

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
               $return = ($OffsetOneValue < $OffsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 1)';

          // end if
         }

         if(substr_count($OffsetOne,'-') > 0 && substr_count($OffsetTwo,'-') == 0){

            $OffsetOneValue = (float) substr($OffsetOne,0,3);
            $OffsetTwoValue = (float) $OffsetTwo;

            if($OffsetOneValue == $OffsetTwoValue){

               /*echo */$OffsetOneSecondValue = substr($OffsetOne,4);
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
               $return = ($OffsetOneValue < $OffsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 2)';

          // end if
         }

         if(substr_count($OffsetOne,'-') == 0 && substr_count($OffsetTwo,'-') > 0){

            $OffsetOneValue = (float) $OffsetOne;
            $OffsetTwoValue = (float) substr($OffsetTwo,0,3);

            if($OffsetOneValue == $OffsetTwoValue){
               $return = 0;
             // end if
            }
            else{
               $return = ($OffsetOneValue < $OffsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 3)';

          // end if
         }

         if(substr_count($OffsetOne,'-') == 0 && substr_count($OffsetTwo,'-') == 0){

            $OffsetOneValue = (float) $OffsetOne;
            $OffsetTwoValue = (float) $OffsetTwo;

            if($OffsetOneValue == $OffsetTwoValue){
               $return = 0;
             // end if
            }
            else{
               $return = ($OffsetOneValue < $OffsetTwoValue) ? 1 : -1;
             // end else
            }
            //echo ' "'.$return.'" (case 4)';

          // end if
         }

         // return sort indicator
         return $return;

       // end function
      }

    // end class
   }
?>