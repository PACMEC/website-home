<?php
/**
 *
 * @package    PACMEC
 * @category   Run
 * @copyright  2020-2021 FelipheGomez
 * @author     FelipheGomez <feliphegomez@pm.me>
 * @license    license.txt
 * @version    1.0.1
 */

namespace PACMEC\System;

class Init
{
  /**
  * Creacion de variables globales
  *
  * @author FelipheGomez <feliphegomez@gmail.com>
  */
  public static function start()
  {
    global $PACMEC;
    $PACMEC['hooks'] = \PACMEC\System\Hooks::getInstance();
    $PACMEC['DB'] = \PACMEC\System\DB::conexion();
    $PACMEC['settings']['lang'] = Self::get_detect_lang();
    $PACMEC['site'] = new \PACMEC\System\Site(['host' => $PACMEC['settings']['host']]);
    $PACMEC['glossary'] = Self::get_langs_http();

  }

  public static function get_detect_lang()
  {
    global $PACMEC;
    $result = PACMEC_LANG_DEF;
    if (isset($_COOKIE['language']) && !empty($_COOKIE['language']) && isset($PACMEC['glossaries'][$_COOKIE['language']])){
      $result = $_COOKIE['language'];
    }
    if(!isset($_COOKIE['language']) || $result !== $_COOKIE['language']) setcookie('language', $PACMEC['settings']['lang']);
    return $result;
  }

  public static function pacmec_parse_value($option_value)
  {
    switch ($option_value) {
      case 'true':
        return true;
        break;
      case 'false':
        return false;
        break;
      case 'null':
        return null;
        break;
      default:
        return $option_value;
        break;
    }
  }

  /**
   * Carga de diccionarios
   */
  public static function get_langs_http()
  {
    $glossary_active = false;
    foreach (glob(PACMEC_PATH . "/i18n/*") as $i => $file_path) {
      $file_info = \validate_file($file_path);
      if(isset($file_info['translation_for_the_language']) && isset($file_info['text_domain'])){
        $slug = $file_info['translation_for_the_language'];
        $text_domain = $file_info['text_domain'];
        $GLOBALS['PACMEC']['i18n'][$slug] = ["name" => $file_info['text_domain'], "dictionary" => []];
        $info_lang = \extract_info_lang($file_path);
        foreach ($info_lang as $key => $value) $GLOBALS['PACMEC']['i18n'][$slug]['dictionary'][$key] = $value;
        $PACMEC['autoload']['dictionary'][$file_path] = $file_info;
      }
    };
    foreach($GLOBALS['PACMEC']["i18n"] as $code => $lang) {
      foreach($GLOBALS['PACMEC']['DB']->FetchAllObject(
        "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('glossary')}` WHERE `i18n` IN (?) AND `site` IN (?) OR `i18n` IN (?) AND `site` IS NULL"
        , [$code, $GLOBALS['PACMEC']['site']->id, $code]) as $option) {
          $GLOBALS['PACMEC']['i18n'][$option->i18n]['dictionary'][$option->slug] = $option->text;
      }
    }
    if(!isset($GLOBALS["PACMEC"]['i18n'][$GLOBALS["PACMEC"]['lang-detect']])) exit("El lenguaje: $glossary_active no se encuentra en la DB.");
    $GLOBALS["PACMEC"]['glossary'] = $GLOBALS["PACMEC"]['i18n'][$GLOBALS["PACMEC"]['lang-detect']]['dictionary'];
  }
}
