<?php
namespace DEV\wizard\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\tools\form\taglib\HtmlFormTag;

class DatabaseController extends BaseDocumentController {

   public function transformContent() {

      $form = & $this->getForm('database-adaption');

      if ($form->isSent() && $form->isValid()) {

         $configNamespace = 'APF\core\database';
         $configName = 'connections.ini';
         $config = $this->getConfiguration($configNamespace, $configName);

         $host = $this->getFormValue($form, 'database-host');
         $port = $this->getFormValue($form, 'database-port');
         $user = $this->getFormValue($form, 'database-user');
         $pass = $this->getFormValue($form, 'database-pass');

         foreach ($config->getSectionNames() as $section) {
            $section = $config->getSection($section);

            $section->setValue('Host', $host);

            if (!empty($port)) {
               $section->setValue('Port', $port);
            }

            $section->setValue('User', $user);
            $section->setValue('Pass', $pass);
         }

         $config->getSection('Comments')->setValue('Name', $this->getFormValue($form, 'database-name-general'));
         $config->getSection('FulltextSearch')->setValue('Name', $this->getFormValue($form, 'database-name-search'));
         $config->getSection('Forum')->setValue('Name', $this->getFormValue($form, 'database-name-forum'));
         $config->getSection('Wiki')->setValue('Name', $this->getFormValue($form, 'database-name-wiki'));
         $config->getSection('Tracker')->setValue('Name', $this->getFormValue($form, 'database-name-tracker'));
         $config->getSection('Stat')->setValue('Name', $this->getFormValue($form, 'database-name-stat'));

         $this->saveConfiguration($configNamespace, $configName, $config);

         $this->getTemplate('success')->transformOnPlace();

      } else {
         $form->transformOnPlace();
      }
   }

   private function getFormValue(HtmlFormTag $form, $controlName) {
      return $form->getFormElementByName($controlName)->getValue();
   }

}