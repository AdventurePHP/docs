<?php
   import('core::filesystem','filesystemHandler');


   /**
   *  @package sites::demosite::pres::documentcontroller
   *  @module releases_v1_controller
   *
   *  Implementiert den DocumentController für das Design 'releases.html'. Zeigt die<br />
   *  Releases des Frameworks an.<br />
   *
   *  @author Christian Schäfer
   *  @version
   *  Version 0.1, 16.08.2007<br />
   */
   class releases_v1_controller extends baseController
   {

      /**
      *  @private
      *  Definiert den Pfad, in dem die Releases liegen.
      */
      var $__ReleasesFolder = null;


      /**
      *  @public
      *
      *  Konstruktor der Klasse. Initialisiert den Pfad, in dem die Releases liegen.<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 12.06.2008<br />
      */
      function releases_v1_controller(){

         // Lokaler Pfad unterscheidet sich vom Remote-Pfad
         $Reg = &Singleton::getInstance('Registry');
         if($Reg->retrieve('sites::apfdocupage','sitemap.env') == 'dev'){
            $this->__ReleasesFolder = 'D:/Entwicklung/Dokumentation/Build/RELEASES';
          // end if
         }
         else{
            $this->__ReleasesFolder = './frontend/media/releases';
          // end else
         }

       // end function
      }


      /**
      *  @module transformContent
      *  @public
      *
      *  Implementiert die abstrakte Methode "transformContent".<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 16.08.2007<br />
      *  Version 0.2, 18.08.2007 (Release-Dateien werden nun sortiert ausgegeben)<br />
      */
      function transformContent(){

         // Buffer initialisieren
         $Buffer_Releases = (string)'';

         // Releases auslesen und aufsteigend sortieren
         if(!is_dir($this->__ReleasesFolder)){
            $Template__NoContent = &$this->__getTemplate('NoContent_'.$this->__Language);
            $this->setPlaceHolder('Content',$Template__NoContent->transformTemplate());
            return true;
          // end if
         }

         // FilesystemHandler instanziieren und Verzeichnis auslesen
         $fH = new filesystemHandler($this->__ReleasesFolder);
         $Releases = array_reverse($fH->showDirContent());
         usort($Releases,array('releases_v1_controller','sortReleases'));

         // Releases ausgeben
         if(count($Releases) > 0){

            // Referenzen auf die Templates holen
            $Template__ReleaseHead = &$this->__getTemplate('ReleaseHead_'.$this->__Language);
            $Template__ReleaseFile = &$this->__getTemplate('ReleaseFile');

            for($i = 0; $i < count($Releases); $i++){

               // Release-Nummer füllen
               $Template__ReleaseHead->setPlaceHolder('ReleaseNumber',$Releases[$i]);

               // Dateien holen
               $fH->changeWorkDir($this->__ReleasesFolder.'/'.$Releases[$i].'/download');
               $Files = $fH->showDirContent();

               // Dateien sortieren
               sort($Files);

               // ReleaseDescription füllen
               $ReleaseDescriptionFile = $this->__ReleasesFolder.'/'.$Releases[$i].'/'.$this->__Language.'_release_description.html';
               if(file_exists($ReleaseDescriptionFile)){
                  $Template__ReleaseHead->setPlaceHolder('ReleaseDescription',file_get_contents($ReleaseDescriptionFile));
                // end if
               }
               else{
                  $Template__ReleaseDescriptionPlaceHolder = &$this->__getTemplate('ReleaseDescriptionPlaceHolder_'.$this->__Language);
                  $Template__ReleaseHead->setPlaceHolder('ReleaseDescription',$Template__ReleaseDescriptionPlaceHolder->transformTemplate());
                // end else
               }

               // Documentation füllen
               $fH->changeWorkDir($this->__ReleasesFolder.'/'.$Releases[$i].'/doku');
               $DokuFiles = $fH->showDirContent();

               $Template__OfflineDoku = &$this->__getTemplate('OfflineDoku_'.$this->__Language);
               $Template__OfflineDoku->setPlaceHolder('ReleaseVersion',$Releases[$i]);
               $Buffer_OfflineDoku = (string)'';
               for($k = 0; $k < count($DokuFiles); $k++){

                  if(!is_dir($this->__ReleasesFolder.'/'.$Releases[$i].'/doku/'.$DokuFiles[$k])){

                     // Datei-Typ erzeugen
                     switch(substr($DokuFiles[$k],strrpos($DokuFiles[$k],'.') + 1)){

                        case 'chm':
                           $LibType = 'chm';
                           break;
                        default:
                           $LibType = 'html + zip';
                           break;

                      // end switch
                     }

                     // Doku-Typ erzeugen
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

                     // Build-Datum extrahieren
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
                     $Buffer_OfflineDoku .= $Template__OfflineDoku->transformTemplate();

                   // end if
                  }

                // end for
               }
               $Template__Documentation = &$this->__getTemplate('Documentation_'.$this->__Language);
               $Template__Documentation->setPlaceHolder('ReleaseVersion',$Releases[$i]);
               $Template__Documentation->setPlaceHolder('OfflineDoku',$Buffer_OfflineDoku);
               $Template__ReleaseHead->setPlaceHolder('Documentation',$Template__Documentation->transformTemplate());


               // Dateien ausgeben
               $fH->changeWorkDir($this->__ReleasesFolder.'/'.$Releases[$i].'/download');
               $Buffer_Files = (string)'';

               for($j = 0; $j < count($Files); $j++){

                  if(!is_link($this->__ReleasesFolder.'/'.$Releases[$i].'/download/'.$Files[$j]) && !is_dir($this->__ReleasesFolder.'/'.$Releases[$i].'/download/'.$Files[$j])){

                     // Datei-Attribute lesen
                     $FileAttributes = $fH->showFileAttributes($this->__ReleasesFolder.'/'.$Releases[$i].'/download/'.$Files[$j]);

                     //Template füllen
                     $Template__ReleaseFile->setPlaceHolder('Link','/frontend/media/releases/'.$Releases[$i].'/download/'.$Files[$j]);
                     $Template__ReleaseFile->setPlaceHolder('Name',$Files[$j]);
                     $Template__ReleaseFile->setPlaceHolder('Date',$FileAttributes['ModifyingDate']);
                     $Template__ReleaseFile->setPlaceHolder('Size',$fH->showFileSize($this->__ReleasesFolder.'/'.$Releases[$i].'/download/'.$Files[$j]));
                     $Template__ReleaseFile->setPlaceHolder('Type',$FileAttributes['Extension']);

                     // Datei zum Puffer hinzufügen
                     $Buffer_Files .= $Template__ReleaseFile->transformTemplate();

                   // end if
                  }

                // end for
               }

               // Datei-Liste in Release einsetzen
               $Template__ReleaseHead->setPlaceHolder('ReleaseFiles',$Buffer_Files);

               // Release generieren
               $Buffer_Releases .= $Template__ReleaseHead->transformTemplate();

             // end for
            }

          // end if
         }

         // Buffer in Inhalt einsetzen
         $this->setPlaceHolder('Content',$Buffer_Releases);

       // end function
      }


      /**
      *  @module sortReleases()
      *  @public
      *  @static
      *
      *  Sortierfunktion für die Ausgabe von Releases.<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 23.10.2007<br />
      *  Version 0.2, 15.01.2008 (Sortierung nochmals korrigiert)<br />
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
            //echo ' "'.$return.'" (Fall 1)';

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
            //echo ' "'.$return.'" (Fall 2)';

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
            //echo ' "'.$return.'" (Fall 3)';

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
            //echo ' "'.$return.'" (Fall 4)';

          // end if
         }

         // Wert zurückgeben
         return $return;

       // end function
      }

    // end class
   }
?>