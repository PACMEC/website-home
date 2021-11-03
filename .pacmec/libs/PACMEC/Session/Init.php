<?php
/**
 * @package    PACMEC
 * @subpackage Session
 * @category   Init
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

namespace PACMEC\Session
{
  class Init
  {
   	public  $isGuest            = true;
   	public  $user               = null;
   	public  $rol                = null;
   	public  $permissions        = [];
   	public  $permissions_items  = [];
   	public  $notifications      = [];
   	public  $shopping_cart      = [];
    public  $subtotal_cart      = 0;
    public  $remote_ip          = "";

   	/**
   	* Inicializa la sesión
   	*/
   	public function __construct()
   	{
      $this->user             = new \stdClass();
      $this->permission_group = new \stdClass();
      $this->user = (Object) [];
      $this->permissions_items  = [];
      $this->permissions        = [];
      $this->notifications      = [];
      $this->shopping_cart      = [];
      $this->subtotal_cart      = 0;
      $this->remote_ip = \getIpRemote();
      $this->isGuest = !\isUser();

      if(\isUser()) $this->refreshSession();
   	}

    public function refreshSession()
    {
      try {
        $this->getById(\userID());
        foreach ($GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` WHERE `session_id` IN (?)", [session_id()]) as $pedid) {
          switch ($pedid->type) {
            case 'product':
            $data = new \PACMEC\System\Product((object) ['id'=>$pedid->ref_id]);
            $this->subtotal_cart += ($data->price*$pedid->quantity);
            break;
            case 'service':
            $data = new \PACMEC\System\Service((object) ['id'=>$pedid->ref_id]);
            $this->subtotal_cart += ($data->price*$pedid->quantity);
            break;
            default:
            $data = null;
            break;
          }
          $pedid->data = $data;
          $this->shopping_cart["{$pedid->type}:{$pedid->ref_id}"] = $pedid;
        }
      }
      catch(Exception $e){
        echo $e->getMessage();
      }
    }

    /**
    * Retorna string default
    * @return string
    */
    public function __toString()
    {
      return $this->get_fullname();
      // COLOCAL LABEL O GUEST
   		return json_encode($this->getAll());
    }

    public function get_fullname()
    {
      return (isset($this->user->names) && isset($this->user->surname))
      ? "{$this->user->names} {$this->user->surname}"
      : \__at("guest_user");
    }

   	public function add_alert(string $message, string $title=null, string $url=null, int $time=null, string $uniqid=null, string $icon=null)
   	{
   		$time = $time==null ? time() : $time;
   		$uniqid = $uniqid==null ? uniqid() : $uniqid;
   		$icon = $icon==null ? "fas fa-bell" : $icon;
   		$url = $url==null ? "#" : $url;
   		$title = $title==null ? "Nueva notificacion" : $title;
   		$date = date('Y-m-d H:i:s', $time);
   		$alert = [
   			"title"=>$title,
   			"message"=>$message,
   			"time"=>$time,
   			"uniqid"=>$uniqid,
   			"date"=>$date,
   			"url"=>$url,
   			"icon"=>$icon,
   		];
   		if(!isset($this->notifications[$uniqid])){
   			$this->set($uniqid, $alert, 'notifications');
   			// $this->notifications[$uniqid] = $_SESSION['notifications'][$uniqid] = $alert;
   		};
   	}

   	public function add_permission(string $tag, $obj=null):bool
   	{
   		$tag = strtolower($tag);
   		if($obj !== null){
   			$obj = (object) $obj;
   		} else {
   			$obj = (object) [
   				"id"=>999999999999999999999999,
   				"tag"=>$tag,
   				"name"=>$tag,
   				"description"=>$tag,
   			];
   		}
   		if(!isset($this->permissions_items[$tag])){
   			$this->permissions_items[$tag] = $_SESSION['permissions_items'][$tag] = $obj;
   			$this->permissions[] = $tag;
   		}
   		if(
        isset($_SESSION['permissions'])
        && !in_array($tag, $_SESSION['permissions'])
      ) $this->permissions[] = $_SESSION['permissions'][] = $tag;
   		return true;
   	}

   	public function set($k, $v, $l=null)
   	{
   		if($l == null){
   			$this->{$k} = $_SESSION[$k] = $v;
   		} else {
   			if(is_array($this->{$l})){
   				$this->{$l}[$k] = $_SESSION[$l][$k] = $v;
   			} else {
   				$this->{$l}->{$k} = $_SESSION[$l][$k] = $v;
   			}
   		}
   	}

    public function getById($user_id=null)
    {
      $user_id = $user_id!==null ? $user_id : \userID();
      //$tbl = $GLOBALS['PACMEC']['DB']->getTableName('users');
      //$dataUser = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$tbl}` WHERE `id`=? ", [ $user_id ]);
      $this->setAll(new \PACMEC\Users\User((object) ['user_id'=>$user_id]));
      return $this;
    }

   	public function setAll($user)
   	{
      foreach($user as $a => $b){ $this->user->{$a} = $b; }
      # $this->rol = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('roles')}` WHERE `name` IN (?)", [$this->user->rol]);
      /*
      $permissions = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT P.* FROM `{$GLOBALS['PACMEC']['DB']->getTableName('roles_permissions')}` RP
      INNER JOIN `{$GLOBALS['PACMEC']['DB']->getTableName('permissions')}` P
      ON P.`tag` = RP.`permission`
      WHERE `rol` IN (?)", [$this->user->rol]);

      if($permissions !== false && count($permissions) > 0){
        foreach($permissions as $perm){
          ##$siteObj->permissions[] = $perm;
          $this->add_permission($perm->tag, $perm);
        }
      }
      */

      // Cargar Sitios del usuario
      /*
      $sites = [];
      if($users_sites !== false){
        foreach ($users_sites as $ai => $site) {
          $site->permissions = [];
          $permissions = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT E.*
            FROM `{$GLOBALS['PACMEC']['DB']->getTableName('roles')}` R
            JOIN `{$GLOBALS['PACMEC']['DB']->getTableName('roles_permissions')}` RP
            ON RP.`rol` = R.`rol`
            WHERE R.`name` IN (?)", [$site->rol]);
          if($permissions !== false && count($permissions) > 0){
            foreach($permissions as $perm){
              $siteObj->permissions[] = $perm;
              // $this->add_permission($perm->tag, $perm);
            }
          }
          $siteObj = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('sites')}` WHERE `id` IN (?)", [$site->id]);
          if($siteObj !== false){
            $siteObj->hash = base64_encode($site->id);
            $siteObj->role = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('roles')}` WHERE `id` IN (?)", [$site->rol]);
          }
          $sites[] = $siteObj;
        }
      }
      $this->sites = $sites;
      */
      $notifications = \PACMEC\System\Notifications::get_all_by_user_id(\userID(), false);
      foreach ($notifications as $item) {
        # $this->notifications[] = $this->add_alert($item->message, $item->title, $item->host, strtotime($item->created), $item->id);
        $this->notifications[] = $item;
      }

      #$this->emails_boxes = \PACMEC\System\eMailsBoxes::load_users_by('user_id', \userID());

      foreach ($this as $k => $v) {
        $_SESSION[$k] = is_object($v) ? (Array) $v : $v;
      }
      /*
      if(isset($this->user->permissions) && $this->user->permissions !== null && $this->user->permissions > 0 && count($this->permissions)==0){
        $result = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT E.*
          FROM `{$GLOBALS['PACMEC']['DB']->getTableName('permissions')}` D
          JOIN `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_items')}` E
          ON E.`id` = D.`permission`
          WHERE D.`group` IN (?)", [$this->user->permissions]);
        if($result !== false && count($result) > 0){
          foreach($result as $perm){
            $this->add_permission($perm->tag, $perm);
          }
        }
        $result = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_group')}` WHERE `id` IN (?)", [$this->user->permissions]);
        if($result !== false){
          $this->permission_group = $result;
        }

      }
      /*
      // Cargar permisos del usuario (Individuales)
      $_sql = "SELECT E.*
   			FROM `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_users')}` D
   			JOIN `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_items')}` E
   			ON E.`id` = D.`permission`
   			WHERE D.`user_id` IN (?) AND D.`host` IN ('*', ?)";
      $result = $GLOBALS['PACMEC']['DB']->FetchAllObject($_sql, [$this->user->id, $PACMEC['host']]);
   		if($result !== false && count($result) > 0){
   			foreach($result as $perm){
   				$this->add_permission($perm->tag, $perm);
   			}
   		}
      */


   	}

   	/**
   	* Retorna todos los valores del array de sesión
   	* @return el array de sesión completo
   	*/
   	public function getAll()
   	{
   		#$this->refreshSession();
   		return isset($_SESSION['user']) ? $this : [];
   	}

   	/**
   	* Cierra la sesión eliminando los valores
   	*/
   	public static function close()
   	{
   		\session_unset();
   		\session_destroy();
   	}

   	/**
   	* Retorna el estatus de la sesión
   	* @return string el estatus de la sesión
   	*/
   	public static function getStatus()
   	{
   		switch(\session_status())
   		{
   			case 0:
   				return "DISABLED";
   				break;
   			case 1:
   				return "NONE";
   				break;
   			case 2:
   				return "ACTIVE";
   				break;
   		}
   	}

   	/**
   	* Retorna array default
   	* @return string
   	*/
   	public function __sleep()
   	{
   		return array_keys($this->getAll());
   	}

   	public function login($args = [])
   	{
   		$args = (object) $args;
   		if(isset($args->nick) && isset($args->hash)){
   			$result = $this->validateUserDB($args->nick);
   			switch($result){
   				case "error":
   				case "no_exist":
   				case "inactive":
   					return $result;
   					break;
   				case $result->id > 0:
   					if (\password_verify($args->hash, $result->hash) == true) {
   						if (!\headers_sent()) {
   			          \session_regenerate_id(true);
   			      }
   						$this->setAll($result);
   						return "success";
   					} else {
   						return "invalid_credentials";
   					}
   					break;
   				default:
   					return "error";
   					break;
   			}
   		}
   	}

   	public function validateUserDB($nick_or_email='')
   	{
   		try {
   			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `username`=? AND `status` IN (1) ";
   			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `username`=? ";
   			$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$nick_or_email]);
   			if($result == false){
   				$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `email`=? AND `status` IN (1) ";
   				$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `email`=? ";
   				$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$nick_or_email]);
   			}
   			if($result !== false && isset($result->id)){
   				if($result->status == 0){
   					return "inactive";
   				}
   				return $result;
   			}
   			return "no_exist";
   		}
   		catch(Exception $e){
   			#echo $e->getMessage();
   			return "error";
   		}
   	}

   	public function validateUserDB_recover($key,$email)
   	{
   		try {
   			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `keyrecov` IN (?) AND `email` IN (?) ";
   			$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$key,$email]);
   			if($result !== false && isset($result->id)){
   				if($result->status == 0){
   					return "inactive";
   				}
   				return $result;
   			}
   			return "no_exist";
   		}
   		catch(Exception $e){
   			#echo $e->getMessage();
   			return "error";
   		}
   	}

   	public function save()
   	{
   		try {
   			$user_id = Self::getUserId();
        /*
        * https://www.php.net/manual/es/filter.filters.sanitize.php
          "username" => '([^A-Za-z0-9])',
          "email" => '([^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$])',
          "names" => '([^[\\p{L}\\. \'-]+$])',
          "surname" => '([^[\\p{L}\\. \'-]+$])',
          "identification_type" => '([^0-9])',
          "identification_number" => '([^0-9])',
          "phone" => '([^0-9])',
          "mobile" => '([^0-9])',
        */
        $clmns = [
          "names",
          "surname",
          "identification_type",
          "identification_number",
          "phone",
          "mobile",
        ];
        $save_data = [];
        $ib = (array) $this->user;
        foreach ($clmns as $i => $key) {
          if(in_array($key, array_keys($ib))){
            switch ($key) {
              case 'phone':
              case 'mobile':
                $save_data[$key] = str_replace([' ', '-', '.', '(', ')'], [''], $this->user->{$key});
                break;
              case 'identification_number':
                $save_data[$key] = str_replace([' ', '.', '(', ')'], [''], $this->user->{$key});
                break;
              default:
                $save_data[$key] = $this->user->{$key};
                break;
            }
          }
        }
        $labels = "`";
        $labels .= implode("`=?,`", array_keys($save_data));
        $labels .= "`=?";
        $result = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE  `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` SET {$labels} WHERE `id`={$user_id}", array_values($save_data));
   			if($result==true) {
   				foreach ($save_data as $key => $value) {
   					$_SESSION['user'][$key] = $value;
   				}
   			};
   			return $result;
   		}
   		catch(Exception $e){
   			#echo $e->getMessage();
   			return false;
   		}
   	}

   	public function register()
   	{
   		try {


        /*
        $clmns = [
          "names",
          "surname",
          "identification_type",
          "identification_number",
          "phone",
          "mobile",
        ];
        $save_data = [];
        $ib = (array) $this->user;
        foreach ($clmns as $i => $key) {
          if(in_array($key, array_keys($ib))){
            switch ($key) {
              case 'phone':
              case 'mobile':
                $save_data[$key] = str_replace([' ', '-', '.', '(', ')'], [''], $this->user->{$key});
                break;
              case 'identification_number':
                $save_data[$key] = str_replace([' ', '.', '(', ')'], [''], $this->user->{$key});
                break;
              default:
                $save_data[$key] = $this->user->{$key};
                break;
            }
          }
        }
        $labels = "`";
        $labels .= implode("`=?,`", array_keys($save_data));
        $labels .= "`=?";
        $result = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE  `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` SET {$labels} WHERE `id`={$user_id}", array_values($save_data));
   			if($result==true) {
   				foreach ($save_data as $key => $value) {
   					$_SESSION['user'][$key] = $value;
   				}
   			};
   			return $result;
        */
   		}
   		catch(Exception $e){
   			#echo $e->getMessage();
   			return false;
   		}
   	}

    public function save_info_access()
    {
      try {
        $user_id = Self::getUserId();
        $clmns = [
          "username",
          "email",
        ];
        $save_data = [];
        $ib = (array) $this->user;
        foreach ($clmns as $i => $key) { if(in_array($key, array_keys($ib))){ $save_data[$key] = $this->user->{$key}; } }
        $labels = "`";
        $labels .= implode("`=?,`", array_keys($save_data));
        $labels .= "`=?";
        $result = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` SET {$labels} WHERE `id`={$user_id}", array_values($save_data));
        if($result==true) {
          foreach ($save_data as $key => $value) {
            $_SESSION['user'][$key] = $value;
          }
        };
        return $result;
      }
      catch(Exception $e){
        #echo $e->getMessage();
        return false;
      }
    }

   	public function recover_pass($user_id)
   	{
   		try {
   			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `id`=? ";
   			$user = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$user_id]);
   			if($user == false) return $user;
   			$key = \randString(32);
   			$urlrecover = \siteinfo('siteurl').__url_s("/%forgotten_password_slug%")."?kr={$key}&ue=".urlencode($user->email);
   			$updated = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` SET `keyrecov`=? WHERE `id`={$user_id}", [$key]);
   			if($updated !== false){
          $mail = new \PACMEC\System\EmailsTemplates((object) ['template_slug'=>infosite('register_forgotten_password')]);
          if($mail->isValid()){
            $mail->set_autot([
     			    '%sitelogo%',
     			    '%sitename%',
     			    '%PreviewText%',
     			    '%recover_password_from%',
     			    '%recover_password_text%',
     			    '%display_name%',
     			    '%username%',
     			    '%email%',
     			    '%urlrecover%',
     			    '%siteurl%',
     			    '%recover_password%',
            ], [
     			    infosite('sitelogo'),
     			    infosite('sitename'),
     			    infosite('sitedescr'),
     			    __a('recover_password_from'),
     			    __a('recover_password_text'),
     			    "{$user->names} {$user->surname}",
     			    "{$user->username}",
     			    $user->email,
     			    $urlrecover,
     			    infosite('siteurl').infosite('homeurl'),
     			    __a('recover_password'),
            ]);
            return $result_send = $mail->send(__a('recover_password'), $user->email, "{$user->names} {$user->surname}");
          }

          /*
   			  $email_contact_received = $user->email;
   			  $e_subject = _autoT('recover_password_from');
   			  $template_org = file_get_contents(PACMEC_PATH.'templates-mails/recover-password.php', true);
   				$tags_in = [
   			  ];
   			  $tags_out = [
   			  ];
   			  $template = \str_replace($tags_in, $tags_out, $template_org);
   			  $mail = new PHPMailer(true);
   			  try {
   			      //Server settings
   			      //$mail->SMTPDebug = 2;                 // Enable verbose debug output
   			      $mail->isSMTP();                      // Set mailer to use SMTP
   			      $mail->Host       = SMTP_HOST;        // Specify main and backup SMTP servers
   			      $mail->SMTPAuth   = SMTP_AUTH;        // Enable SMTP authentication
   			      $mail->Username   = SMTP_USER;        // SMTP username
   			      $mail->Password   = SMTP_PASS;        // SMTP password
   			      $mail->SMTPSecure = SMTP_SECURE;      // Enable TLS encryption, `ssl` also accepted
   			      $mail->Port       = SMTP_PORT;        // TCP port to connect to
   			      $mail->CharSet    = infosite('charset');
   			      //Recipients
   			      $mail->setFrom($email_contact_from, infosite('sitename'));
   			      $mail->addAddress($email_contact_received);     // Add a recipient Name is optional (, 'name')
   			      // $mail->addReplyTo($email_contact_from, $e_subject);
   			      if(SMTP_CC!==false) $mail->addCC(SMTP_CC);
   			      if(SMTP_BCC!==false) $mail->addBCC(SMTP_BCC);
   						// Content
   			      $mail->isHTML(true);                                  // Set email format to HTML
   			      $mail->Subject = $e_subject;
   			      $mail->Body    = ($template);
   			      $mail->AltBody = \strip_tags($template);
   			      return ($mail->send());
   			  } catch (Exception $e) {
   			      return false;
   			  }
          */
   			}
   		} catch (\Exception $e) {
   			return false;
   		}
   	}

   	public function change_pass($password)
   	{
   		try {
   			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `id`=? ";
   			$user = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$this->user->id]);
   			if($user == false) return $user;
   			$hash = password_hash($password, PASSWORD_DEFAULT);
   			$updated = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` SET `hash`=?,`keyrecov`=? WHERE `id`={$this->user->id}", [$hash,NULL]);
   			return $updated;
   		} catch (\Exception $e) {
   			return false;
   		}
  	}

    public function check_password($password)
    {
      return \password_verify($password, $GLOBALS['PACMEC']['session']->user->hash);
    }

    public function remove_from_cart($id, $session_id=null)
    {
      try {
        $session_id = !isset($session_id) ? session_id() : $session_id;
        $sql = "DELETE FROM `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` WHERE `session_id`=? AND `id`=? ";
        $result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$session_id, $id]);
        if($result==true){
          $this->refreshSession();
        }
      } catch (\Exception $e) {
        return "add_to_cart_fail";
      }
    }

    public function add_in_cart($item, $quantity, $type='undefined')
    {
      try {
        switch ($type) {
          case 'product':
            // $product = null;
            if(is_numeric($item)){
              $search_sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` WHERE `session_id`=? AND `type`=? AND `ref_id`=?";
              $search = $GLOBALS['PACMEC']['DB']->FetchObject($search_sql, [
                session_id(),
                $type,
                $item
              ]);
              if($search !== false) {
                  $this->update_quantity_in_cart($item, ($quantity+$search->quantity), $type);
              } else {
                $product = new \PACMEC\System\Product((object) ['id'=>$item]);
                if($product->isValid()){
                  $a_c  = (int) $product->available;
                  if($a_c>0){
                    $a_cc = !isset($this->shopping_cart["product:{$product->id}"]) ? 0 : $this->shopping_cart["product:{$product->id}"]->quantity;
                    $a_i = ($a_c >= ($a_cc+$quantity)) ? ($a_cc+$quantity) : $a_c;
                    $id_shop = !isset($this->shopping_cart["product:{$product->id}"]) ? null : $this->shopping_cart["product:{$product->id}"]->id;
                    $sql = "INSERT INTO `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` (`session_id`, `type`, `ref_id`, `quantity`)
                      SELECT ?, ?, ?, ?
                      WHERE NOT EXISTS(SELECT 1 FROM `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` WHERE `session_id`=? AND `type`=? AND `ref_id`=?)";

                    $result = $GLOBALS['PACMEC']['DB']->FetchObject($sql,
                      [
                        session_id(),
                        $type,
                        $item,
                        $a_i,

                        session_id(),
                        $type,
                        $item,
                      ]
                    );
                    $this->refreshSession();
                    //header("Location: ".$_SERVER['PHP_SELF']);
                    return $result == true ? "add_to_cart_success" : "add_to_cart_fail";
                  } else {
                    return "product_not_available";
                  }
                }
              }
            }
            return "add_to_cart_fail";
            break;
          case 'service':
            // $service = null;
            if(is_numeric($item)){
              $search_sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` WHERE `session_id`=? AND `type`=? AND `ref_id`=?";
              $search = $GLOBALS['PACMEC']['DB']->FetchObject($search_sql, [
                session_id(),
                $type,
                $item
              ]);
              if($search !== false) {
                  $this->update_quantity_in_cart($item, ($quantity+$search->quantity), $type);
              } else {
                $service = new \PACMEC\System\Service((object) ['id'=>$item]);
                if($service->isValid()){
                  $a_c  = (int) $service->is_active;
                  if($a_c>0){
                    $a_cc = !isset($this->shopping_cart["service:{$service->id}"]) ? 0 : $this->shopping_cart["service:{$service->id}"]->quantity;
                    $a_i = ($a_c >= ($a_cc+$quantity)) ? ($a_cc+$quantity) : $a_c;
                    $id_shop = !isset($this->shopping_cart["service:{$service->id}"]) ? null : $this->shopping_cart["service:{$service->id}"]->id;
                    $sql = "INSERT INTO `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` (`session_id`, `type`, `ref_id`, `quantity`)
                    SELECT ?, ?, ?, ?
                    WHERE NOT EXISTS(SELECT 1 FROM `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}` WHERE `session_id`=? AND `type`=? AND `ref_id`=?)";

                    $result = $GLOBALS['PACMEC']['DB']->FetchObject($sql,
                      [
                        session_id(),
                        $type,
                        $item,
                        $a_i,

                        session_id(),
                        $type,
                        $item,
                      ]
                    );
                    $this->refreshSession();
                    //header("Location: ".$_SERVER['PHP_SELF']);
                    return $result == true ? "add_to_cart_success" : "add_to_cart_fail";
                  } else {
                    return "service_not_available";
                  }
                }
              }
            }
            return "add_to_cart_fail";
            break;
          default:
            break;
        }
      } catch (\Exception $e) {
        return "add_to_cart_fail";
      }
    }

    public function update_quantity_in_cart($item, $quantity, $type='product')
    {
      try {
        switch ($type) {
          case 'product':
            // $product = null;
            if(is_numeric($item)){
              $product = new \PACMEC\System\Product((object) ['id'=>$item]);
              if($product->isValid()){
                $a_c  = (int) $product->available;
                if($a_c>0){
                  $a_cc = !isset($this->shopping_cart["product:{$product->id}"]) ? 0 : $this->shopping_cart["product:{$product->id}"]->quantity;
                  $a_i = ($a_c >= ($quantity)) ? ($quantity) : $a_c;
                  $id_shop = !isset($this->shopping_cart["product:{$product->id}"]) ? null : $this->shopping_cart["product:{$product->id}"]->id;
                  $result = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}`
                  SET `session_id`=?, `type`=?, `ref_id`=?, `quantity`=? WHERE `id`=?",
                  [
                    session_id(),
                    $type,
                    $item,
                    $a_i,
                    $id_shop,
                  ]);
                  $this->refreshSession();
                  //unset($_POST);
                  //header("Location: ".$_SERVER['PHP_SELF']);

                  return $result == true ? "update_to_cart_success" : "update_to_cart_fail";
                } else {
                  return "product_not_available";
                }
              }
            }
            return "update_to_cart_fail";
            break;
          case 'service':
            // $service = null;
            if(is_numeric($item)){
              $service = new \PACMEC\System\Service((object) ['id'=>$item]);
              if($service->isValid()){
                $a_c  = (int) $service->is_active;
                if($a_c>0){
                  $a_cc = !isset($this->shopping_cart["service:{$service->id}"]) ? 0 : $this->shopping_cart["service:{$service->id}"]->quantity;
                  $a_i = ($a_c >= ($quantity)) ? ($quantity) : $a_c;
                  $id_shop = !isset($this->shopping_cart["service:{$service->id}"]) ? null : $this->shopping_cart["service:{$service->id}"]->id;
                  $result = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE `{$GLOBALS['PACMEC']['DB']->getTableName('shoppings_carts')}`
                  SET `session_id`=?, `type`=?, `ref_id`=?, `quantity`=? WHERE `id`=?",
                  [
                    session_id(),
                    $type,
                    $item,
                    $a_i,
                    $id_shop,
                  ]);
                  $this->refreshSession();
                  //unset($_POST);
                  //header("Location: ".$_SERVER['PHP_SELF']);

                  return $result == true ? "update_to_cart_success" : "update_to_cart_fail";
                } else {
                  return "service_not_is_active";
                }
              }
            }
            return "update_to_cart_fail";
            break;
          default:
            break;
        }
      } catch (\Exception $e) {
        return "update_to_cart_fail";
      }
    }

    public function get_cart_table_html($items=null)
    {
      $items = ($items == null) ? $this->shopping_cart : $items;
      //$table = \PACMEC\Table::borderedTable();
      $thead = \PACMEC\Util\Html::tag('thead',
        \PACMEC\Util\Html::tag('tr',
            \PACMEC\Util\Html::tag('th', '',              ['pro-thumbnail'], [])
          . \PACMEC\Util\Html::tag('th', __a('detail'),   ['pro-title'], [])
          . \PACMEC\Util\Html::tag('th', __a('price'),    ['pro-price'], [])
          . \PACMEC\Util\Html::tag('th', __a('quantity'), ['pro-quantity'], [])
          . \PACMEC\Util\Html::tag('th', __a('subtotal'), ['pro-subtotal'], [])
          . \PACMEC\Util\Html::tag('th', '',              ['pro-remove'], ['width'=>'5%'])
        , [], [])
      , [], []);
      /*
      $table->addHeaderRow([
        'Thumb'
        , 'Details'
        , 'Price'
        , 'Quantity'
        , 'Total'
        , 'Remove'
      ]);
      */
      $rows = '';
      foreach ($items as $key=>$item) {
        $url_item = isset($item->data->link_view) ? $item->data->link_view : "#";
        $thumb_uri = (isset($item->data->thumb) ? $item->data->thumb : infosite('default_picture'));
        $img = \PACMEC\Util\Html::tag('img', '', ['img-fluid'], ['src'=>$thumb_uri], true);
        /*
        switch ($item->type) {
          case 'product':
            $name = ;
            break;
          default:
            $name = "Sin parametrizar";
            break;
        }
        */

        $rows .= \PACMEC\Util\Html::tag('tr',
            \PACMEC\Util\Html::tag('td', \PACMEC\Util\Html::tag('a', $img, [], ['href'=>$url_item]), ['pro-thumbnail'], [])
          . \PACMEC\Util\Html::tag('td', \PACMEC\Util\Html::tag('a',
            ($item->data->name)
          , [], ['href'=>$url_item]), ['pro-title'], [])
          . \PACMEC\Util\Html::tag('td', formatMoney($item->data->price), ['pro-price'], [])
          // . \PACMEC\Util\Html::tag('td', "{$item->quantity} {$item->data->unid}", ['pro-quantity'], [])
          . \PACMEC\Util\Html::tag('td',
              \PACMEC\Util\Html::tag('div',
                \PACMEC\Util\Html::tag('div',
                  \PACMEC\Util\Html::tag('input', '', ['cart-plus-minus-box'], ["name"=> $key, "value"=>$item->quantity, "type"=>"text", "max"=>($item->type == 'product' ? ((int) $item->data->available) : 1), "step"=>"1"], true)
                , ['cart-plus-minus'], [])
              , ['quantity'], [])
            , ['pro-quantity'], [])
          . \PACMEC\Util\Html::tag('td', \PACMEC\Util\Html::tag('span', formatMoney($item->data->price*$item->quantity), [], []), ['pro-subtotal'], [])
          . \PACMEC\Util\Html::tag('td', \PACMEC\Util\Html::tag('a', \PACMEC\Util\Html::tag('i', '', ['pe-7s-trash'], []), [], ['href'=>__url_s('/%cart_slug%?discard-in-cart='.$item->id)]), ['pro-remove'], [])
        , [], []);


        /*
        $table->addRow([
          \PACMEC\Util\Html::tag('a', $img, [], ["href"=>$url_item])
        ]);*/
      }
      $tbody = \PACMEC\Util\Html::tag('tbody', $rows, [], []);
      return \PACMEC\Util\Html::tag('table', $thead.$tbody, ['table', 'table-bordered'], []);
    }

  }

}
