<?php
namespace DOCS\biz\statistics;

use APF\core\pagecontroller\APFObject;

/**
 * Implements the basic stat section domain object.
 */
class BaseStatSection extends APFObject {

   /**
    * @var string[] $attributes
    */
   protected $attributes = [];

   public function __construct() {
      $this->attributes['Title'] = (string) '';
      $this->attributes['Divisor'] = (float) 0;
      $this->attributes['Entries'] = [];
      $this->attributes['Average'] = (string) '';
      $this->attributes['Sum'] = (string) '';
   }

   public function getAttribute($name, $default = null) {
      return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
   }

   public function setAttribute($name, $value) {
      $this->attributes[$name] = $value;

      return $this;
   }

}
