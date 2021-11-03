<?php
global $PACMEC;
// Def. Folders
if (!defined('HOME_PATH')) define('HOME_PATH', $_SERVER['DOCUMENT_ROOT']);
if (!defined('PACMEC_PATH')) define('PACMEC_PATH', HOME_PATH . "/.pacmec");

try {
  $file_settings = (is_file(PACMEC_PATH . "/.prv/{$_SERVER["HTTP_HOST"]}.php") && file_exists(PACMEC_PATH . "/.prv/{$_SERVER["HTTP_HOST"]}.php"))
    ? PACMEC_PATH . "/.prv/{$_SERVER["HTTP_HOST"]}.php"
    : PACMEC_PATH . '/.settings/default.php';   // detect file settings
  require_once $file_settings;                  // configuraciones principales del sitio

  if(strtolower(PACMEC_MODE) == 'dev'){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
  }

  require_once(PACMEC_PATH . "/functions.php"); // Functions Globals
  $PACMEC["autoload"]['classes']           = [];
  $PACMEC["autoload"]['dictionaries']           = [];

  $PACMEC['method']                     = $_SERVER["REQUEST_METHOD"];
  $PACMEC['path_orig']                  = $_SERVER["REQUEST_URI"];
  $PACMEC['path']                       = str_replace("/".\basename(__FILE__), "", $_SERVER["REQUEST_URI"]);
  $query                      = [];
  foreach (explode('&', $_SERVER["QUERY_STRING"]) as $chunk) {
      $param = explode("=", $chunk);
      if ($param && isset($param[0]) && isset($param[1])) {
        $query[urldecode($param[0])] = urldecode($param[1]);
      }
  }
  $PACMEC['input']                      = array_merge(\input_post_data_json(), $query);

  $PACMEC['settings']['scheme']         = $_SERVER['REQUEST_SCHEME'];
  $PACMEC['settings']['port']           = $_SERVER['SERVER_PORT'];
  $PACMEC['settings']['host']           = $_SERVER['SERVER_NAME'];
  $PACMEC['settings']['lang']           = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
  $PACMEC['settings']['server_address'] = $_SERVER['SERVER_ADDR'];
  $PACMEC['settings']['remote_address'] = \getIpRemote();
  $PACMEC['settings']['OS']             = \getOperatingSystem();
  $PACMEC['settings']['agent']          = \getBrowser();
  $PACMEC['settings']['session_id']     = NULL;
  $PACMEC['settings']['admin']          = $_SERVER['SERVER_ADMIN'];

  $PACMEC['origin'] = "{$PACMEC['settings']['scheme']}://{$PACMEC['settings']['host']}:{$PACMEC['settings']['port']}{$PACMEC['path']}";

  require_once(PACMEC_PATH . "/libs/PACMEC/AutoClasses.php");  // AutoLoad Classes
  \spl_autoload_register(array(new \PACMEC\AutoClasses(), 'exec'));

  $PACMEC['hooks'] = \PACMEC\System\Hooks::getInstance();
  $PACMEC['DB'] = \PACMEC\System\DB::conexion();
  $PACMEC['settings']['lang'] = \PACMEC\System\Init::get_detect_lang();
  $PACMEC['site'] = new \PACMEC\System\Site(['host' => $PACMEC['settings']['host']]);

  \PACMEC\System\Init::get_langs_http();

  $PACMEC['autoload']                   = [
    "classes"     => [],
    "dictionary"     => [],
  ];

  \session_set_save_handler(new \PACMEC\System\Session(), true);

  if (!\is_session_started()) {
    session_name(SS_NAME);
    session_start();
  }

  $PACMEC['settings']['session_id']     = \session_id();
  $PACMEC['settings']['session_name']     = \session_name();

  if(empty($GLOBALS['PACMEC']['settings']['lang'])) $GLOBALS['PACMEC']['settings']['lang'] = \siteinfo("lang");

  setlocale(LC_ALL, \siteinfo('locale')); // Establecer el localismo
  setlocale(LC_MONETARY, \siteinfo('format_currency')); // Establecer el localismo

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($PACMEC, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
  Echo "ERROR: \n".$e->getMessage();
}

/*
namespace PACMEC {
  // interfaces
  interface IPACMEC {
    public function __construct();
  }

  interface ILayout {}
  interface IRoute {}
  interface ITemplate {}
  interface IComponent {}

  // classes
  class PACMEC implements IPACMEC {
    public $active = false;
    public $addr   = "0.0.0.0";
    public $vhost  = "domain.tld";

    public function __construct(){
      $this->addr = $_SERVER["SERVER_ADDR"];
      $this->ip = $_SERVER["HTTP_HOST"];
    }
  }
  class Layout implements ILayout {}
  class Route implements IRoute {}
  class Template implements ITemplate {}
  class Component implements IComponent {}
}
*/
