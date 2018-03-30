<?php
namespace DOCS\pres\controller\external;

use APF\core\database\ConnectionManager;
use APF\core\pagecontroller\BaseDocumentController;
use APF\core\registry\Registry;

/**
 * Implements the document controller for the FAQ list.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 10.01.2009<br />
 */
class FaqController extends BaseDocumentController {

   public function transformContent() {

      // get forum database connection
      /* @var $cM ConnectionManager */
      $cM = $this->getServiceObject(ConnectionManager::class);
      $conn = $cM->getConnection('Forum');

      // get configuration from the registry
      $forumBaseURL = Registry::retrieve('DOCS', 'ForumBaseURL');

      $select = 'SELECT
                          `topic_id`,
                          `topic_title`,
                          `topic_time`,
                          `topic_first_poster_name`,
                          `topic_last_post_time`
                       FROM `de_phpbb3_topics`
                       WHERE `forum_id` = \'6\'
                       ORDER BY topic_last_post_time DESC;';
      $result = $conn->executeTextStatement($select);

      // get template and pre-fill it
      $template = $this->getTemplate('PostsForum');
      $templateAuthorLabel = $this->getTemplate('Author_' . $this->language);
      $templateCreationDateLabel = $this->getTemplate('CreationDate_' . $this->language);
      $template->setPlaceHolder('AuthorLabel', $templateAuthorLabel->transformTemplate());
      $template->setPlaceHolder('CreationDateLabel', $templateCreationDateLabel->transformTemplate());

      // create post list
      while ($data = $conn->fetchData($result)) {

         // fill template
         $template->setPlaceHolder('Link', $forumBaseURL . '/viewtopic.php?f=6&t=' . $data['topic_id']);
         $template->setPlaceHolder('LinkText', utf8_encode($data['topic_title']));
         $template->setPlaceHolder('Title', utf8_encode($data['topic_title']));

         $template->setPlaceHolder('CreationDate', utf8_encode(date('Y-m-d, H:i:s', $data['topic_time'])));
         $template->setPlaceHolder('Author', utf8_encode($data['topic_first_poster_name']));

         // add current post to list
         $this->setPlaceHolder('Entries', $template->transformTemplate(), true);

      }

   }

}
