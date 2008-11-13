<?php
   import('3rdparty::statistics::biz','StatManager');
   import('tools::datetime','dateTimeManager');
   import('tools::variablen','variablenHandler');


   /**
   *  @package sites::adminpanel::pres::statistikpanel
   *  @module stat_v2_controller
   *
   *  Implementiert den DocumentController für die Templates:<br />
   *  - uebersicht.html<br />
   *  - jahr.html<br />
   *  - monat.html<br />
   *  - tag.html<br />
   *  - stunde.html<br />
   *
   *  @author Christian Schäfer
   *  @versio
   *  Version 0.1, 04.06.2006<br />
   *  Version 0.2, 05.06.2006<br />
   */
   class stat_v2_controller extends baseController
   {


      /**
      *  @private
      *  Contains the parameters that are used by the GUI.
      */
      var $_LOCALS = array();


      function stat_v2_controller(){
         $this->__registerLocal(array('pagepart','Jahr','Monat','Tag','Stunde'));
       // end function
      }


      function transformContent(){

         // Allgemeine Template-Inhalte setzen
         if($this->__placeholderExists('Jahr')){
            $this->setPlaceHolder('Jahr',$this->_LOCALS['Jahr']);
          // end if
         }

         if($this->__placeholderExists('Monat')){
            $this->setPlaceHolder('Monat',dateTimeManager::showMonthLabel($this->_LOCALS['Monat']));
          // end if
         }

         if($this->__placeholderExists('MonatZahl')){
            $this->setPlaceHolder('MonatZahl',$this->_LOCALS['Monat']);
          // end if
         }

         if($this->__placeholderExists('Tag')){
            $this->setPlaceHolder('Tag',dateTimeManager::addLeadingZero($this->_LOCALS['Tag']));
          // end if
         }

         if($this->__placeholderExists('Zeit')){
            $this->setPlaceHolder('Zeit',dateTimeManager::addLeadingZero($this->_LOCALS['Stunde']).':00 - '.dateTimeManager::addLeadingZero($this->_LOCALS['Stunde'] + 1).':00');
          // end if
         }


         // Statistik-Daten ausgeben
         $sM = &$this->__getServiceObject('sites::apfdocupage::biz','StatManager');
         $Liste = $sM->getStatData($this->_LOCALS['pagepart']);

         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('Ausgabe');

         $SektionsPuffer = (string)'';

         for($i = 0; $i < count($Liste); $i++){

            // Sektion initialisieren
            if(get_class($Liste[$i]) == strtolower('linkTabellenStatSektion')){
               $Sektion = &$this->__getTemplate('linkTabellenStatSektion');
             // end if
            }
            else{
               $Sektion = &$this->__getTemplate('statSektion');
             // end else
            }


            $Sektion->setPlaceHolder('Titel',$Liste[$i]->zeigeTitel());

            if($this->__templatePlaceholderExists($Sektion,'Summe')){
               $Sektion->setPlaceHolder('Summe',$Liste[$i]->zeigeSumme());
             // end if
            }

            if($this->__templatePlaceholderExists($Sektion,'Durchschnitt')){
               $Sektion->setPlaceHolder('Durchschnitt',$Liste[$i]->zeigeDurchschnitt());
             // end if
            }

            // Template für Eintrag initialisieren
            $E = $Liste[$i]->zeigeEintraege();

            // Puffer für Einträge initialisieren
            $EintragsPuffer = (string)'';

            // Template holen
            $Eintrag = &$this->__getTemplate('Eintrag_LinkTabellen');

            for($j = 0; $j < count($E); $j++){

               // Sektions-Typ über Klasse abfragen
               if(get_class($Liste[$i]) == strtolower('linkTabellenStatSektion')){

                  $Eintrag->setPlaceHolder('Link',$E[$j]->zeigeLink());
                  $Eintrag->setPlaceHolder('Tooltip','');
                  $Eintrag->setPlaceHolder('LinkText',$E[$j]->zeigeAnzeigeText());
                  $Eintrag->setPlaceHolder('Text','');
                  $Eintrag->setPlaceHolder('Breite',$E[$j]->zeigeWert()/$Liste[$i]->zeigeTeiler());
                  $Eintrag->setPlaceHolder('Anzahl',$E[$j]->zeigeWert());

                // end if
               }
               elseif(get_class($Liste[$i]) == strtolower('tabellenStatSektion')){

                  $Eintrag = &$this->__getTemplate('Eintrag_Tabellen');

                  $Eintrag->setPlaceHolder('Text',$E[$j]->zeigeAnzeigeText());
                  $Eintrag->setPlaceHolder('Breite',$E[$j]->zeigeWert()/$Liste[$i]->zeigeTeiler());
                  $Eintrag->setPlaceHolder('Anzahl',$E[$j]->zeigeWert());

                // end if
               }
               else{

                  $Eintrag = &$this->__getTemplate('Eintrag_Standard');

                  $Eintrag->setPlaceHolder('Text',$E[$j]->zeigeAnzeigeText());
                  $Eintrag->setPlaceHolder('Breite',$E[$j]->zeigeWert()/$Liste[$i]->zeigeTeiler());
                  $Eintrag->setPlaceHolder('Anzahl',$E[$j]->zeigeWert());

                // end if
               }

               $EintragsPuffer .= $Eintrag->transformTemplate();

             // end for
            }

            $Sektion->setPlaceHolder('Inhalt',$EintragsPuffer);

            $SektionsPuffer .= $Sektion->transformTemplate();

          // end for
         }

         $this->setPlaceHolder('Inhalt',$SektionsPuffer);

         $T->stop('Ausgabe');
         //echo $T->zeigeZeiten();

       // end function
      }


      /**
      *  @public
      *
      *  Implements an enhanced param registration method.
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 08.05.2006<br />
      *  Version 0.2, 03.06.2006 (Bugfix, that Bug behoben, dass nachträgliches Initialisieren von bereits registrierten Offsets fehl schlägt)<br />
      */
      function __registerLocal($Variablen = array()){
         $Temp = variablenHandler::registerLocal($Variablen);
         $this->_LOCALS = array_merge($this->_LOCALS,$Temp);
       // end function
      }

    // end class
   }
?>