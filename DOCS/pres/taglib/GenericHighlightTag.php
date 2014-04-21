<?php
namespace DOCS\pres\taglib;

use APF\core\pagecontroller\Document;

/**
 * @package DOCS\pres\taglib
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
   private static $JAVA_SCRIPT = 'javascript';
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
            $title = $this->getLanguage() == 'de' ? 'Html-Code' : 'HTML code';
            $cssClass = self::$HTML;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$SHELL:
            $title = 'Shell';
            $cssClass = self::$SHELL;
            break;

         case self::$APF_XML:
            $title = $this->getLanguage() == 'de' ? 'APF-Template' : 'APF template';
            $cssClass = self::$APF_XML;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$PHP:
            $title = $this->getLanguage() == 'de' ? 'PHP-Code' : 'PHP code';
            $cssClass = self::$PHP;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$XML:
            $title = $this->getLanguage() == 'de' ? 'XML-Code' : 'XML code';
            $cssClass = self::$XML;
            $content = str_replace('<', '&lt;', str_replace('>', '&gt;', trim($this->content)));
            break;

         case self::$INI:
            $title = $this->getLanguage() == 'de' ? 'APF-Konfiguration' : 'APF configuration';
            $cssClass = self::$INI;
            break;

         case self::$CSS:
            $title = $this->getLanguage() == 'de' ? 'CSS-Code' : 'CSS code';
            $cssClass = self::$CSS;
            break;

         case self::$SQL:
            $title = $this->getLanguage() == 'de' ? 'SQL-Statement' : 'SQL statement';
            $cssClass = self::$SQL;
            break;

         case self::$JAVA_SCRIPT:
            $title = $this->getLanguage() == 'de' ? 'Java-Script-Code' : 'Java script code';
            $cssClass = self::$JAVA_SCRIPT;
            break;

         default:
            break;

      }

      return '<div class="listing ' . $cssClass . '"><div class="codeHeading">' . $title . '</div><code>' . $content . '</code></div>';
   }

}
