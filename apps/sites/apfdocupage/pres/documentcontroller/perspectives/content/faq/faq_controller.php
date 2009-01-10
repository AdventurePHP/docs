<?php
   import('core::database','connectionManager');


   /**
   *  @namespace sites::apfdocupage::pres::documentcontroller::perspectives::content::faq
   *  @class faq_controller
   *
   *  Implements the document controller for the FAQ list.
   *
   *  @author Christian Achatz
   *  @version
   *  Version 0.1, 10.01.2009<br />
   */
   class faq_controller extends baseController
   {

      function faq_controller(){
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
      function transformContent(){

         // get forum database connection
         $cM = &$this->__getServiceObject('core::database','connectionManager');
         $SQLForum = &$cM->getConnection('Forum');

         // get configuration from the registry
         $Reg = &Singleton::getInstance('Registry');
         $ForumBaseURL = $Reg->retrieve('sites::apfdocupage','ForumBaseURL');

         // build select
         if($this->__Language === 'de'){
            $forumID = '6';
          // end if
         }
         else{
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
         $Template__PostsForum = &$this->__getTemplate('PostsForum');
         $Template__AuthorLabel = &$this->__getTemplate('Author_'.$this->__Language);
         $Template__CreationDateLabel = &$this->__getTemplate('CreationDate_'.$this->__Language);
         $Template__PostsForum->setPlaceHolder('AuthorLabel',$Template__AuthorLabel->transformTemplate());
         $Template__PostsForum->setPlaceHolder('CreationDateLabel',$Template__CreationDateLabel->transformTemplate());

         // create post list
         $buffer = (string)'';
         $isFirstPost = true;

         while($data = $SQLForum->fetchData($result)){

            // fill template
            $Template__PostsForum->setPlaceHolder('Link',$ForumBaseURL.'/'.$this->__Language.'/viewtopic.php?f='.$forumID.'&t='.$data['topic_id']);
            $Template__PostsForum->setPlaceHolder('LinkText',utf8_encode($data['topic_title']));
            $Template__PostsForum->setPlaceHolder('Title',utf8_encode($data['topic_title']));

            $Template__PostsForum->setPlaceHolder('CreationDate',utf8_encode(date('Y-m-d, H:i:s',$data['topic_time'])));
            $Template__PostsForum->setPlaceHolder('Author',utf8_encode($data['topic_first_poster_name']));

            // add current post to list
            $buffer .= $Template__PostsForum->transformTemplate();

          // end while
         }

         // add post list to content
         $this->setPlaceHolder('Entries',$buffer);

       // end function
      }

    // end class
   }
?>