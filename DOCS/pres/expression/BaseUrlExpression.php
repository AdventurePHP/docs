<?php
namespace DOCS\pres\expression;

use APF\core\pagecontroller\TemplateExpression;
use DOCS\pres\taglib\BaseUrlTag;

class BaseUrlExpression implements TemplateExpression {

   const START_TOKEN = 'getBaseUrl(';
   const END_TOKEN = ')';

   public static function applies($token) {
      return strpos($token, self::START_TOKEN) !== false && strpos($token, self::END_TOKEN) !== false;
   }

   public static function getDocument($token) {
      $startTokenPos = strpos($token, self::START_TOKEN);
      $endTokenPos = strpos($token, self::END_TOKEN, $startTokenPos + 1);

      $argument = substr($token, $startTokenPos + 11, $endTokenPos - $startTokenPos - 11);

      $object = new BaseUrlTag();
      $object->setAttribute('type', trim($argument));

      return $object;
   }

}
