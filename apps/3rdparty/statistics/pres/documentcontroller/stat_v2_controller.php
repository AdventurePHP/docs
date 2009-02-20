<?php
   import('3rdparty::statistics::biz','StatManager');
   import('tools::datetime','dateTimeManager');
   import('tools::request','RequestHandler');
   import('tools::link','linkHandler');


   /**
   *  @package 3rdparty::statistics::pres
   *  @class stat_v2_controller
   *
   *  Implements the document controller for the following templates:
   *  <ul>
   *   <li>overview.html</li>
   *   <li>year.html</li>
   *   <li>month.html</li>
   *   <li>day.html</li>
   *   <li>hour.html</li>
   *  </ul>
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


      /**
      *  @public
      *
      *  Initializes the local params.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 04.06.2006<br />
      *  Version 0.4, 16.11.2008 (Removed the __registerLocal() method)<br />
      */
      function stat_v2_controller(){
         $this->_LOCALS = RequestHandler::getValues(array('pagepart' => 'overview','Year' => null,'Month' => null,'Day' => null,'Hour' => null));
       // end function
      }


      /**
      *  @public
      *
      *  Display the statistic data for the desired periods.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 04.06.2006<br />
      *  Version 0.2, 05.06.2006<br />
      *  Version 0.3, 15.11.2008 (Adapted to new business objects)<br />
      *  Version 0.4, 16.11.2008 (Optimize some parts of the code)<br />
      */
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


         // get the business component and read the statistic data
         $sM = &$this->__getServiceObject('sites::apfdocupage::biz','StatManager');
         $list = $sM->readStatistic(
                                     $this->_LOCALS['pagepart'],
                                     $this->_LOCALS['Year'],
                                     $this->_LOCALS['Month'],
                                     $this->_LOCALS['Day'],
                                     $this->_LOCALS['Hour']
                                    );

         // generate the output
         $sectionBuffer = (string)'';

         for($i = 0; $i < count($list); $i++){

            // initialize section
            if(get_class($list[$i]) == strtolower('LinkTableStatSection')){
               $section = &$this->__getTemplate('LinkTableStatSection');
             // end if
            }
            else{
               $section = &$this->__getTemplate('StatSection');
             // end else
            }

            $section->setPlaceHolder('Title',$list[$i]->getAttribute('Title'));

            if($this->__templatePlaceholderExists($section,'Sum')){
               $section->setPlaceHolder('Sum',$list[$i]->getAttribute('Sum'));
             // end if
            }

            if($this->__templatePlaceholderExists($section,'Average')){
               $section->setPlaceHolder('Average',$list[$i]->getAttribute('Average'));
             // end if
            }

            // initialize entries template
            $entries = $list[$i]->getAttribute('Entries');

            // create entries buffer
            $entriesBuffer = (string)'';

            // get template
            $entriesTmpl = &$this->__getTemplate('Entry_LinkTableStatSection');

            for($j = 0; $j < count($entries); $j++){

               if(get_class($list[$i]) == strtolower('LinkTableStatSection')){

                  $entriesTmpl->setPlaceHolder('Link',$this->__generateLink($entries[$j]->getAttribute('DisplayText')));
                  $entriesTmpl->setPlaceHolder('Tooltip','');

                  // format the link text
                  if(!empty($this->_LOCALS['Year']) && empty($this->_LOCALS['Month'])){
                     $linkText = dateTimeManager::showMonthLabel($entries[$j]->getAttribute('DisplayText'));
                   // end elseif
                  }
                  elseif(!empty($this->_LOCALS['Month']) && empty($this->_LOCALS['Day'])){
                     $linkText = dateTimeManager::addLeadingZero($entries[$j]->getAttribute('DisplayText')).'.'.dateTimeManager::addLeadingZero($this->_LOCALS['Month']).'.'.$this->_LOCALS['Year'];
                   // end elseif
                  }
                  elseif(!empty($this->_LOCALS['Day']) && empty($this->_LOCALS['Hour'])){
                     $linkText = dateTimeManager::addLeadingZero($entries[$j]->getAttribute('DisplayText')).':00 - '.dateTimeManager::addLeadingZero(intval($entries[$j]->getAttribute('DisplayText') + 1)).':00';
                   // end else
                  }
                  elseif(!empty($this->_LOCALS['Hour'])){
                     $linkText = dateTimeManager::addLeadingZero($this->_LOCALS['Hour']).':'.dateTimeManager::addLeadingZero($entries[$j]->getAttribute('DisplayText'));
                   // end elseif
                  }
                  else{
                     $linkText = $entries[$j]->getAttribute('DisplayText');
                   // end else
                  }
                  $entriesTmpl->setPlaceHolder('LinkText',$linkText);
                  $entriesTmpl->setPlaceHolder('Width',$entries[$j]->getAttribute('Value')/$list[$i]->getAttribute('Divisor'));
                  $entriesTmpl->setPlaceHolder('Count',$entries[$j]->getAttribute('Value'));

                // end if
               }
               elseif(get_class($list[$i]) == strtolower('TableStatSection')){

                  $entriesTmpl = &$this->__getTemplate('Entry_TableStatSection');
                  $entriesTmpl->setPlaceHolder('Text',$entries[$j]->getAttribute('DisplayText'));
                  $entriesTmpl->setPlaceHolder('Width',$entries[$j]->getAttribute('Value')/$list[$i]->getAttribute('Divisor'));
                  $entriesTmpl->setPlaceHolder('Count',$entries[$j]->getAttribute('Value'));

                // end if
               }
               else{

                  $entriesTmpl = &$this->__getTemplate('Entry_SimpleStatSection');
                  $entriesTmpl->setPlaceHolder('Text',$entries[$j]->getAttribute('DisplayText'));
                  $entriesTmpl->setPlaceHolder('Width',$entries[$j]->getAttribute('Value')/$list[$i]->getAttribute('Divisor'));
                  $entriesTmpl->setPlaceHolder('Count',$entries[$j]->getAttribute('Value'));

                // end if
               }

               $entriesBuffer .= $entriesTmpl->transformTemplate();

             // end for
            }

            $section->setPlaceHolder('Content',$entriesBuffer);
            $sectionBuffer .= $section->transformTemplate();

          // end for
         }

         $this->setPlaceHolder('Content',$sectionBuffer);

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