<?php
/**
 *
 * @package    PACMEC
 * @subpackage Sites
 * @category   Site
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
namespace PACMEC\Sites
{
  class Site extends \PACMEC\System\BaseRecords
  {
    const TABLE_NAME = 'sites';
    const COLUMNS_AUTO_T  = [
    ];
  	public $id                    = 0;
    public $ip_server             = null;
    public $is_active             = false;
  	public $host                  = null;
    public $name                  = null;
    public $subject               = null;
    public $description           = null;
    public $keywords              = [];
    public $lang                  = null;
    public $locale                = null;
    public $slogan                = null;

    public $ssl                   = false;
    public $url                   = null;

    public $default_picture       = null;
    public $logo                  = null;
    public $logo_alt              = null;

    public $charset               = null;
    public $email                 = null;
    public $favicon               = null;
    public $format_currency       = null;
    public $html_type             = null;

    public $theme                 = null;
    public $theme_admin           = null;

    public $plugins               = [];
  	public $socials_links         = [];
  	# public $routes                = [];
  	#public $dictionary              = [];
  	#public $team                  = [];
    public $settings              = [];
    public $owner                 = null;

    public function __construct($opts=null)
    {
      Parent::__construct(false);
      $opts = (object) $opts;
      if(is_object($opts) && isset($opts->id)) $this->get_by_id($opts->id);
      else if(is_object($opts) && isset($opts->domain)) $this->get_by('domain', $opts->domain);
      else if(is_object($opts) && isset($opts->host)) $this->get_by('domain', $opts->host);
    }

    public static function autodetect() : Self
    {
      $t = new Self;
      $t->get_by_host_and_ip($GLOBALS['PACMEC']['host'], $GLOBALS['PACMEC']['server_ip']);
      return $t;
    }

    public function get_by_host_and_ip($host, $ip)
    {
      try {
        $sql = "Select * from `".Self::get_table()."` WHERE `host`=? AND `server_ip`=?";
  			$result = Self::link()->FetchObject($sql, [$host, $ip]);
        if($result !== false){
          $this->set_all($result);
        }
      } catch (\Exception $e) {

      }
    }

    public function set_all($obj)
    {
      $obj = (object) $obj;
      if(isset($obj->id)){
        $this->id = $obj->id;
        $this->host = $obj->host;
        $this->ip_server = $obj->server_ip;
        $this->is_active = (boolean) $obj->status;
        $this->owner = new \PACMEC\Users\User(["id" => $obj->owner]);
        // $this->owner = $obj->owner;
        $this->load_settings();
        //$this->load_team();
        $this->plugins = \array_filter(explode(',', $this->get_option('plugins_activated')));

        # $this->socials_links = serialize([ [ "label" => "GitHub", "icon" => "fa fa-github", "link" => "https://github.com/feliphegomez" ] ]);

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
        $this->check_ssl();
      }
    }

    private function check_ssl()
    {
      if ($this->ssl == true && empty($_SERVER['HTTPS']))
      {
        header('Location: https://'  . $GLOBALS['PACMEC']['host'] . $GLOBALS['PACMEC']['path_orig']);
        exit("Redireccionando...");
      }
    }

    private function load_settings()
    {
      try {
        $sql = "Select * from `{$GLOBALS['PACMEC']['DB']->getTableName('settings')}` WHERE `site` IN (?) OR `site` IS NULL ORDER BY `option_name` ASC, `site` ASC";
  			$result = Self::link()->FetchAllObject($sql, [$this->id]);
        if($result !== false){
          foreach($result as $option){
            switch($option->option_name){
              case "solvemedia":
              case "socials_links":
                $option->option_value = \json_decode($option->option_value);
              break;
              case "keywords":
                $option->option_value = \array_filter(explode(',', $option->option_value));
              break;
              default:
                $option->option_value = \pacmec_parse_value($option->option_value);
              break;
            }

            if($option->option_init == 1)
              $this->{$option->option_name} = ($option->option_value);

            $this->settings[$option->option_name] = ($option->option_value);
          }
        }
      } catch (\Exception $e) {
        echo $e->getMessage();
        return [];
      }
    }

    private function load_team()
    {
      try {
        $sql = "Select * from `{$GLOBALS['PACMEC']['DB']->getTableName('users_sites')}` WHERE `host` IN (?) ORDER BY `user` ASC";
  			$result = Self::link()->FetchAllObject($sql, [$this->host]);
        if($result !== false){
          foreach($result as $site_in_user){
            $site_in_user->user = new \PACMEC\System\User(['id'=>$site_in_user->user]);
            $site_in_user->role = new \PACMEC\System\Role(['id'=>$site_in_user->role]);
            $this->team[] = $site_in_user;
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

    public function isActive()
    {
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
};
