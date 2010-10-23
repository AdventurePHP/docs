<?php
   include_once('../apps/core/pagecontroller/pagecontroller.php');

   // configure page values (to avoid rendering errors during index creation!)
   Registry::register('sites::apf','Releases.LocalDir','D:/Entwicklung/Dokumentation/Build/RELEASES');
   Registry::register('sites::apf','Releases.BaseURL','http://files.adventure-php-framework.org');
   Registry::register('sites::apf','ForumBaseURL','http://forum.adventure-php-framework.org');
   Registry::register('sites::apf','WikiBaseURL','http://wiki.adventure-php-framework.org');

   // special output filter (to filter scriptlet tags out of the index!)
   Registry::register(
      'apf::core::filter',
      'OutputFilter',
      new FilterDefinition('sites::apf::pres::filter::output','ScriptletOutputFilter')
   );

   import('sites::apf::data::indexer','indexer');
?>