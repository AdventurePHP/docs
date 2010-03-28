<?php
   /**
    * @package sites::apf::pres::taglib
    * @class gen_taglib_highlight
    *
    * Implements a generic code highlight taglib.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 03.01.2010<br />
    */
   class gen_taglib_highlight extends Document {

      private static $CSS = 'css';
      private static $SHELL = 'shell';
      private static $PHP = 'php';
      private static $HTML = 'html';
      private static $INI = 'ini';
      private static $SQL = 'sql';
      private static $XML = 'xml';
      private static $APF_XML = 'apf-xml';
      private static $GENERIC = 'generic';

      /**
       * @public
       *
       * Displays the code box concerning the given type.
       *
       * @return string The code box markup.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 03.01.2010<br />
       * Version 0.2, 18.03.2010 (Added css markup code class)<br />
       */
      public function transform(){

         // gather the type of the code box
         $type = $this->getAttribute('type');

         $title = (string)'Code';
         $cssClass = (string) self::$GENERIC;
         //$cssClass = (string) self::$PHP;
         $content = (string) trim($this->__Content);
         switch($type){

            case self::$HTML:
               $title = 'Html-Code';
               $cssClass = self::$HTML;
               $content = str_replace('<','&lt;',str_replace('>','&gt;',trim($this->__Content)));
               break;

            case self::$SHELL:
               $title = 'Shell';
               $cssClass = self::$SHELL;
               break;

            case self::$APF_XML:
               $title = 'APF-Template';
               $cssClass = self::$APF_XML;
               $content = str_replace('<','&lt;',str_replace('>','&gt;',trim($this->__Content)));
               break;

            case self::$PHP:
               $title = 'PHP-Code';
               $cssClass = self::$PHP;
               $content = str_replace('&','&amp;',trim($this->__Content));
               break;

            case self::$XML:
               $title = 'XML-Code';
               $cssClass = self::$XML;
               break;

            case self::$INI:
               $title = 'APF-Konfiguration';
               $cssClass = self::$INI;
               break;

            case self::$CSS:
               $title = 'CSS-Code';
               $cssClass = self::$CSS;
               break;

            default:
               break;
               
          // end switch
         }

         return '<div class="listing '.$cssClass.'"><div class="codeHeading">'.$title.'</div><code>'.$content.'</code></div>';

       // end function
      }

    // end class
   }
?>