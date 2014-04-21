<?php
namespace DOCS\biz\statistics;

use APF\core\pagecontroller\APFObject;

/**
 * @class BaseStatSection
 *
 * Implements the basic stat section domain object.
 */
class BaseStatSection extends APFObject {

   public function __construct() {

      $this->attributes['Title'] = (string)'';
      $this->attributes['Divisor'] = (float)0;
      $this->attributes['Entries'] = array();
      $this->attributes['Average'] = (string)'';
      $this->attributes['Sum'] = (string)'';

   }

}
