<?php

function getIpRemote()
{
  $ip = "0.0.0.0";
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
      $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}

/**
* Input data JSON
*
* @author FelipheGomez <feliphegomez@pm.me>
* @return  : Array
*/
function input_post_data_json() : array
{
  try {
    $r  =  [];
    $rawData = @\file_get_contents("php://input");
    if(@\json_decode($rawData) !== null) foreach (@\json_decode($rawData) as $k => $v) $r[$k] = $v;
    return $r;
  } catch (\Exception $e) {
    return [];
  }
}

function is_session_started()
{
   if (php_sapi_name() === 'cli')
       return false;
   if (version_compare(phpversion(), '5.4.0', '>='))
       return session_status() === PHP_SESSION_ACTIVE;
   return session_id() !== '';
}

function getOperatingSystem()
{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$os_platform =   "Bilinmeyen İşletim Sistemi";
	$os_array =   array(
		'/windows nt 10/i'      =>  'Windows 10',
		'/windows nt 6.3/i'     =>  'Windows 8.1',
		'/windows nt 6.2/i'     =>  'Windows 8',
		'/windows nt 6.1/i'     =>  'Windows 7',
		'/windows nt 6.0/i'     =>  'Windows Vista',
		'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     =>  'Windows XP',
		'/windows xp/i'         =>  'Windows XP',
		'/windows nt 5.0/i'     =>  'Windows 2000',
		'/windows me/i'         =>  'Windows ME',
		'/win98/i'              =>  'Windows 98',
		'/win95/i'              =>  'Windows 95',
		'/win16/i'              =>  'Windows 3.11',
		'/macintosh|mac os x/i' =>  'Mac OS X',
		'/mac_powerpc/i'        =>  'Mac OS 9',
		'/linux/i'              =>  'Linux',
		'/ubuntu/i'             =>  'Ubuntu',
		'/iphone/i'             =>  'iPhone',
		'/ipod/i'               =>  'iPod',
		'/ipad/i'               =>  'iPad',
		'/android/i'            =>  'Android',
		'/blackberry/i'         =>  'BlackBerry',
		'/webos/i'              =>  'Mobile'
	);
	foreach ( $os_array as $regex => $value ) {
		if ( preg_match($regex, $user_agent ) ) {
			$os_platform = $value;
		}
	}
	return $os_platform;
}

/**
 * Kullanicinin kullandigi internet tarayici bilgisini alir.
 *
 * @since 2.0
 */
function getBrowser()
{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$browser        = "Bilinmeyen Tarayıcı";
	$browser_array  = array(
		'/msie/i'       =>  'Internet Explorer',
		'/firefox/i'    =>  'Firefox',
		'/safari/i'     =>  'Safari',
		'/chrome/i'     =>  'Chrome',
		'/edge/i'       =>  'Edge',
		'/opera/i'      =>  'Opera',
		'/netscape/i'   =>  'Netscape',
		'/maxthon/i'    =>  'Maxthon',
		'/konqueror/i'  =>  'Konqueror',
		'/mobile/i'     =>  'Handheld Browser'
	);

	foreach ( $browser_array as $regex => $value ) {
		if ( preg_match( $regex, $user_agent ) ) {
			$browser = $value;
		}
	}
	return $browser;
}

function validate_file($file_path)
{
  $info_r = [];
  if((!\is_file($file_path) && !\file_exists($file_path)) || \is_dir($file_path) && \is_file($file_path)) return $info_r;
  $texto = @\file_get_contents($file_path);
  $input_line = \nl2br($texto);
  \preg_match_all('/[*\s]+([a-zA-Z\s\i]+)[:]+[\s]+([a-zA-Z0-9]+[^<]+)/mi', $input_line, $detect_array);
  if(isset($detect_array[1]) && isset($detect_array[2])){
    foreach($detect_array[1] as $i=>$lab){
      $_tag = \str_replace(['  ', ' ', '+'], '_', \strtolower($lab));
      $_val = $detect_array[2][$i];
      $info_r[$_tag] = $_val;
    }
  }
  return $info_r;
}

function extract_info_lang($file_path)
{
  $info_r = [];
  if((!\is_file($file_path) && !\file_exists($file_path)) || \is_dir($file_path) && \is_file($file_path)) return $info_r;
  $texto = @\file_get_contents($file_path);
  $input_line = \nl2br($texto);
  \preg_match_all('/[*\s](.+)[=]+[\s]+([a-zA-Z0-9]+[^<]+)/mi', $input_line, $detect_array);
  foreach($detect_array[1] as $i=>$lab){ $info_r[\str_replace([], [], ($lab))] = $detect_array[2][$i]; }
  return $info_r;
}

function siteinfo($option_name)
{
  if(!isset($GLOBALS['PACMEC']['site']->settings[$option_name])){
    return "NaN";
    return "NaN - {$option_name} - NaN";
  }
  return @html_entity_decode($GLOBALS['PACMEC']['site']->settings[$option_name]);
}

/**
* Traduccion automatica
* @param string   $label       *
* @param string   $lang        (Optional)
* @return string
*/
function __at($label, $lang=null) : string
{
  try {
    global $PACMEC;
    $lang = ($lang == null) ? $GLOBALS["PACMEC"]['settings']['lang'] : $lang;
    if(isset($PACMEC['i18n'][$lang])){
      if(isset($PACMEC['i18n'][$lang]['dictionary'][$label])) {
        return $PACMEC['i18n'][$lang]['dictionary'][$label];
      } else {
        $slug = $label;
        $text = "þ{{$label}}";
        if(\__site("dictionary_insert")===true){
          $glossary_id = $GLOBALS['PACMEC']['i18n'][$GLOBALS['PACMEC']["lang"]];

          $sql_ins = "INSERT INTO `{$GLOBALS['PACMEC']['DB']->getTableName('glossary')}` (`i18n`, `site`, `slug`, `text`)
          SELECT * FROM (SELECT {$glossary_id},'{$slug}','{$text}') AS tmp WHERE NOT EXISTS (SELECT `id` FROM `{$GLOBALS['PACMEC']['DB']->getTableName('glossary')}` WHERE `i18n` = '{$glossary_id}' AND `slug` = '{$slug}') LIMIT 1";
          $insert = $GLOBALS['PACMEC']['DB']->FetchObject($sql_ins, []);
        }
      }
    }
    return "þ{{$label}}";
  } catch (\Exception $e) {
    return "ÞE{{$label}}";
  }
}

function add_style_head($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['styles']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['styles']['head'][] = [
      "tag" => "link",
      "attrs" => array_merge($attrs, [
        "href" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function add_style_foot($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['styles']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['styles']['foot'][] = [
      "tag" => "link",
      "attrs" => array_merge($attrs, [
        "href" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function add_scripts_head($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['scripts']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['scripts']['head'][] = [
      "tag" => "script",
      "attrs" => array_merge($attrs, [
        "src" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function add_scripts_foot($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['scripts']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['scripts']['foot'][] = [
      "tag" => "script",
      "attrs" => array_merge($attrs, [
        "src" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function __url($link_href)
{
  return (str_replace(array_keys($GLOBALS['PACMEC']['permanents_links']), array_values($GLOBALS['PACMEC']['permanents_links']), $link_href));
}

function __site($option_name)
{
  if(!isset($GLOBALS['PACMEC']['site']->settings[$option_name])){
    return "NaN";
  }
  return @html_entity_decode($GLOBALS['PACMEC']['site']->settings[$option_name]);
}

/** Short access Hooks **/
/**
 * Execute functions hooked on a specific action hook.
 *
 * @param    string $tag     <p>The name of the action to be executed.</p>
 * @param    mixed  $arg     <p>
 *                           [optional] Additional arguments which are passed on
 *                           to the functions hooked to the action.
 *                           </p>
 *
 * @return   bool            <p>Will return false if $tag does not exist in $filter array.</p>
 */
function do_action(string $tag, $arg = ''): bool
{
  global $PACMEC;
	return $PACMEC['hooks']->do_action($tag, $arg);
}

/**
 * Hooks a function on to a specific action.
 *
 * @param    string       $tag              <p>
 *                                          The name of the action to which the
 *                                          <tt>$function_to_add</tt> is hooked.
 *                                          </p>
 * @param    string|array $function_to_add  <p>The name of the function you wish to be called.</p>
 * @param    int          $priority         <p>
 *                                          [optional] Used to specify the order in which
 *                                          the functions associated with a particular
 *                                          action are executed (default: 50).
 *                                          Lower numbers correspond with earlier execution,
 *                                          and functions with the same priority are executed
 *                                          in the order in which they were added to the action.
 *                                          </p>
 * @param     string      $include_path     <p>[optional] File to include before executing the callback.</p>
 *
 * @return bool
 */
function add_action(string $tag, $function_to_add, int $priority = 50, string $include_path = null) : bool
{
	return $GLOBALS['PACMEC']['hooks']->add_action($tag, $function_to_add, $priority, $include_path);
}

/**
 *
 * Add hook for shortcode tag.
 *
 * <p>
 * <br />
 * There can only be one hook for each shortcode. Which means that if another
 * plugin has a similar shortcode, it will override yours or yours will override
 * theirs depending on which order the plugins are included and/or ran.
 * <br />
 * <br />
 * </p>
 *
 * Simplest example of a shortcode tag using the API:
 *
 * <code>
 * // [footag foo="bar"]
 * function footag_func($atts) {
 *  return "foo = {$atts[foo]}";
 * }
 * add_shortcode('footag', 'footag_func');
 * </code>
 *
 * Example with nice attribute defaults:
 *
 * <code>
 * // [bartag foo="bar"]
 * function bartag_func($atts) {
 *  $args = shortcode_atts(array(
 *    'foo' => 'no foo',
 *    'baz' => 'default baz',
 *  ), $atts);
 *
 *  return "foo = {$args['foo']}";
 * }
 * add_shortcode('bartag', 'bartag_func');
 * </code>
 *
 * Example with enclosed content:
 *
 * <code>
 * // [baztag]content[/baztag]
 * function baztag_func($atts, $content='') {
 *  return "content = $content";
 * }
 * add_shortcode('baztag', 'baztag_func');
 * </code>
 *
 * @param string   $tag  <p>Shortcode tag to be searched in post content.</p>
 * @param callable $callback <p>Hook to run when shortcode is found.</p>
 *
 * @return bool
 */
function add_shortcode($tag, $callback) : bool
{
	if($GLOBALS['PACMEC']['hooks']->shortcode_exists($tag) == false){
		/*
		if(!isset($_GET['editor_front'])){
		} else {
			return $GLOBALS['PACMEC']['hooks']->add_shortcode( $tag, function() use ($tag) { echo "[{$tag}]"; } );
			return true;
		};*/
		return $GLOBALS['PACMEC']['hooks']->add_shortcode( $tag, $callback );
	} else {
		return false;
	}
}

/**
*
* Add
*
* @param array   $pairs       *
* @param array   $atts        *
* @param string  $shortcode   (Optional)
*
* @return array
*/
function shortcode_atts($pairs, $atts, $shortcode = ''): array
{
	return $GLOBALS['PACMEC']['hooks']->shortcode_atts($pairs, $atts, $shortcode);
}

/**
 *
 * Adds Hooks to a function or method to a specific filter action.
 *
 * @param    string              $tag             <p>
 *                                                The name of the filter to hook the
 *                                                {@link $function_to_add} to.
 *                                                </p>
 * @param    string|array|object $function_to_add <p>
 *                                                The name of the function to be called
 *                                                when the filter is applied.
 *                                                </p>
 * @param    int                 $priority        <p>
 *                                                [optional] Used to specify the order in
 *                                                which the functions associated with a
 *                                                particular action are executed (default: 50).
 *                                                Lower numbers correspond with earlier execution,
 *                                                and functions with the same priority are executed
 *                                                in the order in which they were added to the action.
 *                                                </p>
 * @param string                 $include_path    <p>
 *                                                [optional] File to include before executing the callback.
 *                                                </p>
 *
 * @return bool
 */
function add_filter(string $tag, $function_to_add, int $priority = 50, string $include_path = null): bool
{
  return $GLOBALS['PACMEC']['hooks']->add_filter($tag, $function_to_add, $priority, $include_path);
}

/**
 * Search content for shortcodes and filter shortcodes through their hooks.
 *
 * <p>
 * <br />
 * If there are no shortcode tags defined, then the content will be returned
 * without any filtering. This might cause issues when plugins are disabled but
 * the shortcode will still show up in the post or content.
 * </p>
 *
 * @param string $content <p>Content to search for shortcodes.</p>
 *
 * @return string <p>Content with shortcodes filtered out.</p>
 */
function do_shortcode(string $content) : string
{
	return $GLOBALS['PACMEC']['hooks']->do_shortcode($content);
}

function pacmec_meta_head()
{
  $a = "\n\t\t";
  foreach($GLOBALS['PACMEC']['website']['meta'] as $meta){
   $a .= \PACMEC\Util\Html::tag($meta['tag'], $meta['content'], [], $meta['attrs'], (in_array($meta['tag'], ['title'])?false:true))."\n\t\t";
  }
  echo $a;
  return $a;
  #echo json_encode($GLOBALS['PACMEC']['website']['meta'], JSON_PRETTY_PRINT);
}

function isUser() : bool
{
	return !(isGuest());
}

function isGuest() : bool
{
	return !isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || $_SESSION['user']['id']<=0 ? true : false;
}

function pacmec_add_meta_tag($name_or_property_or_http_equiv_or_rel, $content, $ordering=0.35, $atts=[])
{
  switch ($name_or_property_or_http_equiv_or_rel) {
    case 'title':
    case 'description':
    case 'url':
      if($name_or_property_or_http_equiv_or_rel == 'title' && strlen($content) <= 350) $content = $content . " | " . \siteinfo('name');
      if($name_or_property_or_http_equiv_or_rel == 'description' && strlen($content) <= 350) $content = $content;
      if($name_or_property_or_http_equiv_or_rel == 'title') $GLOBALS['PACMEC']['website']['meta'][] = [ "tag" => "title", "content" => $content, "attrs" => [], "ordering" => $ordering ];
      //
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "name" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      \pacmec_add_meta_tag('og:'.$name_or_property_or_http_equiv_or_rel, $content);
      break;
    case 'keywords':
    case 'language':
    case 'robots':
    case 'Classification':
    case 'author':
    case 'designer':
    case 'copyright':
    case 'reply-to':
    case 'owner':
    case 'Expires':
    case 'Pragma':
    case 'Cache-Control':
    case 'generator':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "name" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    case 'image':
      pacmec_add_meta_tag('og:image', $content);
      break;
    case 'fb:page_id':
    case 'fb:app_id':
    case 'og:site_name':
    case 'og:email':
    case 'og:phone_number':
    case 'og:fax_number':
    case 'og:latitude':
    case 'og:longitude':
    case 'og:street-address':
    case 'og:locality':
    case 'og:region':
    case 'og:postal-code':
    case 'og:country-name':
    case 'og:url':
    case 'og:title':
    case 'og:type':
    case 'og:image':
    case 'og:description':
    case 'og:points':
    case 'og:video':
    case 'og:video:height':
    case 'og:video:width':
    case 'og:video:type':
    case 'og:audio':
    case 'og:audio:title':
    case 'og:audio:artist':
    case 'og:audio:album':
    case 'og:audio:type':
    case 'product:plural_title':
    case 'product:price:amount':
    case 'product:price:currency':
    case 'ia:markup_url':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "property" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    case 'favicon':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "link", "attrs" => array_merge($atts, [ "rel" => "shortcut icon", "href" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    case 'canonical':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "link", "attrs" => array_merge($atts, [ "rel" => $name_or_property_or_http_equiv_or_rel, "href" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    default:
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "name" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
  }
}

function pageinfo($key)
{
	return isset($GLOBALS['PACMEC']['route']->{$key}) ? $GLOBALS['PACMEC']['route']->{$key} : siteinfo($key);
}

function DevBy()
{
  return base64_decode("UHJvdWRseSBEZXZlbG9wZWQgYnkg") . base64_decode("RmVsaXBoZUdvbWV6");
}

function pacmec_exist_meta($meta)
{
  $search_keys = in_array($meta, $GLOBALS['PACMEC']['website']['meta']);
  if($search_keys==true) return true;
  foreach ($GLOBALS['PACMEC']['website']['meta'] as $a => $metaTag){
    $name = isset($metaTag['attrs']['property'])
    ? $metaTag['attrs']['property']
    : (isset($metaTag['attrs']['name'])
      ? $metaTag['attrs']['name']
      : (isset($metaTag['attrs']['rel'])
        ? $metaTag['attrs']['rel'] : $a
      )
    );
    if($name == $meta) return true;
  }
  return false;
}
