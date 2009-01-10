<?php
   import('core::database','connectionManager');
   import('sites::apfdocupage::biz','APFModel');


   /**
   *  @package sites::apfdocupage::pres::documentcontroller::perspectives::start
   *  @class i_box_latest_controller
   *
   *  Implements the documentcontroller for the "i_box_latest.html" template.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 05.10.2008<br />
   */
   class i_box_latest_controller extends baseController
   {

      function i_box_latest_controller(){
      }


      /**
      *  @public
      *
      *  Reads the latest posts from the forum and displays them. Ths implementation is quick&dirty,
      *  because there is not business component for the web page.
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 05.10.2008<br />
      *  Version 0.2, 17.10.2008 (Added multilanguage support)<br />
      */
      function transformContent(){

         // get forum database connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQLForum = &$cM->getConnection('Forum');

         // read last two forum topics
         $select_posts = 'SELECT forum_id,topic_id,topic_title
                          FROM '.$this->__Language.'_phpbb3_topics
                          WHERE topic_type = 0 AND topic_moved_id = 0
                          ORDER BY topic_last_post_time DESC
                          LIMIT 2;';
         $result_posts = $SQLForum->executeTextStatement($select_posts);

         // get template
         $Template__PostsForum = &$this->__getTemplate('PostsForum');

         // get configuration from the registry
         $Reg = &Singleton::getInstance('Registry');
         $ForumBaseURL = $Reg->retrieve('sites::apfdocupage','ForumBaseURL');

         // create post list
         $Buffer = (string)'';
         $isFirstPost = true;

         while($data = $SQLForum->fetchData($result_posts)){

            // fill template
            $Template__PostsForum->setPlaceHolder('Link',$ForumBaseURL.'/'.$this->__Language.'/viewtopic.php?f='.$data['forum_id'].'&t='.$data['topic_id']);
            $Template__PostsForum->setPlaceHolder('LinkText',utf8_encode(substr($data['topic_title'],0,35).'...'));
            $Template__PostsForum->setPlaceHolder('Title',utf8_encode($data['topic_title']));

            // add line break if not first post
            if($isFirstPost === true){
               $isFirstPost = false;
             // end if
            }
            else{
               $Buffer .= '<br />';
             // end else
            }

            // add current post to list
            $Buffer .= $Template__PostsForum->transformTemplate();

          // end while
         }

         // add post list to content
         $this->setPlaceHolder('Posts',$Buffer);


         // get comment database connection
         $SQLComments = &$cM->getConnection('Comments');

         // get comment posts template
         $Template__PostsComments = &$this->__getTemplate('PostsComments');

         // select the last two comments
         $select_comments = 'SELECT Comment,CategoryKey
                             FROM article_comments
                             WHERE CategoryKey LIKE \''.$this->__Language.'%\'
                             ORDER BY Date DESC, Time DESC
                             LIMIT 2;';
         $result_comments = $SQLComments->executeTextStatement($select_comments);

         // fetch the page indicator from the model
         $Model = &Singleton::getInstance('APFModel');
         $PageIndicators = $Model->getAttribute('page.indicator');
         $CurrentPageIndicator = $PageIndicators[$Model->getAttribute('page.language')];

         // create comment list
         $Buffer = (string)'';
         $isFirstPost = true;

         while($data = $SQLComments->fetchData($result_comments)){

            // gather page information by the current category key
            $Lang = substr($data['CategoryKey'],0,2);
            $PageID = substr($data['CategoryKey'],3,3);

            // fill template
            $PageInfo = $this->__getPageInfo($PageID,$Lang);
            $Template__PostsComments->setPlaceHolder('Link','./?'.$CurrentPageIndicator.'='.$PageID.'-'.$PageInfo['URLName'].'#comments');
            $Template__PostsComments->setPlaceHolder('LinkText',utf8_encode(substr($data['Comment'],0,35).'...'));
            $Template__PostsComments->setPlaceHolder('Title',$PageInfo['Title']);

            // add line break if not first post
            if($isFirstPost === true){
               $isFirstPost = false;
             // end if
            }
            else{
               $Buffer .= '<br />';
             // end else
            }

            // add current post to list
            $Buffer .= $Template__PostsComments->transformTemplate();

          // end while
         }

         // add post list to content
         $this->setPlaceHolder('Comments',$Buffer);

       // end function
      }


      /**
      *  @private
      *
      *  Returns the comment link using the sitemap index.
      *
      *  @param string $PageID desired page id
      *  @param string $Language language of the desired page
      *  @return array $PageInfo the offset "URLName" contains the url name of the page, the "Title" offset presents the page's title
      *
      *  @author Christian Achatz
      *  @version
      *  Version 0.1, 05.10.2008<br />
      */
      function __getPageInfo($PageID,$Language){

         // get model
         $Model = &Singleton::getInstance('APFModel');

         // get appropriate model paramters
         $Lang = $Model->getAttribute('page.language');
         $PageIndicators = $Model->getAttribute('page.indicator');

         // get database connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQLFTSearch = &$cM->getConnection('FulltextSearch');

         // select appropriate page
         $select = 'SELECT Title,URLName FROM search_articles
                    WHERE Language = \''.$Language.'\' AND PageID = \''.$PageID.'\';';
         $result = $SQLFTSearch->executeTextStatement($select);

         // fetch data and return link
         $data = $SQLFTSearch->fetchData($result);
         return array(
                      'URLName' => $data['URLName'],
                      'Title' => $data['Title']
                     );

       // end function
      }

    // end class
   }
?>