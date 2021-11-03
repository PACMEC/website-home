<?php
/**
 *
 * @package    PACMEC
 * @category   PIM
 * @copyright  2020-2021 FelipheGomez & FelipheGomez CO
 * @author     FelipheGomez <feliphegomez@gmail.com>
 * @license    license.txt
 * @version    0.0.1
 */
namespace PACMEC\System;
#use PHPExcel\PHPExcel_IOFactory;
class Site extends \PACMEC\System\BaseRecords
{
  const TABLE_NAME = 'sites';
  const COLUMNS_AUTO_T  = [
  ];
	public $id                    = 0;
	public $host                  = null;
	public $name                  = null;
	public $description           = null;
  public $url               = null;
	public $homeurl               = null;
	public $ip_server             = null;
	public $is_active             = false;
	public $ssl                   = false;
	public $logo                  = false;
	public $logo_alt              = false;
	public $favicon               = null;
	public $lang                  = null;
  public $theme                 = null;
  public $keywords              = [];
  public $owner                 = null;
	public $plugins               = [];
	public $socials_links          = [];
	# public $routes                = [];
	#public $dictionary              = [];
	public $team                  = [];
  public $settings              = [];

  public function __construct($opts=null)
  {
    Parent::__construct(false);
    $opts = (object) $opts;
    if(is_object($opts) && isset($opts->id)) $this->get_by_id($opts->id);
    else if(is_object($opts) && isset($opts->host)) $this->get_by('host', $opts->host);
  }

  public function set_all($obj)
  {
    $obj = (object) $obj;
    if(isset($obj->id)){
      $this->id = $obj->id;
      $this->host = $obj->host;
      $this->ip_server = $obj->server_ip;
      $this->is_active = (boolean) $obj->status == 1;
      $this->load_settings();
      $this->description = $this->get_option('descr');
      $this->ssl = $this->get_option('enable_ssl');
      $this->theme = $this->get_option('theme_default');
      $this->keywords = explode(',', $this->get_option('keywords'));
      $this->name = $this->get_option('name');
      $this->url = $this->get_option('url');
      $this->homeurl = $this->url.$this->get_option('homeurl');
      $this->lang = $this->get_option('lang_default');
      $this->logo = $this->get_option('logo');
      $this->logo_alt = $this->get_option('logo_alt');
      $this->favicon = $this->get_option('favicon');
      $this->owner = new \PACMEC\System\User(["id" => $obj->owner]);
      $this->plugins = array_filter(explode(',', $this->get_option('plugins_activated')));
      $this->socials_links = json_decode($this->get_option('socials_links'));
      $this->ssl = ($this->get_option('ssl'));
      $this->lang = ($this->get_option('lang'));
      $this->theme = ($this->get_option('theme'));
      $this->currency = ($this->get_option('currency'));

      foreach (get_called_class()::COLUMNS_AUTO_T as $key => $atts) {
        $parts = [];
        if(property_exists($this, $key)){
          foreach ($atts["parts"] as $x) {
            if (property_exists($this, $x)) $x = $this->{$x};
            elseif (isset(${$x})) $x = ${$x};
            elseif (isset($$x)) $x = $$x;
            $parts[] = $x;
          }
          $s = ($atts["autoT"] == true) ? __a(implode($atts["s"], $parts)) : implode($atts["s"], $parts);;
          $this->{$key} = $s;
        }
      }
    }
  }

  private function load_settings()
  {
    try {
      $sql = "Select * from `{$GLOBALS['PACMEC']['DB']->getTableName('settings')}` WHERE `site` IN (?) OR `site` IS NULL ORDER BY `site` ASC, `option_name` ASC";
			$result = Self::link()->FetchAllObject($sql, [$this->id]);
      if($result !== false){
        #$result
        #$PACMEC['settings'][$option->option_name] = Self::pacmec_parse_value($option->option_value);
        foreach($result as $option){
          $this->settings[$option->option_name] = \PACMEC\System\Init::pacmec_parse_value($option->option_value);
        }
      }

    } catch (\Exception $e) {
      echo $e->getMessage();
      return [];
    }
  }

  public function get_option($key)
  {
    return isset($this->settings[$key]) ? $this->settings[$key] : null;
  }

  public function isActive(){
    return $this->is_active;
  }

  public function getTotalRows() : Array
  {
    $r = [];
    foreach ($GLOBALS['PACMEC']['DB']->get_tables_info() as $tbl => $data) {
      $r[$tbl] = 0;
    }
    return $r;
  }
}
