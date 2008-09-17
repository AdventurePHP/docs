<?php
   import('sites::apfdocupage::biz','APFModel');
   import('tools::variablen','variablenHandler');


   /**
   *  @package sites::apfdocupage::biz::actions::setmodel
   *  @class SetModelAction
   *
   *  Represents the front controller action to initialize the page's model.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 22.08.2008<br />
   */
   class SetModelAction extends AbstractFrontcontrollerAction
   {

      function SetModelAction(){
      }


      /**
      *  @public
      *
      *  Implements the abstract run method. Initializes the model.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.08.2008<br />
      */
      function run(){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // register request parameters
         $PageIndicatorNames = $Model->getAttribute('page.indicator');
         $PageIndicatorNameDE = $PageIndicatorNames['de'];
         $PageIndicatorNameEN = $PageIndicatorNames['en'];
         $CurrentPageIndicators = variablenHandler::registerLocal(array($PageIndicatorNameDE => null,$PageIndicatorNameEN => null));

         // check request parameters and set current language
         if($CurrentPageIndicators[$PageIndicatorNameDE] != null){
            $Model->setAttribute('page.language','de');
            $this->__ParentObject->set('Language','de');
            $Model->setAttribute('perspective.name','content');
          // end if
         }
         elseif($CurrentPageIndicators[$PageIndicatorNameEN] != null){
            $Model->setAttribute('page.language','en');
            $this->__ParentObject->set('Language','en');
            $Model->setAttribute('perspective.name','content');
          // end if
         }
         else{

            // use default language of the front controller (maybe a problem, perhaps use
            // a session instance to store the language)
            $Language = $this->__ParentObject->get('Language');
            $Model->setAttribute('page.language',$Language);
            $Model->setAttribute('perspective.name','start');

          // end else
         }

         // fill current page id and title
         $PageName = $CurrentPageIndicators[$PageIndicatorNames[$Model->getAttribute('page.language')]];
         $PageID = substr($PageName,0,3);
         $Model->setAttribute('page.id',$PageID);

         // fill the current title
         $PageTitle = str_replace('-',' ',substr($PageName,4));
         $Model->setAttribute('page.title',$PageTitle);

       // end function
      }

    // end class
   }
?>