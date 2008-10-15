<?php
   import('tools::variablen','variablenHandler');


   /**
   *  @package sites::demosite::pres::taglib
   *  @class file_taglib_highlight
   *  @deprecated
   *
   *  Implements the tag library to highlight php code from files.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 22.05.2007<br />
   *  Version 0.2, 18.09.2008 (Refactoring to fit new docu page)<br />
   */
   class file_taglib_highlight extends Document
   {

      /**
      *  @private
      *  Contains a list of known files.
      */
      var $__Files = array();


      /**
      *  @public
      *
      *  Defines the known files.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.05.2007<br />
      *  Version 0.2, 27.05.2007<br />
      *  Version 0.3, 24.06.2007<br />
      *  Version 0.4, 10.10.2207<br />
      *  Version 0.5, 05.02.2008<br />
      *  Version 0.6, 18.09.2008 (Refactoring)<br />
      */
      function file_taglib_highlight(){

         // get lib dir
         $Reg = &Singleton::getInstance('Registry');
         $LibPath = $Reg->retrieve('apf::core','LibPath');

         // define files
         $this->__Files = array(
                                // comment function tutorial
                                'ArticleComment.php' => $LibPath.'/modules/comments/biz/ArticleComment.php',
                                'listing.html' => $LibPath.'/modules/comments/pres/templates/listing.html',
                                'comment_listing_v1_controller.php' => $LibPath.'/modules/comments/pres/documentcontroller/comment_listing_v1_controller.php',
                                'form.html' => $LibPath.'/modules/comments/pres/templates/form.html',
                                'comment_form_v1_controller.php' => $LibPath.'/modules/comments/pres/documentcontroller/comment_form_v1_controller.php',

                                // contact form tutorial
                                'DEFAULT_language.ini' => $LibPath.'/config/modules/kontakt4/sites/apfdocupage/DEFAULT_language.ini',
                                'kontakt.html' => $LibPath.'/modules/kontakt4/pres/templates/kontakt.html',
                                'formular.html' => $LibPath.'/modules/kontakt4/pres/templates/formular.html',
                                'meldung.html' => $LibPath.'/modules/kontakt4/pres/templates/meldung.html',
                                'kontakt_v4_controller.php' => $LibPath.'/modules/kontakt4/pres/documentcontroller/kontakt_v4_controller.php',
                                'contactManager.php' => $LibPath.'/modules/kontakt4/biz/contactManager.php',
                                'contactMapper.php' => $LibPath.'/modules/kontakt4/data/contactMapper.php',

                                // user specific taglibs tutorial
                                'document_taglib_createcontentobject.php' => $LibPath.'/tools/html/taglib/doc_taglib_createobject.php',

                                // guestbook tutorial
                                'guestbookMapper.php' => $LibPath.'/modules/guestbook/data/guestbookMapper.php',
                                'guestbookManager.php' => $LibPath.'/modules/guestbook/biz/guestbookManager.php',
                                'display.html' => $LibPath.'/modules/guestbook/pres/templates/display.html',
                                'createentry.html' => $LibPath.'/modules/guestbook/pres/templates/createentry.html',
                                'guestbook.html' => $LibPath.'/modules/guestbook/pres/templates/guestbook.html',
                                'guestbook_v1_controller.php' => $LibPath.'/modules/guestbook/pres/documentcontroller/guestbook_v1_controller.php',
                                'guestbook_display_v1_controller.php' => $LibPath.'/modules/guestbook/pres/documentcontroller/guestbook_display_v1_controller.php',
                                'guestbook_createentry_v1_controller.php' => $LibPath.'/modules/guestbook/pres/documentcontroller/guestbook_createentry_v1_controller.php',
                                'adminlogin.html' => $LibPath.'/modules/guestbook/pres/templates/adminlogin.html',
                                'guestbook_adminlogin_v1_controller.php' => $LibPath.'/modules/guestbook/pres/documentcontroller/guestbook_adminlogin_v1_controller.php',
                                'adminlogin.html' => $LibPath.'/modules/guestbook/pres/templates/adminlogin.html',
                                'adminaddcomment.html' => $LibPath.'/modules/guestbook/pres/templates/adminaddcomment.html',
                                'guestbook_adminaddcomment_v1_controller.php' => $LibPath.'/modules/guestbook/pres/documentcontroller/guestbook_adminaddcomment_v1_controller.php',

                                // news pager AJAX tutorial
                                'newspagerMapper.php' => $LibPath.'/modules/newspager/data/newspagerMapper.php',
                                'newspagerContent.php' => $LibPath.'/modules/newspager/biz/newspagerContent.php',
                                'newspagerManager.php' => $LibPath.'/modules/newspager/biz/newspagerManager.php',
                                'newspagerAction.php' => $LibPath.'/modules/newspager/biz/actions/newspagerAction.php',
                                'newspagerInput.php' => $LibPath.'/modules/newspager/biz/actions/newspagerInput.php',
                                'TESTSERVER_actionconfig.ini' => $LibPath.'/config/modules/newspager/biz/actions/sites/apfdocupage/DEFAULT_actionconfig.ini'

                                // OLD STUFF, perhaps not needed any more
                                //'index.php' => 'index.php',
                                //'website.html' => $LibPath.'/sites/apfdocupage/pres/templates/site.html',
                                //'content.html' => $LibPath.'/sites/apfdocupage/pres/templates/content.html',
                                //'menu.html' => $LibPath.'/sites/apfdocupage/pres/templates/menu.html',
                                //'INIT_helloworld.ini' => $LibPath.'/config/core/applicationmanager/INIT_helloworld.ini',
                                //'helloworld.html' => $LibPath.'/sites/helloworld/pres/templates/helloworld.html',
                                //'helloworld.php' => 'helloworld.php',

                               );

       // end function
      }


      /**
      *  @public
      *
      *  Creates the output.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 22.05.2007<br />
      *  Version 0.2, 26.05.2007 (Corrected height calculation)<br />
      *  Version 0.3, 27.05.2007 (Added check, if file exists)<br />
      *  Version 0.4, 19.01.2007 (Height is not limited during print perspective)<br />
      *  Version 0.5, 18.09.2008 (Refactoring; removed print view; markec as deprecated)<br />
      */
      function transform(){

         // read file name attribute
         if(!isset($this->__Attributes['name']) || empty($this->__Attributes['name'])){
            return (string)'<pre class="filesource" style="height: 30px;"><strong>Attribute "name" is not set or empty!</strong></pre>';
          // end if
         }

         // check, if file is a known file
         if(!isset($this->__Files[$this->__Attributes['name']])){
            trigger_error('[file_taglib_highlight::transform()] File "'.$this->__Attributes['name'].'" is not allowed to be viewed!',E_USER_WARNING);
            return (string)'<pre class="filesource" style="height: 30px;"><strong>File "'.$this->__Attributes['name'].'" is not allowed to view!</strong></pre>';
          // end if
         }

         // read file of display error
         if(file_exists($this->__Files[$this->__Attributes['name']])){
            $SourceFileContent = file($this->__Files[$this->__Attributes['name']]);
          // end if
         }
         else{
            trigger_error('[file_taglib_highlight::transform()] File "'.$this->__Attributes['name'].'" cannot be found!',E_USER_WARNING);
            return (string)'<pre class="filesource" style="height: 30px;"><strong>File "'.$this->__Files[$this->__Attributes['name']].'" doesn\'t exist!</strong></pre>';
          // end else
         }

         // calculate the div's height
         // 16 px per line = 8px letter + 8px space
         $LineCount = count($SourceFileContent);
         $Height = ($LineCount * 8) + ($LineCount - 1) * 8;

         // define minimum height
         if($Height < 16){
            $Height = 28;
          // end if
         }

         // define maximum height
         if($Height > 500){
            $Height = 500;
          // end if
         }

         // create tag output
         $Buffer = (string)'';
         $Buffer .= '<pre class="phpcode" style="height: '.$Height.'px;">';
         $Buffer .= PHP_EOL;
         $Buffer .= highlight_string(implode('',$SourceFileContent),true);
         $Buffer .= PHP_EOL;
         $Buffer .= '</pre>';

         // return buffer
         return $Buffer;

       // end function
      }

    // end class
   }
?>