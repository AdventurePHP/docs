<?php
   import('tools::datetime','dateTimeManager');
   import('tools::link','LinkHandler');
   import('sites::apf::biz','FulltextsearchManager');
   import('sites::apf::biz','APFModel');
   import('tools::request','RequestHandler');

   /**
    * @package sites::apf::pres::controller
    * @class search_controller
    *
    * Implements the document controller for the search.html template.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 08.03.2008<br />
    * Version 0.2, 03.10.2008 (Ported the controller to the new site structure)<br />
    */
   class search_controller extends baseController {

      function search_controller(){
      }

      /**
       * @public
       *
       * Displays the search result.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 08.03.2008<br />
       * Version 0.2, 09.03.2008 (introduced business layer)<br />
       * Version 0.3, 16.03.2008 (introduced deactivation for unknown hosts)<br />
       * Version 0.4, 02.04.2008 (changed target of the search page. hence, this module is no more independent)<br />
       * Version 0.5, 13.06.2008 (changed target link for english results on german pages)<br />
       * Version 0.6, 03.10.2008 (Addapted to the new page structure; removed deactivation)<br />
       */
      function transformContent(){

         // register search content
         $_LOCALS = RequestHandler::getValues(array('search' => ''));

         // get registry and gather url mode
         $reg = &Singleton::getInstance('Registry');
         $urlRewriting = $reg->retrieve('apf::core','URLRewriting');
         $urlRewritingBasePath = $reg->retrieve('apf::core','URLBasePath');

         // get model and gather current page information
         $model = &$this->__getServiceObject('sites::apf::biz','APFModel');
         $pageIndicators = $model->getAttribute('page.indicator');
         $lang = $model->getAttribute('page.language');
         $currPageIndicator = $pageIndicators[$lang];
         $urlName = $model->getAttribute('page.urlname');
         $pageId = $model->getAttribute('page.id');

         // display form
         $form = &$this->__getForm('SearchV2');

         if($urlRewriting == true){
            $form->setAttribute('action','/'.$currPageIndicator.'/'.$pageId.'-'.$urlName);
          // end if
         }
         else{
            $form->setAttribute('action','./?'.$currPageIndicator.'='.$pageId.'-'.$urlName);
          // end else
         }
         $form->transformOnPlace();

         // display results
         if(strlen($_LOCALS['search']) >= 3){

            // get manager
            $m = &$this->__getServiceObject('sites::apf::biz','FulltextsearchManager');

            // load results
            $searchResults = $m->loadSearchResult($_LOCALS['search']);

            // load language config
            $config = &$this->__getConfiguration('sites::apf::biz','language');

            // initialize buffer
            $buffer = (string)'';

            // get template
            $template = &$this->__getTemplate('Result');

            // Ausgabe erzeugen
            $count = count($searchResults);
            for($i = 0; $i < $count; $i++){

               // create link
               $link = LinkHandler::generateLink('',array($pageIndicators[$searchResults[$i]->get('Language')] => $searchResults[$i]->get('PageID').'-'.$searchResults[$i]->get('URLName')));

               // display title
               $template->setPlaceHolder('Title','<a href="'.$link.'" title="'.($searchResults[$i]->get('Title')).'" style="font-size: 14px; font-weight: bold;">'.$searchResults[$i]->get('Title').'</a>');

               // display content language
               $resultLang = $config->getValue($this->__Language,'DisplayName.'.$searchResults[$i]->get('Language'));
               $template->setPlaceHolder('Language',$resultLang);

               // diaplay last modifying date
               $date = dateTimeManager::convertDate2Normal(substr($searchResults[$i]->get('LastMod'),0,10));
               $time = substr($searchResults[$i]->get('LastMod'),11);
               $template->setPlaceHolder('LastMod',$date.', '.$time);

               // display word count of search word in article
               $template->setPlaceHolder('WordCount',$searchResults[$i]->get('WordCount'));

               // display indexed word with highlight
               $indexWord = preg_replace('/'.preg_quote($_LOCALS['search']).'/i','<span style="background-color: yellow;">'.strtolower($_LOCALS['search']).'</span>',$searchResults[$i]->get('IndexWord'));
               $template->setPlaceHolder('IndexWord',$indexWord);

               // add current result to list
               $buffer .= $template->transformTemplate();

             // end for
            }

            // display "nothing found" if result count is null
            if($count < 1){

               // get template
               $Template__NoSearchResult = &$this->__getTemplate('NoSearchResult_'.$this->__Language);

               // add message to buffer
               $buffer .= $Template__NoSearchResult->transformTemplate();

             // end if
            }

            // display buffer
            $this->setPlaceHolder('Result',$buffer);

          // end if
         }

       // end function
      }

    // end class
   }
?>