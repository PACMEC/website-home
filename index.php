<?php
/**
 * @package    PACMEC
 * @category   AutoLoad
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
try {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  if (!defined('ROOT_PATH')) define('ROOT_PATH', __DIR__);
  if (!defined('PACMEC_PATH')) define('PACMEC_PATH', ROOT_PATH . "/.pacmec");
  require_once (is_file(PACMEC_PATH . "/.settings/{$_SERVER['SERVER_NAME']}.php") && file_exists(PACMEC_PATH . "/.settings/{$_SERVER['SERVER_NAME']}.php")) ? PACMEC_PATH . "/.settings/{$_SERVER['SERVER_NAME']}.php" : PACMEC_PATH . "/.settings/default.php";
  require_once PACMEC_PATH . '/core/functions.php';
  \init_pacmec_vars();
  require_once PACMEC_PATH . '/core/autoclasses.php';
  \spl_autoload_register(array(new \PACMEC\AutoClasses(), 'exec'));
  \session_set_save_handler(new \PACMEC\System\Session(), true);

  // \PACMEC\System\Init::create();
  try {
    require_once PACMEC_PATH . "/libs/solvemedia/solvemedialib.php";

    $GLOBALS['PACMEC']['host']         = $_SERVER['SERVER_NAME'];
    $GLOBALS['PACMEC']['server_ip'] = $_SERVER['SERVER_ADDR'];
    $GLOBALS['PACMEC']['path'] = \strtok($GLOBALS['PACMEC']['path_orig'], '?');
    $GLOBALS['PACMEC']['DB'] = \PACMEC\System\DB::conexion();
    \PACMEC\System\Init::run_session();
    $GLOBALS['PACMEC']['session'] = new \PACMEC\Session\Init();
    $GLOBALS['PACMEC']['site'] = \PACMEC\Sites\Site::autodetect();
    if(empty($GLOBALS['PACMEC']['lang-detect'])) $GLOBALS['PACMEC']['lang-detect'] = \siteinfo("lang");

    setlocale(LC_ALL, \siteinfo('locale')); // Establecer el localismo
    setlocale(LC_MONETARY, \siteinfo('format_currency')); // Establecer el localismo
    \PACMEC\System\Init::get_langs_http();
    $GLOBALS['PACMEC']['permanents_links'] = [
      "%pacmec_adminpanel%"  => urlencode(\__at("pacmec_adminpanel")),

      "%pacmec_signin%"  => urlencode(\__at("pacmec_signin")),
      "%pacmec_signup%"  => urlencode(\__at("pacmec_signup")),
      "%pacmec_signout%"  => urlencode(\__at("pacmec_signout")),
      "%pacmec_forgotten_password%"  => urlencode(\__at("pacmec_forgotten_password")),
      "%pacmec_me_account%"  => urlencode(\__at("pacmec_me_account")),
        "%pacmec_me_orders%"  => urlencode(\__at("pacmec_me_orders")),
        "%pacmec_me_memberships%"  => urlencode(\__at("pacmec_me_memberships")),
        "%pacmec_me_wishlist%"  => urlencode(\__at("pacmec_me_wishlist")),
        "%pacmec_me_payments%"  => urlencode(\__at("pacmec_me_payments")),
      "%pacmec_aboutus%"  => urlencode(\__at("pacmec_aboutus")),
      "%pacmec_briefcase%"  => urlencode(\__at("pacmec_briefcase")),
      "%pacmec_store%"  => urlencode(\__at("pacmec_store")),
        "%pacmec_store_single%"  => urlencode(\__at("pacmec_store_single")),
      "%pacmec_memberships%"  => urlencode(\__at("pacmec_memberships")),
      "%pacmec_memberships_single%"  => urlencode(\__at("pacmec_memberships_single")),
      "%pacmec_help%"  => urlencode(\__at("pacmec_help")),
      "%pacmec_contactus%"  => urlencode(\__at("pacmec_contactus")),
      #"%pacmec_how2buy%"  => urlencode(\__at("pacmec_how2buy")),
      "%pacmec_search%"  => urlencode(\__at("pacmec_search")),
    ];

    $GLOBALS['PACMEC']['hooks'] = \PACMEC\System\Hooks::getInstance();
    require_once PACMEC_PATH . "/core/shortcodes.php";
    if(!$GLOBALS['PACMEC']['site']->isValid()) throw new \Exception(\__at("domain_no_create"), 1);
    if(!$GLOBALS['PACMEC']['site']->isActive()) throw new \Exception(\__at("domain_no_auth"), 1);
    $GLOBALS['PACMEC']['layout'] = $GLOBALS['PACMEC']['site']->theme;

    \PACMEC\System\Init::checkedTables();
    \PACMEC\System\Init::checkedOptions();
    \PACMEC\System\Init::addAssetsPACMEC();
    \PACMEC\System\Init::initTemplates();
    \PACMEC\System\Init::initGateways();
    \PACMEC\System\Init::initPlugins();
    \PACMEC\System\Init::initRoute();
    \add_action('meta_head', 'pacmec_meta_head');
    \PACMEC\System\Init::initMetas();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($GLOBALS['PACMEC'], JSON_PRETTY_PRINT);
    // \PACMEC\System\Init::create();
    //$pacmec = new \PACMEC\System\Init();
  } catch (\Exception $e) {
    echo str_replace("%s", $GLOBALS['PACMEC']['host'], $e->getMessage());
    exit;
  }
  exit;
} catch (\Exception $e) {
 echo \get_error_html($e->getMessage(), 'PACMEC-ERROR');
 exit;
}
