<?php
/**
 * @class BaseStatSection
 *
 * Implements the basic stat section domain object.
 */
class BaseStatSection extends APFObject {

   public function __construct() {

      $this->__Attributes['Title'] = (string)'';
      $this->__Attributes['Divisor'] = (float)0;
      $this->__Attributes['Entries'] = array();
      $this->__Attributes['Average'] = (string)'';
      $this->__Attributes['Sum'] = (string)'';

   }

}
