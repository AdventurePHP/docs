<?php
/**
 * @class StatEntry
 *
 * Represents a single stat entry within a stat section.
 */
class StatEntry extends APFObject {

   public function __construct() {
      $this->attributes['DisplayText'] = (string)'n/a';
      $this->attributes['Link'] = (string)'';
      $this->attributes['Value'] = (string)'';
   }

}
