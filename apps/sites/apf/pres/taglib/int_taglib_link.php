<?php
   /**
    * @package sites::apf::pres::taglib
    * @class int_taglib_link
    *
    * This taglib let's you easily create internal links. Depending on
    * the attributes given, the link is generated using the title of the
    * target page or a custom one.
    * <p/>
    * Tag signature:
    * <pre>
    * &lt;int:link pageid=""[ anchor=""][ title=""][ lang=""][ target=""][ id=""][ class=""][ params=""]/&gt;
    * &lt;int:link pageid=""[ anchor=""][ title=""][ lang=""][ target=""][ id=""][ class=""][ params=""]&gt;Link text&lt;/int:link&gt;
    * </pre>
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 23.12.2009<br />
    */
   class int_taglib_link extends Document {

      public function transform(){

         $pageId = $this->getAttribute('pageid');
         if($pageId === null){
            trigger_error('[int_taglib_link::transform()] Attribute "pageid" must not be null!',E_USER_ERROR);
            exit(1);
         }

         // setup language
         $lang = $this->getAttribute('lang');
         if($lang === null){
            $lang = $this->__Language;
         }

         // setup link text
         $urlMan = &$this->__getServiceObject('sites::apf::biz','UrlManager');
         $linkText = $this->getLinkText();
         if($linkText === null){
            $linkText = $urlMan->getPageTitle($pageId,$lang);
         }

         // setup link title
         $title = $this->getAttribute('title');
         if($title === null){
            $title = $linkText;
         }

         // generate link
         $link = $urlMan->generateLink($pageId,$lang);
         $params = $this->getAttribute('params');
         if($params !== null){
            $link .= $params;
         }
         $anchor = $this->getAttribute('anchor');
         if($anchor !== null){
            $link .= '#'.$anchor;
         }

         // resolve and add target if desired
         $target = $this->getAttribute('target');
         if($target !== null){
            $target = ' target="'.$target.'"';
         }
         else{
            $target = '';
         }

         // enable id and class attributes
         $id = $this->getAttribute('id');
         $class = $this->getAttribute('class');
         if($id !== null){
            $id = ' id="'.$id.'"';
         }
         if($class !== null){
            $class = ' class="'.$class.'"';
         }

         return '<a href="'.$link.'" title="'.$title.'"'.$target.$id.$class.'>'.$linkText.'</a>';

       // end function
      }

      private function getLinkText(){
         if(empty($this->__Content)){
            return null;
         }
         return $this->__Content;
      }

    // end class
   }
?>