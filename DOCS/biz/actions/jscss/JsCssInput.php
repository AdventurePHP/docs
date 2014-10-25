<?php
namespace DOCS\biz\actions\jscss;

use APF\core\frontcontroller\FrontcontrollerInput;

/**
 * Represents the action's input object.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 15.12.2009<br />
 */
class JsCssInput extends FrontcontrollerInput {

   private static $FILE = 'file';
   private static $TYPE = 'type';
   private static $EXT_CSS = 'css';
   private static $EXT_JS = 'js';

   public function getFileName() {
      if ($this->isCssFileRequested()) {
         $ext = self::$EXT_CSS;
      } else {
         $ext = self::$EXT_JS;
      }

      return $this->getParameter(self::$FILE) . '.' . $ext;
   }

   public function isCssFileRequested() {
      return $this->getParameter(self::$TYPE) === self::$EXT_CSS ? true : false;
   }

   public function isJsFileRequested() {
      return $this->getParameter(self::$TYPE) === self::$EXT_JS ? true : false;
   }

}
