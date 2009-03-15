<?php
   import('tools::request','RequestHandler');
   import('tools::datetime','dateTimeManager');
   import('tools::link','linkHandler');
   import('sites::apfdocupage::biz','fulltextsearchManager');
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content
   *  @class search_controller
   *
   *  Implements the document controller for the search.html template.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 08.03.2008<br />
   *  Version 0.2, 03.10.2008 (Ported the controller to the new site structure)<br />
   */
   class search_controller extends baseController
   {

      function search_controller(){
      }


      /**
      *  @public
      *
      *  Displays the search result.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 08.03.2008<br />
      *  Version 0.2, 09.03.2008 (introduced business layer)<br />
      *  Version 0.3, 16.03.2008 (introduced deactivation for unknown hosts)<br />
      *  Version 0.4, 02.04.2008 (changed target of the search page. hence, this module is no more independent)<br />
      *  Version 0.5, 13.06.2008 (changed target link for english results on german pages)<br />
      *  Version 0.6, 03.10.2008 (Addapted to the new page structure; removed deactivation)<br />
      */
      function transformContent(){

         // register search content
         $_LOCALS = RequestHandler::getValues(array('search' => ''));

         // get registry and gather url mode
         $Reg = &Singleton::getInstance('Registry');
         $URLRewriting = $Reg->retrieve('apf::core','URLRewriting');
         $URLBasePath = $Reg->retrieve('apf::core','URLBasePath');

         // get model and gather current page information
         $Model = &$this->__getServiceObject('sites::apfdocupage::biz','APFModel');
         $PageIndicators = $Model->getAttribute('page.indicator');
         $Lang = $Model->getAttribute('page.language');
         $CurrPageIndicator = $PageIndicators[$Lang];
         $URLName = $Model->getAttribute('page.urlname');
         $PageID = $Model->getAttribute('page.id');

         // display form
         $Form__SearchV2 = &$this->__getForm('SearchV2');

         if($URLRewriting == true){
            $Form__SearchV2->setAttribute('action','/'.$CurrPageIndicator.'/'.$PageID.'-'.$URLName);
          // end if
         }
         else{
            $Form__SearchV2->setAttribute('action','./?'.$CurrPageIndicator.'='.$PageID.'-'.$URLName);
          // end else
         }

         $Form__SearchV2->transformOnPlace();


         // display results
         if(strlen($_LOCALS['search']) >= 3){

            // get manager
            $M = &$this->__getServiceObject('sites::apfdocupage::biz','fulltextsearchManager');

            // load results
            $SearchResults = $M->loadSearchResult($_LOCALS['search']);

            // load language config
            $Config = &$this->__getConfiguration('sites::apfdocupage::biz','language');

            // initialize buffer
            $Buffer = (string)'';

            // get template
            $Template__Result = &$this->__getTemplate('Result');

            // Ausgabe erzeugen
            $count = count($SearchResults);
            for($i = 0; $i < $count; $i++){

               // create link
               $Link = linkHandler::generateLink('',array($PageIndicators[$SearchResults[$i]->get('Language')] => $SearchResults[$i]->get('PageID').'-'.$SearchResults[$i]->get('URLName')));

               // display title
               $Template__Result->setPlaceHolder('Title','<a href="'.$Link.'" title="'.($SearchResults[$i]->get('Title')).'" style="font-size: 14px; font-weight: bold;">'.$SearchResults[$i]->get('Title').'</a>');

               // display content language
               $Language = $Config->getValue($this->__Language,'DisplayName.'.$SearchResults[$i]->get('Language'));
               $Template__Result->setPlaceHolder('Language',$Language);

               // diaplay last modifying date
               $Date = dateTimeManager::convertDate2Normal(substr($SearchResults[$i]->get('LastMod'),0,10));
               $Time = substr($SearchResults[$i]->get('LastMod'),11);
               $Template__Result->setPlaceHolder('LastMod',$Date.', '.$Time);

               // display word count of search word in article
               $Template__Result->setPlaceHolder('WordCount',$SearchResults[$i]->get('WordCount'));

               // display indexed word with highlight
               $IndexWord = preg_replace('/'.preg_quote($_LOCALS['search']).'/i','<span style="background-color: yellow;">'.strtolower($_LOCALS['search']).'</span>',$SearchResults[$i]->get('IndexWord'));
               $Template__Result->setPlaceHolder('IndexWord',$IndexWord);

               // add current result to list
               $Buffer .= $Template__Result->transformTemplate();

             // end for
            }

            // display "nothing found" if result count is null
            if($count < 1){

               // get template
               $Template__NoSearchResult = &$this->__getTemplate('NoSearchResult_'.$this->__Language);

               // add message to buffer
               $Buffer .= $Template__NoSearchResult->transformTemplate();

             // end if
            }

            // display buffer
            $this->setPlaceHolder('Result',$Buffer);

          // end if
         }

       // end function
      }

    // end class
   }
?>