<?php
   /**
   *  @class BaseStatSection
   *
   *  Implements the basic stat section domain object.
   *
   */
   class BaseStatSection extends APFObject {

      function BaseStatSection(){

         $this->__Attributes['Title'] = (string)'';
         $this->__Attributes['Divisor'] = (float) 0;
         $this->__Attributes['Entries'] = array();
         $this->__Attributes['Average'] = (string)'';
         $this->__Attributes['Sum'] = (string)'';

       // end function
      }

    // end class
   }
?>