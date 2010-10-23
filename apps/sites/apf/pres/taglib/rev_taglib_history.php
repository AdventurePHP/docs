<?php
   import('sites::apf::pres::controller::release','release_base_controller');

   /**
    * @package sites::apf::pres::taglib
    * @class rev_taglib_history
    *
    * Displays the revision history for the desired release.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 30.12.2009<br />
    */
   class rev_taglib_history extends Document {

      private static $FALLBACK_RELEASE_PARAM = 'fallback';

      /**
       * @public
       *
       * Initializes the known taglibs.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 30.12.2009<br />
       */
      public function __construct(){
         parent::__construct();
         $this->__TagLibs[] = new Taglib('sites::apf::pres::taglib','int','link');
         $this->__TagLibs[] = new Taglib('sites::apf::pres::taglib','doku','link');
         $this->__TagLibs[] = new Taglib('sites::apf::pres::taglib','gen','highlight');
      }

      public function onParseTime(){

         // get the desired release
         $release = $this->getReleaseNumber();

         // append the content to the current document and re-analyze the structure (link tags!)
         $this->__Content = '<div id="RevisionHistory">'.PHP_EOL;
         $this->__Content .= $this->getReleaseHeader($release);
         $this->__Content .= $this->getReleaseDescription($release);
         $this->__Content .= '</div>'.PHP_EOL;
         $this->__extractTagLibTags();

       // end function
      }

      public function onAfterAppend(){
      }

      private function getReleaseHeader($releaseNumber){
         $title = (string)'<h2>';
         $config = $this->getConfiguration('sites::apf::pres','labels');
         $title .= $config->getSection($this->getLanguage())->getValue('downloads.changeset.text.heading');
         return $title.$releaseNumber.'</h2>';
      }

      /**
       * @private
       *
       * Returns the formatted text of the release description file. Resolves unencoded ampersands.
       *
       * @param string $releaseNumber The desired release to display the history of.
       * @return string The revision history text from the release file.
       *
       * @author Christian Achatz
       * @version
       * Version 0.1, 30.12.2009<br />
       */
      private function getReleaseDescription($releaseNumber){
         $relLocalDir = Registry::retrieve('sites::apf','Releases.LocalDir');
         $historyFile = $relLocalDir.'/'.$releaseNumber.'/'.$this->__Language.'_release_description.html';
         if(file_exists($historyFile)){
            return preg_replace('/&([A-Za-z0-9\-_]+)=([A-Za-z0-9\-_]+)/','&amp;$1=$2',file_get_contents($historyFile));
         }
         return (string)'';
      }

      /**
       * @return string The release number contained in the url, or a fallback number form the tag definition.
       */
      private function getReleaseNumber(){
         $release = RequestHandler::getValue(release_base_controller::$REV_HISTORY_PARAM);
         if($release === null){
            $release = $this->getAttribute(self::$FALLBACK_RELEASE_PARAM);
         }
         return $release;
      }

    // end class
   }
?>