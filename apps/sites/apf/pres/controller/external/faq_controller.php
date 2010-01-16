<?php
   import('core::database','connectionManager');

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
   class faq_controller extends baseController {

      function faq_controller() {
      }

      /**
       *  @public
       *
       *  Displays the FAQ list on the FAQ content page.
       *
       *  @author Christian Achatz
       *  @version
       *  Version 0.1, 10.01.2009<br />
       */
      function transformContent() {

         // get forum database connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQLForum = &$cM->getConnection('Forum');

         // get configuration from the registry
         $reg = &Singleton::getInstance('Registry');
         $forumBaseURL = $reg->retrieve('sites::apf','ForumBaseURL');

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
         $templatePostsForum = &$this->__getTemplate('PostsForum');
         $templateAuthorLabel = &$this->__getTemplate('Author_'.$this->__Language);
         $templateCreationDateLabel = &$this->__getTemplate('CreationDate_'.$this->__Language);
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