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
        $GLOBALS['PACMEC']['autoload']["dictionaries"][] = $file_path;
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
    if(!isset($GLOBALS["PACMEC"]['i18n'][$GLOBALS["PACMEC"]['settings']['lang']])) exit("El lenguaje: $glossary_active no se encuentra en la DB.");
    $GLOBALS["PACMEC"]['glossary'] = $GLOBALS["PACMEC"]['i18n'][$GLOBALS["PACMEC"]['settings']['lang']]['dictionary'];
  }

  /**
   * Revision de tablas
   */
  public static function checkedTables()
  {
    $sql = "SELECT * from `INFORMATION_SCHEMA`.`TABLES` where (`information_schema`.`TABLES`.`TABLE_SCHEMA` = database())";
    $database_info = $GLOBALS['PACMEC']['DB']->get_tables_info();
    $tables_ckecks = [
      // 'categories'                   => false,
      // 'comments'                     => false,
      // 'content'                      => false,
      // 'content_attributes'           => false,
      // 'content_media'                => false,
      // 'glossary'                     => false,
      // 'media'                        => false,
      // 'menus'                        => false,
      // 'menus_elements'               => false,
      // 'notifications'                => false,
      // 'permissions'                  => false,
      // 'permissions_users'            => false,
      // 'roles'                        => false,
      // 'roles_permissions'            => false,
      // 'routes'                       => false,
      'sessions'                     => false,
      'settings'                     => false,
      'shoppings_carts'              => false,
      'sites'                        => false,
      'users'                        => false,
    ];
    foreach ($database_info as $slug_gbl => $tbl) if(isset($tables_ckecks[$slug_gbl])) $tables_ckecks[$slug_gbl] = true;
    if(\in_array(false, \array_values($tables_ckecks)) == true) throw new \Exception(\json_encode(["subject"=>"Faltan tablas Sys", "tables" => $tables_ckecks], JSON_PRETTY_PRINT)."\n", 1);
  }

  /**
  * Revision de options
  */
  public static function checkedOptions()
  {
    $options_ckecks = [
      'aboutus'                      => false,
      'author'                      => false,
      'captcha_a'                      => false,
      'captcha_t'                      => false,
      'classification'                      => false,
      'contactus'                      => false,
      'currency'                      => false,
      'default_picture_lg'                      => false,
      'default_picture_md'                      => false,
      'default_picture_sm'                      => false,
      'description'                      => false,
      'designer'                      => false,
      'dictionary_insert'                      => false,
      'email'                      => false,
      'enable_ssl'                      => false,
      'favicon'                      => false,
      'footer_by'                      => false,
      'format_currency'                      => false,
      'html_type'                      => false,
      'keywords'                      => false,
      'lang'                      => false,
      'locale'                      => false,
      'logo'                      => false,
      'logo_alt'                      => false,
      'logo_hq'                      => false,
      'name'                      => false,
      'pagelimit'                      => false,
      'phone_number'                      => false,
      'plugins_activated'                      => false,
      'req_session'                      => false,
      'slogan'                      => false,
      'sloganby'                      => false,
      'socials_links'                      => false,
      'solvemedia'                      => false,
      'solvemedia_k_c'                      => false,
      'solvemedia_k_h'                      => false,
      'solvemedia_k_v'                      => false,
      'subject'                      => false,
      'theme'                      => false,
      'theme_admin'                      => false,
      'url'                      => false,
      #'licence'                      => false,
    ];
    foreach ($options_ckecks as $key => $value) { if(\siteinfo($key) !== 'NaN' && !empty($key)) $options_ckecks[$key] = true; }
    if(in_array(false, array_values($options_ckecks)) == true) throw new \Exception("Error en las opciones del sitio.".\json_encode($options_ckecks, JSON_PRETTY_PRINT)."\n", 1);
  }

  /**
   * Assets Globals
   */
  public static function addAssetsPACMEC()
  {
    \add_style_head(\siteinfo('url')   . "/.pacmec/assets/css/pacmec-top.css",               ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 1, false);
    \add_scripts_head(\siteinfo('url') . "/.pacmec/assets/js/pacmec-top.js",    ["type"=>"text/javascript", "charset"=>"UTF-8"], 1, false);

    \add_style_foot(\siteinfo('url')   . "/.pacmec/assets/css/pacmec-down.css",               ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 1, false);
    \add_scripts_foot(\siteinfo('url') . "/.pacmec/assets/js/pacmec-down.js",    ["type"=>"text/javascript", "charset"=>"UTF-8"], 1, false);
  }

  public static function initTemplates()
  {
    $path_theme = null;
    $path = PACMEC_PATH . "/themes";
    if (is_dir("{$path}/{$GLOBALS['PACMEC']['site']->theme}")) $path_theme = "{$path}/{$GLOBALS['PACMEC']['site']->theme}/{$GLOBALS['PACMEC']['site']->theme}.php";
    else if(is_file("{$path}/{$GLOBALS['PACMEC']['site']->theme}.php")) $path_theme = "{$path}/{$GLOBALS['PACMEC']['site']->theme}.php";
    if(is_file($path_theme)){
      $file_info = validate_file($path_theme);
      if(isset($file_info['theme_name'])){
        $GLOBALS['PACMEC']['themes'][$file_info['text_domain']] = $file_info;
        $GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['active'] = false;
        $GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['path'] = dirname($path_theme);
        $GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['file'] = ($path_theme);
        if(is_file($GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['file'])){
    			//require_once $path_theme;
          //\activation_plugin($file_info['text_domain']);
    		}
      }
      foreach(\glob($path."/*/*") as $file_path){
        $dirname = dirname($file_path);
        $name = str_replace(['.php'], [''], basename($file_path));
        $file_info = validate_file($file_path);
        if(isset($file_info['theme_name'])){
          $GLOBALS['PACMEC']['themes'][$file_info['text_domain']] = $file_info;
          $GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['active'] = false;
          $GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['path'] = $dirname;
          $GLOBALS['PACMEC']['themes'][$file_info['text_domain']]['file'] = ($file_path);
        }
      }
    }
    else {
      throw new \Exception("No existe el tema principal [{$GLOBALS['PACMEC']['site']->theme}]. path: {$path_theme}", 1);
      exit();
    }
  }

  public static function initGateways()
  {
    $GLOBALS['PACMEC']['gateways']['payments'] = new \PACMEC\Gateways\Init();
    $GLOBALS['PACMEC']['gateways']['shipping'] = new \PACMEC\Gateways\Init();
  }

  public static function initPlugins()
  {
    $path = PACMEC_PATH."/plugins";
    $plugins_activateds = explode(',', \siteinfo('plugins_activated'));
    foreach($plugins_activateds as $p){
      $path_plugin = null;
      if(is_dir("{$path}/{$p}")){
        $path_plugin = "{$path}/{$p}/{$p}.php";
      } else if(is_file("{$path}/{$p}.php")){
        $path_plugin = "{$path}/{$p}.php";
      }
      if(is_file($path_plugin)){
        $file_info = Self::validate_file($path_plugin);
        if(isset($file_info['plugin_name'])){
          $GLOBALS['PACMEC']['plugins'][$file_info['text_domain']] = $file_info;
          $GLOBALS['PACMEC']['plugins'][$file_info['text_domain']]['active'] = true;
          $GLOBALS['PACMEC']['plugins'][$file_info['text_domain']]['path'] = dirname($path_plugin);
          $GLOBALS['PACMEC']['plugins'][$file_info['text_domain']]['file'] = ($path_plugin);
          if(is_file($GLOBALS['PACMEC']['plugins'][$file_info['text_domain']]['file'])){
            require_once $path_plugin;
            \activation_plugin($file_info['text_domain']);
          }
        } else {
          \PACMEC\System\Alerts::addAlert([
            "type"        => "error",
            "plugin"      => "system",
            "message"     => "El plugin {$p}, no tiene el formato correcto.\n",
            "actions"  => [
              [
                "name" => "plugins-errors",
                "plugin" => $p,
                "slug" => __url("/%pacmec_adminpanel%?plugins_errors&p={$p}&tab=system"),
                "text" => "Ups error"
              ]
            ],
          ]);
        }
      } else {
        \PACMEC\System\Alerts::addAlert([
          "type"        => "error",
          "plugin"      => "system",
          "message"     => "Hay problemas para cargar un plugin {$p}\n",
          "actions"  => [
            [
              "name" => "plugins-errors",
              "plugin" => $p,
              "slug" => \__url("/%pacmec_adminpanel%?plugins_errors&p={$p}&tab=system"),
              "text" => "Ups error"
            ]
          ],
        ]);
      }
    }
  }

  public static function initRoute()
  {
    switch ($GLOBALS['PACMEC']['path']) {
      case '/pacmec-api':
      case '/pacmec-api-doc':
        throw new \Exception("No implementado para esta version.", 1);
      break;
      case '/robots.txt':
      $filerobots = dirname(PACMEC_PATH) . '/robots.txt';
      if(!is_file($filerobots) || !file_exists($filerobots)) {
        header('Content-Type: text/plain');
        echo "# PACMEC autogenerated robots.txt\n";
        echo "User-agent: *\n";
        echo "Allow: *\n";
        echo "Crawl-delay: 10\n";
        //echo "Sitemap: ".\siteinfo('url')."/pacmec-sitemap.xml\n";
        exit;
      }
      break;
      default:
        $GLOBALS['PACMEC']['route'] = \PACMEC\System\Route::autodetect();
        \do_action('route_extends_path', $GLOBALS['PACMEC']['route']);

        if(\siteinfo("req_session") == true && \isGuest() == true)
        {
          $GLOBALS['PACMEC']['route']->layout = 'pages/signin';
          $GLOBALS['PACMEC']['route']->content = ('[pacmec-form-signin redirect="'.\siteinfo('url').$GLOBALS['PACMEC']['path'].'"][/pacmec-form-signin]');
        } else {
          if ($GLOBALS['PACMEC']['route']->permission_access !== null) {
            $check = \validate_permission($GLOBALS['PACMEC']['route']->permission_access);
            if($check == false){
              if(\isUser()) {
                $GLOBALS['PACMEC']['route']->content = "[pacmec-errors title=\"route_no_access_title\" content=\"route_no_access_content\"][/pacmec-errors]";
              }
              else {
                $GLOBALS['PACMEC']['route']->layout = 'pages/signin';
                $GLOBALS['PACMEC']['route']->content = ('[pacmec-form-signin redirect="'.\siteinfo('url').$GLOBALS['PACMEC']['path'].'"][/pacmec-form-signin]');
              }
            }
          }
        }
      break;
    }
  }

  public static function initMetas()
  {
    $model_route = $GLOBALS['PACMEC']['route'];

    \pacmec_add_meta_tag('generator', 'PACMEC');

    if($model_route->isValid()){
      \pacmec_add_meta_tag('keywords', (implode(',', array_filter(array_merge(explode(',', $GLOBALS['PACMEC']['route']->keywords), $GLOBALS['PACMEC']['site']->keywords)) )));

    } else {
      \pacmec_add_meta_tag('keywords', \siteinfo('keywords'));

    }
    \pacmec_add_meta_tag('title', \pageinfo('title'));
    \pacmec_add_meta_tag('description', trim(
      str_replace(
        ["          ","         ","        ","       ","      ","     ","    ","   ","  "]
        , " "
        , str_replace(["\r", "\t", "\n"], " ", \pageinfo('description'))
      )
    ));

    \pacmec_add_meta_tag('subject', \siteinfo('subject'));
    \pacmec_add_meta_tag('copyright', \DevBy());
    \pacmec_add_meta_tag('language', $GLOBALS['PACMEC']['settings']['lang']);
    if(\pacmec_exist_meta('robots')==false && \siteinfo('robots')!=="NaN") { \pacmec_add_meta_tag('robots', \siteinfo('robots')); } else { \pacmec_add_meta_tag('robots', 'index,follow'); };
    \pacmec_add_meta_tag('Classification', (\pacmec_exist_meta('Classification') == false && \siteinfo('classification')!=="NaN") ? \siteinfo('classification') : 'Internet');
    if(\pacmec_exist_meta('author') == false) \pacmec_add_meta_tag('author', (\siteinfo('author')!=="NaN") ? \siteinfo('author') : \base64_decode("RmVsaXBoZUdvbWV6LCBmZWxpcGhlZ29tZXpAcG0ubWU="));
    if(\pacmec_exist_meta('designer') == false) \pacmec_add_meta_tag('designer', (\siteinfo('designer')!=="NaN") ? \siteinfo('designer') : \base64_decode("RmVsaXBoZUdvbWV6LCBmZWxpcGhlZ29tZXpAcG0ubWU="));
    if(\pacmec_exist_meta('reply-to') == false) \pacmec_add_meta_tag('reply-to', (\siteinfo('email')!=="NaN") ? \siteinfo('email') : \base64_decode("ZmVsaXBoZWdvbWV6QHBtLm1l"));
    if(\pacmec_exist_meta('owner') == false) \pacmec_add_meta_tag('owner', (\siteinfo('owner')!=="NaN") ? \siteinfo('owner') : "{$GLOBALS['PACMEC']['site']->owner->names} {$GLOBALS['PACMEC']['site']->owner->surname}, {$GLOBALS['PACMEC']['site']->owner->email}");
    \pacmec_add_meta_tag('url', \siteinfo('url').$GLOBALS['PACMEC']['path']);
    \pacmec_add_meta_tag('identifier-URL', \siteinfo('url').$GLOBALS['PACMEC']['path']);
    \pacmec_add_meta_tag('pagename', \pageinfo('title'));
    \pacmec_add_meta_tag('site_name', \siteinfo('name'));
    \pacmec_add_meta_tag('fb:app_id', \siteinfo('fb:app_id'));

    \pacmec_add_meta_tag('favicon', \siteinfo('favicon'));
    if(\pacmec_exist_meta('canonical')==false) \pacmec_add_meta_tag('canonical', \siteinfo('url').$GLOBALS['PACMEC']['path']);
    if(\pacmec_exist_meta('og:image')==false) \pacmec_add_meta_tag('image', \siteinfo('url').\siteinfo('logo_hq'));
    /*
    <meta name='revised' content='Sunday, July 18th, 2010, 5:15 pm'>
    <meta name='abstract' content=''>
    <meta name='topic' content=''>
    <meta name='summary' content=''>

    <meta name='directory' content='submission'>
    <meta name='category' content=''>
    <meta name='coverage' content='Worldwide'>
    <meta name='distribution' content='Global'>
    <meta name='rating' content='General'>
    <meta name='revisit-after' content='7 days'>
    <meta name='subtitle' content='This is my subtitle'>
    <meta name='target' content='all'>
    <meta name='HandheldFriendly' content='True'>
    <meta name='MobileOptimized' content='320'>
    <meta name='date' content='Sep. 27, 2010'>
    <meta name='search_date' content='2010-09-27'>
    <meta name='DC.title' content='Unstoppable Robot Ninja'>
    <meta name='ResourceLoaderDynamicStyles' content=''>
    <meta name='medium' content='blog'>
    <meta name='syndication-source' content='https://mashable.com/2008/12/24/free-brand-monitoring-tools/'>
    <meta name='original-source' content='https://mashable.com/2008/12/24/free-brand-monitoring-tools/'>
    <meta name='verify-v1' content='dV1r/ZJJdDEI++fKJ6iDEl6o+TMNtSu0kv18ONeqM0I='>
    <meta name='y_key' content='1e39c508e0d87750'>
    <meta name='pageKey' content='guest-home'>
    <meta itemprop='name' content='jQTouch'>
    <meta http-equiv='Expires' content='0'>
    <meta http-equiv='Pragma' content='no-cache'>
    <meta http-equiv='Cache-Control' content='no-cache'>
    <meta http-equiv='imagetoolbar' content='no'>
    <meta http-equiv='x-dns-prefetch-control' content='off'>
     */

    \pacmec_add_meta_tag('og:email', strip_tags(\siteinfo('email')));
    \pacmec_add_meta_tag('og:phone_number', strip_tags(\siteinfo('phone_number')));
    if(\pacmec_exist_meta('og:type')==false) \pacmec_add_meta_tag('og:type', 'Website');
    \do_action("metatags");
  }

}
