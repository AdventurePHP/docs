<?php
namespace DOCS\pres\taglib;

use APF\core\pagecontroller\Document;

/**
 * Injects Google analytics tracking into APF page.
 */
class TrackingTag extends Document {

   public function transform() {

      if (!$this->isTrackingEnabled()) {
         return '';
      }

      return '<script>
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');

  ga(\'create\', \'UA-68677271-1\', \'auto\');
  ga(\'send\', \'pageview\');

</script>';
   }

   private function isTrackingEnabled() {
      return $_SERVER['HTTP_HOST'] === 'adventure-php-framework.org';
   }

}
