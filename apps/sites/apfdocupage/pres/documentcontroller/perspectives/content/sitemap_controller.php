<?php
   import('sites::apfdocupage::biz','fulltextsearchManager');
   import('tools::link','linkHandler');
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::content
   *  @class sitemap_controller
   *
   *  Implements the sitemap document controller.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 19.09.2008<br />
   */
   class sitemap_controller extends baseController
   {


      function sitemap_controller(){
      }


      /**
      *  @public
      *
      *  Displays the sitemap list.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 24.03.2008<br />
      *  Version 0.2, 31.08.2008 (Removed server quick hack)<br />
      *  Version 0.3, 31.08.2008 (Changed link generation)<br />
      *  Version 0.4, 19.09.2008 (Moved to sites namespace due to the strong dependency to the APFModel)<br />
      */
      function transformContent(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // get fulltextsearch manager
         $M = &$this->__getServiceObject('sites::apfdocupage::biz','fulltextsearchManager');

         // load pages
         $Pages = $M->loadPages();

         // gather current page indicator
         $Language = $Model->getAttribute('page.language');
         $PageIndicators = $Model->getAttribute('page.indicator');
         $CurrentPageIndicator = $PageIndicators[$Language];

         // create page link output
         $count = count($Pages);
         $Buffer = (string)'';
         $Template__Page = &$this->__getTemplate('Page');

         for($i = 0; $i < $count; $i++){

            // set page title
            $Template__Page->setPlaceHolder('Title',utf8_encode($Pages[$i]->get('Title')));

            // build link
            $URLName = $Pages[$i]->get('URLName');
            $PageID = $Pages[$i]->get('PageID');
            $Template__Page->setPlaceHolder('Link',linkHandler::generateLink('',array($CurrentPageIndicator => $PageID.'-'.$URLName)));

            // set last mod
            $Template__Page->setPlaceHolder('LastMod',$Pages[$i]->get('LastMod'));

            // display current page
            $Buffer .= $Template__Page->transformTemplate();

          // end for
         }

         // display list
         $this->setPlaceHolder('PageList',$Buffer);

       // end function
      }

    // end class
   }
?>