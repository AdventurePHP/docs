<?php
   /**
    * @package sites::apf::data::indexer
    * @file indexer.php
    *
    * script wrapper file for the indexer service.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.03.2008<br />
    * Version 0.2, 16.03.2008<br />
    * Version 0.3, 03.10.2008 (Added some corrections)<br />
    * Version 0.4, 22.12.2009 (Ported to the new documentation page)<br />
    */

   // configure some php params
   ini_set('html_errors','off');
   error_reporting(E_ALL);
   ini_set('display_errors','On');
   set_time_limit(0);
   ini_set('memory_limit','300M');

   // include front controller
   import('core::frontcontroller','Frontcontroller');

   // include indexer class
   import('sites::apf::data::indexer','FulltextsearchIndexer');

   // create indexer
   $fSI = new FulltextsearchIndexer();
   $fSI->setContext('sites::apf');
   $fSI->setContentFolder('../apps/sites/apf/pres/content');

   // execute desired job
   $nothing2do = false;

   if(isset($_REQUEST['job'])) {

      if($_REQUEST['job'] == 'createindex') {
         $fSI->createIndex();
         // end if
      }
      elseif($_REQUEST['job'] == 'importarticles') {
         $fSI->importArticles();
         // end if
      }
      else {
         $nothing2do = true;
         // end else
      }

      // end if
   }
   else {
      $nothing2do = true;
      // end else
   }

   // print usage
   if($nothing2do == true) {
      echo 'Parameter "job" not filled, so there\'s nothing to do! Valid jobs are "createindex" and "importarticles".';
      // end if
   }
?>