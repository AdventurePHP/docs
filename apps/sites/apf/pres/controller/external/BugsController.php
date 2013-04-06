<?php
namespace APF\sites\apf\pres\controller\external;

use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;

/**
 * @package APF\sites\apf\pres\controller\external
 * @class BugsController
 *
 * Implements the document controller for the bugs list.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.01.2009<br />
 */
class BugsController extends BaseDocumentController {

   public function transformContent() {

      // get forum database connection
      /* @var $cM ConnectionManager */
      $cM = & $this->getServiceObject('APF\core\database\ConnectionManager');
      $forumConn = & $cM->getConnection('Forum');

      // get configuration from the registry
      $forumBaseURL = Registry::retrieve('APF\sites\apf', 'ForumBaseURL');

      $select = 'SELECT
                       `topic_id`,
                       `topic_title`,
                       `topic_time`,
                       `topic_first_poster_name`,
                       `topic_last_post_time`
                    FROM `de_phpbb3_topics`
                    WHERE `forum_id` = \'8\'
                    ORDER BY topic_last_post_time DESC
                    LIMIT 10;';
      $result = $forumConn->executeTextStatement($select);

      // get template and pre-fill it
      $postsForum = & $this->getTemplate('PostsForum');
      $authorLabel = & $this->getTemplate('Author_' . $this->language);
      $creationDateLabel = & $this->getTemplate('CreationDate_' . $this->language);
      $postsForum->setPlaceHolder('AuthorLabel', $authorLabel->transformTemplate());
      $postsForum->setPlaceHolder('CreationDateLabel', $creationDateLabel->transformTemplate());

      // create post list
      $buffer = (string)'';

      while ($data = $forumConn->fetchData($result)) {

         // fill template
         $postsForum->setPlaceHolder('Link', $forumBaseURL . '/' . $this->language . '/viewtopic.php?f=8&t=' . $data['topic_id']);
         $postsForum->setPlaceHolder('LinkText', utf8_encode($data['topic_title']));
         $postsForum->setPlaceHolder('Title', utf8_encode($data['topic_title']));

         $postsForum->setPlaceHolder('CreationDate', utf8_encode(date('Y-m-d, H:i:s', $data['topic_time'])));
         $postsForum->setPlaceHolder('Author', utf8_encode($data['topic_first_poster_name']));

         // add current post to list
         $buffer .= $postsForum->transformTemplate();

      }

      // add post list to content
      $this->setPlaceHolder('Entries', $buffer);

   }

}
