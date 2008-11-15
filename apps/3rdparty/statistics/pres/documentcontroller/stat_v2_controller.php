<?php
   import('3rdparty::statistics::biz','StatManager');
   import('tools::datetime','dateTimeManager');
   import('tools::variablen','variablenHandler');


   /**
   *  @package 3rdparty::statistics::pres
   *  @class stat_v2_controller
   *
   *  Implementiert den DocumentController für die Templates:<br />
   *  - overview.html<br />
   *  - year.html<br />
   *  - month.html<br />
   *  - day.html<br />
   *  - hour.html<br />
   *
   *  @author Christian Achatz
   *  @versio
   *  Version 0.1, 04.06.2006<br />
   *  Version 0.2, 05.06.2006<br />
   *  Version 0.3, 15.11.2008 (Adapted to new business objects)<br />
   */
   class stat_v2_controller extends baseController
   {


      /**
      *  @private
      *  Contains the parameters that are used by the GUI.
      */
      var $_LOCALS = array();


      function stat_v2_controller(){
         $this->__registerLocal(array('pagepart','year','month','day','hour'));
       // end function
      }


      function transformContent(){

         // Allgemeine Template-Inhalte setzen
         if($this->__placeholderExists('Jahr')){
            $this->setPlaceHolder('Jahr',$this->_LOCALS['year']);
          // end if
         }

         if($this->__placeholderExists('Monat')){
            $this->setPlaceHolder('Monat',dateTimeManager::showMonthLabel($this->_LOCALS['month']));
          // end if
         }

         if($this->__placeholderExists('MonatZahl')){
            $this->setPlaceHolder('MonatZahl',$this->_LOCALS['month']);
          // end if
         }

         if($this->__placeholderExists('Tag')){
            $this->setPlaceHolder('Tag',dateTimeManager::addLeadingZero($this->_LOCALS['day']));
          // end if
         }

         if($this->__placeholderExists('Zeit')){
            $this->setPlaceHolder('Zeit',dateTimeManager::addLeadingZero($this->_LOCALS['hour']).':00 - '.dateTimeManager::addLeadingZero($this->_LOCALS['hour'] + 1).':00');
          // end if
         }


         // Statistik-Daten ausgeben
         $sM = &$this->__getServiceObject('sites::apfdocupage::biz','StatManager');
         $Liste = $sM->readStatistic($this->_LOCALS['pagepart']);

         $T = &Singleton::getInstance('benchmarkTimer');
         $T->start('Ausgabe');

         $SektionsPuffer = (string)'';

         for($i = 0; $i < count($Liste); $i++){

            // Sektion initialisieren
            if(get_class($Liste[$i]) == strtolower('LinkTableStatSection')){
               $Sektion = &$this->__getTemplate('linkTabellenStatSektion');
             // end if
            }
            else{
               $Sektion = &$this->__getTemplate('statSektion');
             // end else
            }


            $Sektion->setPlaceHolder('Titel',$Liste[$i]->getAttribute('Title'));

            if($this->__templatePlaceholderExists($Sektion,'Summe')){
               $Sektion->setPlaceHolder('Summe',$Liste[$i]->getAttribute('Sum'));
             // end if
            }

            if($this->__templatePlaceholderExists($Sektion,'Durchschnitt')){
               $Sektion->setPlaceHolder('Durchschnitt',$Liste[$i]->getAttribute('Average'));
             // end if
            }

            // Template für Eintrag initialisieren
            $E = $Liste[$i]->getAttribute('Entries');

            // Puffer für Einträge initialisieren
            $EintragsPuffer = (string)'';

            // Template holen
            $Eintrag = &$this->__getTemplate('Eintrag_LinkTabellen');

            //echo printObject($Liste[$i]);

            for($j = 0; $j < count($E); $j++){

               // Sektions-Typ über Klasse abfragen
               if(get_class($Liste[$i]) == strtolower('LinkTableStatSection')){

                  $Eintrag->setPlaceHolder('Link',$E[$j]->getAttribute('Link'));
                  $Eintrag->setPlaceHolder('Tooltip','');
                  $Eintrag->setPlaceHolder('LinkText',$E[$j]->getAttribute('DisplayText'));
                  $Eintrag->setPlaceHolder('Text','');
                  $Eintrag->setPlaceHolder('Breite',$E[$j]->getAttribute('Value')/$Liste[$i]->getAttribute('Divisor'));
                  $Eintrag->setPlaceHolder('Anzahl',$E[$j]->getAttribute('Value'));

                // end if
               }
               elseif(get_class($Liste[$i]) == strtolower('TableStatSection')){

                  $Eintrag = &$this->__getTemplate('Eintrag_Tabellen');

                  $Eintrag->setPlaceHolder('Text',$E[$j]->getAttribute('DisplayText'));
                  $Eintrag->setPlaceHolder('Breite',$E[$j]->getAttribute('Value')/$Liste[$i]->getAttribute('Divisor'));
                  $Eintrag->setPlaceHolder('Anzahl',$E[$j]->getAttribute('Value'));

                // end if
               }
               else{

                  $Eintrag = &$this->__getTemplate('Eintrag_Standard');

                  $Eintrag->setPlaceHolder('Text',$E[$j]->getAttribute('DisplayText'));
                  $Eintrag->setPlaceHolder('Breite',$E[$j]->getAttribute('Value')/$Liste[$i]->getAttribute('Divisor'));
                  $Eintrag->setPlaceHolder('Anzahl',$E[$j]->getAttribute('Value'));

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