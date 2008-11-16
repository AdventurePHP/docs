<?php
   import('3rdparty::statistics::biz','StatManager');
   import('tools::datetime','dateTimeManager');
   import('tools::variablen','variablenHandler');
   import('tools::link','linkHandler');


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
         $this->_LOCALS = variablenHandler::registerLocal(array('pagepart' => 'overview','Year' => null,'Month' => null,'Day' => null,'Hour' => null));
       // end function
      }


      function transformContent(){

         // fill backlinks
         if($this->_LOCALS['pagepart'] == 'year'){
            $this->setPlaceHolder('Backlink',linkHandler::generateLink($_SERVER['REQUEST_URI'],array('pagepart' => 'overview','Year' => '')));
          // end if
         }
         elseif($this->_LOCALS['pagepart'] == 'month'){
            $this->setPlaceHolder('Backlink',linkHandler::generateLink($_SERVER['REQUEST_URI'],array('pagepart' => 'year','Year' => $this->_LOCALS['Year'],'Month' => '')));
          // end elseif
         }
         elseif($this->_LOCALS['pagepart'] == 'day'){
            $this->setPlaceHolder('Backlink',linkHandler::generateLink($_SERVER['REQUEST_URI'],array('pagepart' => 'month','Year' => $this->_LOCALS['Year'],'Month' => $this->_LOCALS['Month'],'Day' => '')));
          // end elseif
         }
         elseif($this->_LOCALS['pagepart'] == 'hour'){
            $this->setPlaceHolder('Backlink',linkHandler::generateLink($_SERVER['REQUEST_URI'],array('pagepart' => 'day','Year' => $this->_LOCALS['Year'],'Month' => $this->_LOCALS['Month'],'Day' => $this->_LOCALS['Day'],'Hour' => '')));
          // end elseif
         }

         // set global template params
         if($this->__placeholderExists('Jahr')){
            $this->setPlaceHolder('Jahr',$this->_LOCALS['Year']);
          // end if
         }

         if($this->__placeholderExists('Monat')){
            $this->setPlaceHolder('Monat',dateTimeManager::showMonthLabel($this->_LOCALS['Month']));
          // end if
         }

         if($this->__placeholderExists('MonatZahl')){
            $this->setPlaceHolder('MonatZahl',$this->_LOCALS['Month']);
          // end if
         }

         if($this->__placeholderExists('Tag')){
            $this->setPlaceHolder('Tag',dateTimeManager::addLeadingZero($this->_LOCALS['Day']));
          // end if
         }

         if($this->__placeholderExists('Zeit')){
            $this->setPlaceHolder('Zeit',dateTimeManager::addLeadingZero($this->_LOCALS['Hour']).':00 - '.dateTimeManager::addLeadingZero($this->_LOCALS['Hour'] + 1).':00');
          // end if
         }


         // Statistik-Daten ausgeben
         $sM = &$this->__getServiceObject('sites::apfdocupage::biz','StatManager');
         $Liste = $sM->readStatistic(
                                     $this->_LOCALS['pagepart'],
                                     $this->_LOCALS['Year'],
                                     $this->_LOCALS['Month'],
                                     $this->_LOCALS['Day'],
                                     $this->_LOCALS['Hour']
                                    );

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

            for($j = 0; $j < count($E); $j++){

               // Sektions-Typ über Klasse abfragen
               if(get_class($Liste[$i]) == strtolower('LinkTableStatSection')){

                  $Eintrag->setPlaceHolder('Link',$this->__generateLink($E[$j]->getAttribute('DisplayText')));
                  $Eintrag->setPlaceHolder('Tooltip','');

                  // format the link text
                  if(!empty($this->_LOCALS['Year']) && empty($this->_LOCALS['Month'])){
                     $linktext = dateTimeManager::showMonthLabel($E[$j]->getAttribute('DisplayText'));
                   // end elseif
                  }
                  elseif(!empty($this->_LOCALS['Month']) && empty($this->_LOCALS['Day'])){
                     $linktext = dateTimeManager::addLeadingZero($E[$j]->getAttribute('DisplayText')).'.'.dateTimeManager::addLeadingZero($this->_LOCALS['Month']).'.'.$this->_LOCALS['Year'];
                   // end elseif
                  }
                  elseif(!empty($this->_LOCALS['Day']) && empty($this->_LOCALS['Hour'])){
                     $linktext = dateTimeManager::addLeadingZero($E[$j]->getAttribute('DisplayText')).':00 - '.dateTimeManager::addLeadingZero(intval($E[$j]->getAttribute('DisplayText') + 1)).':00';
                   // end else
                  }
                  elseif(!empty($this->_LOCALS['Hour'])){
                     $linktext = dateTimeManager::addLeadingZero($this->_LOCALS['Hour']).':'.dateTimeManager::addLeadingZero($E[$j]->getAttribute('DisplayText'));
                   // end elseif
                  }
                  else{
                     $linktext = $E[$j]->getAttribute('DisplayText');
                   // end else
                  }
                  $Eintrag->setPlaceHolder('LinkText',$linktext);

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
      *  @private
      *
      *  Generates the links for the LinkTableStatSection objects.
      *
      *  @param string $addparam the year, month, day and hour indicator
      *  @return string $url the desried url
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 16.11.2008<br />
      */
      function __generateLink($addparam){

         switch($this->_LOCALS['pagepart']){

            case 'year':
               $params = array(
                               'pagepart' => 'month',
                               'Year' => $this->_LOCALS['Year'],
                               'Month' => $addparam
                              );
               break;

            case 'month':
               $params = array(
                               'pagepart' => 'day',
                               'Year' => $this->_LOCALS['Year'],
                               'Month' => $this->_LOCALS['Month'],
                               'Day' => $addparam
                              );
               break;

            case 'day':
               $params = array(
                               'pagepart' => 'hour',
                               'Year' => $this->_LOCALS['Year'],
                               'Month' => $this->_LOCALS['Month'],
                               'Day' => $this->_LOCALS['Day'],
                               'Hour' => $addparam
                              );
               break;

            case 'hour':
               $params = array(
                               'Year' => $this->_LOCALS['Year'],
                               'Month' => $this->_LOCALS['Month'],
                               'Day' => $this->_LOCALS['Day'],
                               'Hour' => $this->_LOCALS['Hour']
                              );
               break;

            default:
               $params = array(
                               'pagepart' => 'year',
                               'Year' => $addparam
                              );
               break;

          // end if
         }

         return linkHandler::generateLink($_SERVER['REQUEST_URI'],$params);

       // end function
      }

    // end class
   }
?>