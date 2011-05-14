<?php
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
class ScriptletOutputFilter implements ChainedContentFilter {

   public function filter(FilterChain &$chain, $input = null) {
      // remove scriptlet tags
      return $chain->filter(preg_replace('/<%([^%>]+)%>/ims', '', $input));
   }

}
?>