<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Def. Folders
if (!defined('HOME_PATH')) define('HOME_PATH', __DIR__ );
if (!defined('PACMEC_PATH')) define('PACMEC_PATH', HOME_PATH . "/.pacmec" );

// Define PACMEC VAR
global $PACMEC;
$PACMEC['settings']['domain']         = $_SERVER['SERVER_NAME'];
$PACMEC['settings']['lang']           = (!isset($_COOKIE["pacmec-lang"])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : $_COOKIE["pacmec-lang"];
$PACMEC['settings']['server_address'] = $_SERVER['SERVER_ADDR'];
$PACMEC['settings']['remote_address'] = (!empty($_SERVER['HTTP_CLIENT_IP'])) ? $_SERVER['HTTP_CLIENT_IP'] : ((!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
$PACMEC['hooks']                      = null;
$PACMEC['DB']                         = null;
$PACMEC['lang']                       = null;
$PACMEC['path_orig']                  = null;
$PACMEC['path']                       = null;
$PACMEC['route']                      = null;
$PACMEC['site']                       = null;
$PACMEC['fullData']                   = [];
$PACMEC['session']                    = null;
$PACMEC['alerts']                     = [];
$PACMEC['dictionary']                 = [];
$PACMEC['glossary']                   = null;
$PACMEC['website']                    = [
  "meta" => [],
  "scripts" => ["head"=>[],"foot"=>[],"list"=>[]],
  "styles" => ["head"=>[],"foot"=>[],"list"=>[]]
];
$PACMEC['total_records']              = [];
$PACMEC['themes']                     = [];
$PACMEC['gateways']                   = [
  'payments'=>[]
];
$PACMEC['autoload']                   = [
  "classes"     => [],
  "dictionary"     => [],
];

// detect file settings
$file_settings = (is_file(PACMEC_PATH . "/.prv/{$_SERVER["HTTP_HOST"]}.php") && file_exists(PACMEC_PATH . "/.prv/{$_SERVER["HTTP_HOST"]}.php")) ? PACMEC_PATH . "/.prv/{$_SERVER["HTTP_HOST"]}.php" : PACMEC_PATH . '/autosettings.php';

// interfaces
interface IPACMEC {
  public function __construct();
};
interface ILayout {};
interface IRoute {};
interface ITemplate {};
interface IComponent {};

// classes
class PACMEC implements IPACMEC {
  public $active = false;
  public $addr   = "0.0.0.0";
  public $vhost  = "domain.tld";

  public function __construct(){
    $this->addr = $_SERVER["SERVER_ADDR"];
    $this->ip = $_SERVER["HTTP_HOST"];
  }
};
class Layout implements ILayout {};
class Route implements IRoute {};
class Template implements ITemplate {};
class Component implements IComponent {};

// Runing
//
try {
  global $PACMEC;
  require_once $file_settings; // configuraciones principales del sitio
  #require_once PACMEC_PATH . '/includes.php';
  #$pacmec = \PACMEC\System\Run::exec();
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($PACMEC, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
  echo "Error: \n";
  echo $e->getMessage();
  exit;
}
