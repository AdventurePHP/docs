<?php
   import('modules::socialbookmark::pres::taglib','social_taglib_bookmark');


   /**
   *  @package sites::demosite::pres::taglib
   *  @module apf_taglib_socialbookmark
   *
   *  Implementiert die Tag-Library für das Erzeugen der Socialbookmark-Ausgabe.<br />
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 25.05.2008<br />
   */
   class apf_taglib_socialbookmark extends social_taglib_bookmark
   {

      function apf_taglib_socialbookmark(){
         parent::social_taglib_bookmark();
       // end function
      }


      /**
      *  @module transform
      *  @public
      *
      *  Erzeugt die Ausgabe mit Hilfe des BookmarkManager. Wrapper des Tags im Modul.<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 25.05.2008<br />
      */
      function transform(){

         // Model beziehen
         $Model = &$this->__getServiceObject('sites::demosite::biz','DemositeModel');

         // Model-Werte auslesen
         $ReqParamName = $Model->getAttribute('ReqParamName');
         $DefaultPageName = $Model->getAttribute('DefaultPageName');
         $RequestParameter = $ReqParamName[$this->__Language];
         $DefaultValue = $DefaultPageName[$this->__Language];

         // Werte lokal registrieren
         $_LOCALS = variablenHandler::registerLocal(array($RequestParameter => $DefaultValue));

         // Title setzen
         $this->__Attributes['title'] = 'Adventure PHP Framework - '.str_replace('-',' ',substr($_LOCALS[$RequestParameter],strpos($_LOCALS[$RequestParameter],'-') + 1));

         // Ausgabe erzeugen
         return parent::transform();

       // end function
      }

    // end class
   }
?>