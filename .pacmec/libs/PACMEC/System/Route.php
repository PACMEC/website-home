<?php
/**
 *
 * @package    PACMEC
 * @category   Route
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <feliphegomez@pm.me>
 * @license    license.txt
 * @version    1.0.0
 */
namespace PACMEC\System;
class Route extends \PACMEC\System\BaseRecords
{
  const TABLE_NAME = 'routes';
  const COLUMNS_AUTO_T  = [
  ];
	public $id = -1;
	public $is_actived = 1;
	public $parent = null;
	public $permission_access = null;
	public $title = 'no_found';
	public $theme = null;
	public $comments_enabled = false;
	public $description = 'No Found';
	public $content = '';
	public $path = '/404';
	public $layout = 'pages/error';
	public $keywords = "";
	public $meta = [];
	public $rating_number = 0;
	public $rating_porcen = 0;
	public $comments = [];
	public $in_home = false;
	public $in_store = false;

	public function __construct($args=[])
	{
    Parent::__construct(false);
		$args = (object) $args;
	}

  public function get_by_site($key, $value)
  {
    try {
      $sql = "Select * from `".Self::get_table()."` WHERE `site`=? AND `{$key}`=?";
      $result = Self::link()->FetchObject($sql, [$GLOBALS['PACMEC']['site']->id, $value]);
      if($result !== false){
        $this->set_all($result);
      }
    } catch (\Exception $e) {

    }
  }

  public static function autodetect()
  {
    $redirect = isset($GLOBALS['PACMEC']['input']['redirect']) ? isset($GLOBALS['PACMEC']['input']['redirect']) : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : \siteinfo("url").__url("/"));
		$url_login = \siteinfo("url") . \__url("/%pacmec_signin%");
    $self = new Self;

    $self->keywords = \siteinfo('keywords');
    #$self->theme = \siteinfo('theme');
    $url_path = Route::decodeURIautoT($GLOBALS['PACMEC']['path']);
    $_expr = \array_filter(explode("/", $url_path));
    $_exploder = [];
    foreach($_expr as $ak=>$av) $_exploder[] = $av;
    $self->path = \__url($url_path);
    $self->in_home = isset($_exploder[0]) ? false : true;
    $self->layout = "pages/none";
    #$self->content = "[pacmec-errors][/pacmec-errors]";


    if(isset($_exploder[0])){
      $self->description = \siteinfo('description');
      $self->id = 0.002;
      $path_principal = isset($_exploder[0]) ? $_exploder[0] : null;
      $path_secondary = isset($_exploder[1]) ? $_exploder[1] : null;
      $path_id = isset($_exploder[2]) ? $_exploder[2] : null;
      $path_name = isset($_exploder[3]) ? $_exploder[3] : null;
      switch(\__url($path_principal)){
        case \__url('%pacmec_store%'):
          $self->in_store = true;
          $self->title = ('store');
          switch(\__url($path_secondary))
          {
            case \__url('%pacmec_store_single%'):
              $self->id = $path_id;
              $self->content = "";
              $self->layout = "pages/store-single";
            break;
            default:
              $self->content = \PACMEC\Util\Shortcode::tag("store-home", "", [], []);
              $self->layout = "pages/store-home";
            break;
          }
        break;
        case \__url('%pacmec_signout%'):
          $GLOBALS['PACMEC']['session']->close();
          $self->title = ('signout');
          $self->content = \PACMEC\Util\Shortcode::tag("pacmec-signout", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
          $self->permission_access = NULL;
          //$self->setAll($GLOBALS['PACMEC']['route']);
          header("Location: ".$redirect);
          echo "<meta http-equiv=\"refresh\" content=\"0; url={$redirect}\">";
        break;
        case \__url('%pacmec_signin%'):
        case \__url('%pacmec_signup%'):
        case \__url('%pacmec_forgotten_password%'):
          if(\isUser())
          {
            header("Location: ".$redirect);
            exit;
          }
          $self->layout = 'pages/sign';
          switch(($path_principal))
          {
            case ('%pacmec_signup%'):
              $self->title = 'signup';
              $self->content = \PACMEC\Util\Shortcode::tag("pacmec-form-signup", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
            case ('%pacmec_forgotten_password%'):
              $self->title = 'forgotten_password';
              $self->content = \PACMEC\Util\Shortcode::tag("pacmec-form-forgotten-password", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
            default:
              $self->title = 'signin';
              $self->content = \PACMEC\Util\Shortcode::tag("pacmec-form-signin", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
          }
        break;
        // PANEL ADMIN
        case \__url('%pacmec_adminpanel%'):
          $GLOBALS['PACMEC']['site']->theme = \siteinfo("theme_admin");
          #$self->layout = \isUser() ? 'pages-none' : 'pages/signin';
          #$self->title = \isUser() ? __at('admin_home') : __at('signin');
          #$self->content = \isUser() ? \PACMEC\Util\Shortcode::tag("pacmec-admin-home", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]) : \PACMEC\Util\Shortcode::tag("pacmec-form-signin", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
          #if(!\isUser()) header("Location: ".$redirect);
          //echo "<meta http-equiv=\"refresh\" content=\"0; url={$redirect}\">";
        break;
        case \__url('%pacmec_me_account%'):
        case \__url('%pacmec_me_orders%'):
        case \__url('%pacmec_me_wishlist%'):
        case \__url('%pacmec_me_payments%'):
          if(!\isUser()) header("Location: ".$url_login."?&redirect=".urlencode($redirect));
          $self->title = 'me_account';
          $self->layout = 'pages/account';
          switch(($path_principal)){
            case ('%pacmec_me_payments%'):
              $self->title = 'me_payments';
              $self->content = \PACMEC\Util\Shortcode::tag("me-payments", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
            case ('%pacmec_me_wishlist%'):
              $self->title = 'me_wishlist';
              $self->content = \PACMEC\Util\Shortcode::tag("me-wishlist", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
            case ('%pacmec_me_orders%'):
              $self->title = 'me_orders';
              $self->content = \PACMEC\Util\Shortcode::tag("me-orders", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
            default:
              $self->content = \PACMEC\Util\Shortcode::tag("me-profile", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
            break;
          }
        break;
        default:
          #$self->content = \PACMEC\Util\Shortcode::tag("me-profile", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
          $self->id = 0;
          $self->content = "[pacmec-errors][/pacmec-errors]";
          $self->get_by_site('path', Route::decodeURIautoT($GLOBALS['PACMEC']['path']));
        break;
      }
        /*
        memberships_slug
          exit;
          $self->id = 1;
          # $self->layout = 'pages-none';
          $self->in_store = true;
          $self->title = \__at('memberships');
          $self->content = \PACMEC\Util\Shortcode::tag("memberships-home", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);

          if(!\isUser()) header("Location: ".$url_login."?&redirect=".urlencode($redirect));
          $self->id = 1;
          $self->layout = 'me-account';
          $self->title = \__at('me_payments');
          $self->content = \PACMEC\Util\Shortcode::tag("me-payments", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
        pacmec_me_memberships
          if(!\isUser()) header("Location: ".$url_login."?&redirect=".urlencode($redirect));
          $self->id = 1;
          $self->layout = 'me-account';
          $self->title = \__at('me_memberships');
          $self->content = \PACMEC\Util\Shortcode::tag("me-memberships", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);

          if(!\isUser()) header("Location: ".$url_login."?&redirect=".urlencode($redirect));
          $self->id = 1;
          $self->layout = 'me-account';
          $self->title = \__at('me_wishlist');
          $self->content = \PACMEC\Util\Shortcode::tag("me-wishlist", "", [], ["redirect"=>\siteinfo('url').$GLOBALS['PACMEC']['path']]);
        */
    } else {
      $self->id = 0.001;
      $self->content = \siteinfo("home_content");
      $self->title = ('home');
      $self->description = \siteinfo('description');
    }
    $self->title = \__at($self->title);
		if($self->id <= 0){
			$self->layout = 'pages/error';
			$self->content = "[pacmec-errors title=\"error_404_title\" content=\"error_404_content\"][/pacmec-errors]";
		}
    return $self;
  }

	public static function encodeURIautoT(string $page_slug) : string
	{
    return str_replace(array_keys($GLOBALS['PACMEC']['permanents_links']), array_values($GLOBALS['PACMEC']['permanents_links']), $page_slug);
	}

	public static function decodeURIautoT(string $page_slug) : string
	{
    return str_replace(array_values($GLOBALS['PACMEC']['permanents_links']), array_keys($GLOBALS['PACMEC']['permanents_links']), $page_slug);
	}

	public function allLoad()
	{
		$r = [];
		if(!isset($GLOBALS['PACMEC']['DB'])){ return $r; }
		foreach($GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$this->getTable()}`", []) as $menu)
    {
			$r[] = new Self($menu);
		}
		return $r;
	}

	public function get_id($a)
	{
		return $this->get_by('id',$a);
	}

	public function get_by($column='id', $val="")
	{
		try {
			global $PACMEC;
			$this->set_all(Self::link()->FetchObject(
				"SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName(SELF::TABLE_NAME)}`
					WHERE `{$column}`=?
					AND `host` IN ('*', ?) ORDER BY `host` desc
					"
				, [
					$val,
					$PACMEC['host']
				]
			));
			return $this;
		}
		catch(\Exception $e){
			return $this;
		}
	}

	function set_all($arg=null)
	{
		global $PACMEC;
		$redirect = isset($GLOBALS['PACMEC']['input']['redirect']) ? $_SERVER['HTTP_REFERER'] : \siteinfo("url").__url("/%pacmec_meaccount%");
		$url_login = \siteinfo("url") . \__url("/%pacmec_signin%");
		if($arg !== null){
			if(\is_object($arg) || \is_array($arg)){
				$arg = (array) $arg;
				switch ($arg['permission_access']) {
					case null:
					break;
					default:
  					$check = \validate_permission($arg['permission_access']);
  					if($check == false){
  						//if(\isGuest()){ $arg['layout'] = 'pages/signin'; } else { $arg['layout'] = 'pages-error'; }
  						$arg['layout'] = 'pages/signin';
  						// $this->layout = 'pages/signin';
  						if(isUser()) $arg['content'] = "[pacmec-errors title=\"route_no_access_title\" content=\"route_no_access_content\"][/pacmec-errors]";
  						else "";
  						#else $arg['content'] = ('[pacmec-form-signin redirect="'.\siteinfo('url').$PACMEC['path'].'"][/pacmec-form-signin]');
  					}
					break;
				}
				foreach($arg as $k=>$v){
					switch ($k) {
						case 'path':
						$this->{$k} = \__url(SELF::encodeURIautoT($v));
						break;
						default:
						$this->{$k} = ($v);
						break;
					}
				}
			}

		}
		#if(is_null($this->theme)) $this->theme = $GLOBALS['PACMEC']['site']->theme;
		#if(\validate_theme($this->theme)==false) $GLOBALS['PACMEC']['site']->theme;
		//$acti = \activation_theme($this->theme);

		//$this->load_ratings();
	}

	private function load_ratings()
	{
		global $PACMEC;
		$rating = \PACMEC\System\Ratign::get_all_uri($PACMEC['path']);
		$this->rating_number = $rating->rating_number;
		$this->rating_porcen = $rating->rating_porcen;
		$this->comments = $rating->votes;
	}

  public function getMeta()
  {
    try {
      if($this->id>0){
        $result = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$this->getTable()}_meta` WHERE `route_id`=? ORDER BY `ordering` DESC", [$this->id]);
        if(is_array($result)) {
          $this->meta = [];
          foreach ($result as $meta) {
            $meta->attrs = json_decode($meta->attrs);
            $this->meta[] = $meta;
          }
        }
        return [];
      }
    }
    catch(\Exception $e){
      return [];
    }
  }
}
