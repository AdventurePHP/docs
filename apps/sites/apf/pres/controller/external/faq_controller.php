<?php
   /**
    *  @package sites::apf::pres::controller::external
    *  @class faq_controller
    *
    *  Implements the document controller for the FAQ list.
    *
    *  @author Christian Achatz
    *  @version
    *  Version 0.1, 10.01.2009<br />
    */
   class faq_controller extends base_controller {

      /**
       *  @public
       *
       *  Displays the FAQ list on the FAQ content page.
       *
       *  @author Christian Achatz
       *  @version
       *  Version 0.1, 10.01.2009<br />
       */
      public function transformContent() {

         // get forum database connection
         $cM = &$this->__getServiceObject('core::database','ConnectionManager');
         $SQLForum = &$cM->getConnection('Forum');

         // get configuration from the registry
         $forumBaseURL = Registry::retrieve('sites::apf','ForumBaseURL');

         // build select
         if($this->__Language === 'de') {
            $forumID = '6';
          // end if
         }
         else {
            $forumID = '8';
          // end else
         }

         $select = 'SELECT
                          `topic_id`,
                          `topic_title`,
                          `topic_time`,
                          `topic_first_poster_name`,
                          `topic_last_post_time`
                       FROM `'.$this->__Language.'_phpbb3_topics`
                       WHERE `forum_id` = \''.$forumID.'\'
                       ORDER BY topic_last_post_time DESC;';
         $result = $SQLForum->executeTextStatement($select);

         // get template and prefill it
         $templatePostsForum = &$this->getTemplate('PostsForum');
         $templateAuthorLabel = &$this->getTemplate('Author_'.$this->__Language);
         $templateCreationDateLabel = &$this->getTemplate('CreationDate_'.$this->__Language);
         $templatePostsForum->setPlaceHolder('AuthorLabel',$templateAuthorLabel->transformTemplate());
         $templatePostsForum->setPlaceHolder('CreationDateLabel',$templateCreationDateLabel->transformTemplate());

         // create post list
         $buffer = (string)'';
         $isFirstPost = true;

         while($data = $SQLForum->fetchData($result)) {

            // fill template
            $templatePostsForum->setPlaceHolder('Link',$forumBaseURL.'/'.$this->__Language.'/viewtopic.php?f='.$forumID.'&t='.$data['topic_id']);
            $templatePostsForum->setPlaceHolder('LinkText',utf8_encode($data['topic_title']));
            $templatePostsForum->setPlaceHolder('Title',utf8_encode($data['topic_title']));

            $templatePostsForum->setPlaceHolder('CreationDate',utf8_encode(date('Y-m-d, H:i:s',$data['topic_time'])));
            $templatePostsForum->setPlaceHolder('Author',utf8_encode($data['topic_first_poster_name']));

            // add current post to list
            $buffer .= $templatePostsForum->transformTemplate();

          // end while
         }

         // add post list to content
         $this->setPlaceHolder('Entries',$buffer);

       // end function
      }

    // end class
   }
?>