<?php
namespace DOCS\biz\statistics;

use APF\core\pagecontroller\APFObject;

/**
 * Represents a single stat entry within a stat section.
 */
class StatEntry extends APFObject {

   /**
    * @var string[] $attributes
    */
   protected $attributes = [];

   public function __construct() {
      $this->attributes['DisplayText'] = (string) 'n/a';
      $this->attributes['Link'] = (string) '';
      $this->attributes['Value'] = (string) '';
   }

   public function getAttribute($name, $default = null) {
      return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
   }

   public function setAttribute($name, $value) {
      $this->attributes[$name] = $value;

      return $this;
   }

}
