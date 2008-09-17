<?php
   import('tools::variablen','variablenHandler');
   import('sites::demosite::biz','DemositeModel');


   /**
   *  @package sites::demosite::pres::taglib
   *  @module html_taglib_content
   *
   *  Implementiert die TagLib für den Tag "html:content".<br />
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 28.03.2008<br />
   */
   class html_taglib_content extends Document
   {

      /**
      *  @private
      *  Sprachebhängige URL-Parameter.
      */
      var $__PageParams = array();


      /**
      *  @private
      *  Sprachebhängige Startseiten-Parameter.
      */
      var $__PageDefaults = array();


      /**
      *  @private
      *  Pfad zu den Content-Dateien.
      */
      var $__ContentFilePath = 'd:/Apache2/htdocs/www/demosite.de/frontend/content';


      /**
      *  @module html_taglib_content()
      *  @public
      *
      *  Konstruktor der Klasse. Ruft den Konstruktor der Eltern-Klasse auf.<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 28.03.2008<br />
      */
      function html_taglib_content(){
         parent::Document();
       // end function
      }


      /**
      *  @module onParseTime()
      *  @public
      *
      *  Implementiert die abstrakte Methode onParseTime().<br />
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 28.03.2008<br />
      */
      function onParseTime(){

         // Model beziehen
         $Model = &$this->__getServiceObject('sites::demosite::biz','DemositeModel');


         // Seiten-Parameter auslesen
         $this->__PageParams = $Model->getAttribute('ReqParamName');
         $RequestParameter = $this->__PageParams[$this->__Language];


         // Standard-Parameter aulesen
         $this->__PageDefaults = $Model->getAttribute('DefaultPageName');
         $DefaultValue = $this->__PageDefaults[$this->__Language];


         // Parameter über variablenHandler initialisieren
         $_LOCALS = variablenHandler::registerLocal(array($RequestParameter => $DefaultValue));


         // Aktuellen Parameter auslesen
         $CurrentRequestParameter = $_LOCALS[$RequestParameter];


         // Request-Parameter übersetzen
         preg_match('~^([0-9]+).*~',$CurrentRequestParameter,$Matches);
         if(!isset($Matches[1])){
            $PageID = '404';
          // end if
         }
         else{
            $PageID = $Matches[1];
          // end else
         }
         $Model->setAttribute('CurrentPageID',$PageID);


         // Content des Objekts setzen
         $this->__Content = $this->__getContent($this->__getFileName($PageID));


         // Tags extrahieren
         $this->__extractTagLibTags();


         // DocumentController extrahieren
         $this->__extractDocumentController();

       // end if
      }


      /**
      *  @module __getContent()
      *  @private
      *
      *  Liest den Inhalt einer Seite aus der zugehörigen Datei aus. Im Fehler-<br />
      *  Fall wird eine 404-Seite angezeigt.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 28.03.2008 (Kompletter Dateiname ist nun Parameter)<br />
      */
      function __getContent($FileName){

         // Pfad zusammensetzen
         $Datei = $this->__ContentFilePath.'/'.$FileName;

         // Inhalt der Datei zurückgeben
         return file_get_contents($Datei);

       // end function
      }


      /**
      *  @module __getFileName()
      *  @public
      *
      *  Sucht den passenden Dateinamen zur ID.<br />
      *
      *  @param string $PageID; ID der Seite
      *  @return string $FileName; Dateinamen der Seite | null
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 28.03.2008<br />
      */
      function __getFileName($PageID){

         $Files = glob($this->__ContentFilePath.'/c_'.$this->__Language.'_'.$PageID.'*');
         if(!isset($Files[0])){
            return 'c_'.$this->__Language.'_404.html';
          // end if
         }
         else{
            return basename($Files[0]);
          // end else
         }

       // end function
      }

    // end class
   }
?>