<?php
   /**
   *  @class StatEntry
   *
   *  Represents a single stat entry within a stat section.
   *
   */
   class StatEntry extends coreObject
   {

      function StatEntry(){
         $this->__Attributes['DisplayText'] = (string)'n/a';
         $this->__Attributes['Link'] = (string)'';
         $this->__Attributes['Value'] = (string)'';
       // end function
      }

    // end class
   }
?>