<?php
   /**
    * @package sites::apf::pres::controller::external
    * @class bugs_controller
    *
    * Implements the document controller for the bugs list.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.01.2009<br />
    */
   class bugs_controller extends base_controller {

      public function transformContent(){

         // get forum database connection
         $cM = &$this->getServiceObject('core::database','ConnectionManager');
         $forumConn = &$cM->getConnection('Forum');

         // get configuration from the registry
         $forumBaseURL = Registry::retrieve('sites::apf','ForumBaseURL');

         // build select
         if($this->__Language === 'de'){
            $forumID = '8';
          // end if
         }
         else{
            $forumID = '9';
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
                    ORDER BY topic_last_post_time DESC
                    LIMIT 10;';
         $result = $forumConn->executeTextStatement($select);

         // get template and prefill it
         $postsForum = &$this->getTemplate('PostsForum');
         $authorLabel = &$this->getTemplate('Author_'.$this->__Language);
         $creationDateLabel = &$this->getTemplate('CreationDate_'.$this->__Language);
         $postsForum->setPlaceHolder('AuthorLabel',$authorLabel->transformTemplate());
         $postsForum->setPlaceHolder('CreationDateLabel',$creationDateLabel->transformTemplate());

         // create post list
         $buffer = (string)'';
         $isFirstPost = true;

         while($data = $forumConn->fetchData($result)){

            // fill template
            $postsForum->setPlaceHolder('Link',$forumBaseURL.'/'.$this->__Language.'/viewtopic.php?f='.$forumID.'&t='.$data['topic_id']);
            $postsForum->setPlaceHolder('LinkText',utf8_encode($data['topic_title']));
            $postsForum->setPlaceHolder('Title',utf8_encode($data['topic_title']));

            $postsForum->setPlaceHolder('CreationDate',utf8_encode(date('Y-m-d, H:i:s',$data['topic_time'])));
            $postsForum->setPlaceHolder('Author',utf8_encode($data['topic_first_poster_name']));

            // add current post to list
            $buffer .= $postsForum->transformTemplate();

          // end while
         }

         // add post list to content
         $this->setPlaceHolder('Entries',$buffer);

       // end function
      }

    // end class
   }
?>