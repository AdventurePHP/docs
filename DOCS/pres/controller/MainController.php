<?php
namespace DOCS\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\core\singleton\Singleton;
use DOCS\biz\APFModel;

/**
 * Implements the document controller to fill the html head information
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 09.04.2007<br />
 */
class MainController extends BaseDocumentController {

   /**
    * @public
    *
    * Generates the dynamic meta tags.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 09.04.2007<br />
    * Version 0.2, 14.04.2007 (Link um Parameter erweitert um Drucken von Anwendungen zu erlauben)<br />
    * Version 0.3, 06.05.2007 (Meta-Angaben ergänzt)<br />
    * Version 0.4, 21.05.2007 (Ausgabe der URL in den Meta-Angaben verbessert (volle Domain))<br />
    * Version 0.5, 27.05.2007 (Drucken-Link ohne JAVA-Script implementiert)<br />
    * Version 0.6, 24.03.2008 (Meta-Angaben korrigiert, Druck-Link um Sprachwahl ergänzt)<br />
    * Version 0.7, 28.03.2008 (Anpassungen wg. neuem URL-Layout)<br />
    * Version 0.8, 21.06.2008 (Replaced APPS__URL_PATH with a value from the Registry)<br />
    */
   public function transformContent() {

      /* @var $model APFModel */
      $model = Singleton::getInstance(APFModel::class);

      // current title
      $this->setPlaceHolder('Title', $model->getTitle());

      // current date
      $TZDiff = date('O');
      $this->setPlaceHolder('Date', date('Y-m-d\TH:i:s') . substr($TZDiff, 0, 3) . ':' . substr($TZDiff, 3, 2));

      // current language
      $this->setPlaceHolder('Language', $model->getAttribute('page.language'));

      // expires header
      $this->setPlaceHolder('Expires', date('D, d M Y H:i:s \G\M\T', strtotime('+4 weeks')));

      // keywords
      $this->setPlaceHolder('Keywords', $model->getAttribute('page.tags'));

      // description
      $this->setPlaceHolder('Description', $model->getAttribute('page.description'));

      // set old values
      $DefaultPageName['de'] = '001-Startseite';
      $DefaultPageName['en'] = '001-Home';

      if ($this->language == 'de') {
         $this->setPlaceHolder('ImpressumTitle', 'Impressum');
         $this->setPlaceHolder('ImpressumLink', '/Seite/015-Impressum');
         $this->setPlaceHolder('SucheTitle', 'Suche');
         $this->setPlaceHolder('SucheLink', '/Seite/044-Suche');
      } else {
         $this->setPlaceHolder('ImpressumTitle', 'Impress');
         $this->setPlaceHolder('ImpressumLink', '/Page/015-Impress');
         $this->setPlaceHolder('SucheTitle', 'Search');
         $this->setPlaceHolder('SucheLink', '/Page/044-Search');
      }

      // Set additional css class for content wrapper, in case the sidebar
      // should not be displayed. Thus, the width must be 100%
      if ($model->getDisplaySidebar() === false) {
         $this->setPlaceHolder('content-wrapper-class', ' class="noSidebar"');
      }

   }

}
