<?php
namespace APF\sites\apf\pres\taglib;

use APF\core\pagecontroller\Document;

/**
 * @package APF\sites\apf\pres\taglib
 * @class GenericHighlightTag
 *
 * Implements a generic code highlighting taglib.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 03.01.2010<br />
 */
class GenericHighlightTag extends Document {

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
   public function transform() {

      // gather the type of the code box
      $type = $this->getAttribute('type');

      $title = 'Code';
      $cssClass = self::$GENERIC;
      $content = trim($this->content);
      switch ($type) {

         case self::$HTML:
            $title = 'Html-Code';
            $cssClass = self::$HTML;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$SHELL:
            $title = 'Shell';
            $cssClass = self::$SHELL;
            break;

         case self::$APF_XML:
            $title = 'APF-Template';
            $cssClass = self::$APF_XML;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$PHP:
            $title = 'PHP-Code';
            $cssClass = self::$PHP;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$XML:
            $title = 'XML-Code';
            $cssClass = self::$XML;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$INI:
            $title = 'APF-Konfiguration';
            $cssClass = self::$INI;
            break;

         case self::$CSS:
            $title = 'CSS-Code';
            $cssClass = self::$CSS;
            break;

         case self::$SQL:
            $title = 'SQL-Statement';
            $cssClass = self::$SQL;
            break;

         default:
            break;

      }

      return '<div class="listing ' . $cssClass . '"><div class="codeHeading">' . $title . '</div><code>' . $content . '</code></div>';
   }

}
