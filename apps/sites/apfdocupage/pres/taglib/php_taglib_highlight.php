<?php
   /**
   *  @package sites::demosite::pres::taglib
   *  @module php_taglib_highlight
   *
   *  Implementiert die Tag-Library für das PHP-Code-Highlightning.<br />
   *
   *  @author Christian Schäfer
   *  @version
   *  Version 0.1, 08.04.2007<br />
   */
   class php_taglib_highlight extends Document
   {

      function php_taglib_highlight(){
      }


      /**
      *  @module transform()
      *  @public
      *
      *  Implementiert die Interface-Methode "transform()", mit dem ein Objekt in HTML transformiert wird.<br />
      *
      *  @author Christian Schäfer
      *  @version
      *  Version 0.1, 08.04.2007<br />
      *  Version 0.2, 05.05.2007 (PHP-Tags werden nun nicht mehr mit ausgegeben)<br />
      *  Version 0.3, 01.07.2007 (Probleme mit verschobenen Quelltexten behoben. Klasse "div.phpcode" wird zur Formatierung verwendet)<br />
      *  Version 0.4, 21.08.2007 (Rudimentärer PHP-5-Support, da highlight_string() dort anderes tickt)<br />
      *  Version 0.5, 16.09.2007 (PHP-5-Support verbessert)<br />
      *  Version 0.6, 02.01.2008 (Höhe der Code-Box auf 400px begrenzt)<br />
      */
      function transform(){

         // Anzahl der Zeilen speichern
         $LineCount = substr_count($this->__Content,"\n") - 1;

         // Quelltext highlighten
         // - Zeilenumbrüche am Anfang entfernen
         // - Leerzeichen und Zeilenumbrüche am Ende entfernen
         // - Leerzeichen und Zeilenumbrüche um den kompletten Text entfernen
         $HighlightedContent = highlight_string(trim('<?php '.ltrim(rtrim($this->__Content),"\x0A..\x0D").' ?>'),true);

         // PHP-Anfangstag ersetzen
         $HighlightedContent = str_replace('<font color="#007700">&lt;?</font>','',$HighlightedContent);
         $HighlightedContent = str_replace('<font color="#0000BB">&lt;?php&nbsp;','<font color="#0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<font color="#0000BB">php','<font color="#0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<font color="#0000BB">&nbsp;</font>','',$HighlightedContent);

         // Erweiterung für PHP 5-Verhalten
         $HighlightedContent = str_replace('<span style="color: #0000BB">&lt;?php&nbsp;','<span style="color: #0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<span style="color: #0000BB">&lt;?php','<span style="color: #0000BB">',$HighlightedContent);
         $HighlightedContent = str_replace('<span style="color: #0000BB">?&gt;</span>','',$HighlightedContent);

         // PHP-Endtag ersetzen
         $HighlightedContent = str_replace('<font color="#0000BB">?&gt;</font>','',$HighlightedContent);

         // Code im DIV zurückgeben (Höhe begrenzen, falls notwenig)
         if($LineCount > 27){
            return '<div class="phpcode" style="height: 400px; overflow: auto;">'.$HighlightedContent.'</div>';
          // end if
         }
         else{
            return '<div class="phpcode">'.$HighlightedContent.'</div>';
          // end else
         }

       // end function
      }

    // end class
   }
?>