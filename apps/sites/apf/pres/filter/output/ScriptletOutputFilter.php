<?php
   import('core::filter','GenericOutputFilter');

   /**
    * @package sites::apf::pres::filter::output
    * @class ScriptletOutputFilter
    *
    * Implements a custom output filter, that kills whitspaces and newlines
    * maked by JAVA scriptlet tags (<% ... %>).
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 15.12.2009<br />
    */
   final class ScriptletOutputFilter extends GenericOutputFilter {

      public function filter($input) {

         $input = parent::filter($input);

         // remove scriptlet tags
         $t = Singleton::getInstance('BenchmarkTimer');
         $reportId = 'ScriptletOutputFilter::removeScriptletTags()';
         $t->start($reportId);
         $input = preg_replace('/<%([^%>]+)%>/ims','',$input);
         $t->stop($reportId);

         return $input;

       // end function
      }

    // end class
   }
?>