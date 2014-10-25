<?php
namespace DOCS\pres\filter\output;

use APF\core\filter\ChainedContentFilter;
use APF\core\filter\FilterChain;

/**
 * Implements a custom output filter, that kills whitespaces and newlines
 * marked by JAVA scriptlet tags (<% ... %>).
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
