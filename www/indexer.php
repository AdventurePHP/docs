<?php
include_once('../apps/core/pagecontroller/pagecontroller.php');

// include front controller
import('core::frontcontroller', 'Frontcontroller');

// configure page values (to avoid rendering errors during index creation!)
Registry::register('sites::apf', 'Releases.LocalDir', 'D:/Entwicklung/Dokumentation/Build/RELEASES');
Registry::register('sites::apf', 'Releases.BaseURL', 'http://files.adventure-php-framework.org');
Registry::register('sites::apf', 'ForumBaseURL', 'http://forum.adventure-php-framework.org');
Registry::register('sites::apf', 'WikiBaseURL', 'http://wiki.adventure-php-framework.org');

// special output filter (to filter scriptlet tags out of the index!)
import('sites::apf::pres::filter::output', 'ScriptletOutputFilter');
OutputFilterChain::getInstance()->addFilter(new ScriptletOutputFilter());

import('sites::apf::data::indexer', 'indexer');
?>