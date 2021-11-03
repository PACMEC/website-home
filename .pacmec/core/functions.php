<?php
/**
 * @package    PACMEC
 * @category   Functions
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
function get_error_html(string $error_message="Ocurrio un problema.", $error_title="PACMEC-ERROR") : string
{
  return sprintf("<code style=\"background:#CCC;padding:5px;\"><b>%s</b>: <span>%s</span></code>", $error_title, $error_message);
}

/**
 * Init PACMEC Variables
 * @return bool true|false Result init PACMEC Vars
 */
function init_pacmec_vars() : bool
{
  try {
    global $PACMEC;
    $PACMEC['host']             = $_SERVER['SERVER_NAME'];
    $PACMEC['lang-detect']      = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
    $PACMEC['server_ip']        = $_SERVER['SERVER_ADDR'];
    $PACMEC['client_ip']        = \getIpRemote();
    $PACMEC['request']          = \getURL();
    $PACMEC['path_orig']        = \getURI();
    $PACMEC['path']             = \strtok($PACMEC['path_orig'], '?');
    $PACMEC['input']            = array_merge($_GET, $_POST, \input_post_data_json());
    $PACMEC['DB']               = NULL;
    $PACMEC['session']          = NULL;
    $PACMEC['hooks']            = NULL;
    $PACMEC['layout']           = NULL;
    $PACMEC['theme']            = NULL;
    $PACMEC['route']            = NULL;
    $PACMEC['alerts']           = [];
    $PACMEC['site']             = NULL;
    $PACMEC['gateways']         = [
     'payments' => [],
     'shipping' => [],
    ];
    $PACMEC['permanents_links'] = [];
    $PACMEC['i18n']       = [];
    $PACMEC['glossary']       = [];
    $PACMEC['website']          = [
      "meta" => [],
      "scripts" => ["head" => [], "foot" => [], "list" => []],
      "styles" => ["head" => [], "foot" => [], "list" => []]
    ];
    return true;
  } catch (\Exception $e) {
    return false;
  }
}

/**
 * Return Ip Client
 * @return String IP CLient
 */
function getIpRemote() : string
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
 * Return URL Complete Current
 * @return string SERVER + URI
 */
function getURL() : string
{
	$link = "http";
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $link = "https";
	return $link . "://" . $_SERVER['HTTP_HOST'] . \getURI();
}

/**
 * Return URI Complete Current
 * @return string URI
 */
function getURI() : string
{
	return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
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

function add_route(string $path, string $name, string $template_part) : bool
{
  try {
    $GLOBALS['PACMEC']['routes'][$path] = [
      "path" => \__url($path),
      "name" => $name,
      "component" => "template-parts/{$template_part}"
    ];
    return true;
  } catch (\Exception $e) {
    echo $e->getMessage();
    return false;
  }
}

function check_route($path) : bool
{
  return isset($GLOBALS['PACMEC']['routes'][$path]);
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
      # echo "{$i}: ";
      # echo "{$lab} - ";
      # echo "{$_tag} ||| ";
      # echo "{$_val}\n";
    }
  }
  return $info_r;
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
    $lang = ($lang == null) ? $PACMEC['lang-detect'] : $lang;
    if(isset($PACMEC['i18n'][$lang])){
      if(isset($PACMEC['i18n'][$lang]['dictionary'][$label])) {
        return $PACMEC['i18n'][$lang]['dictionary'][$label];
      } else {
        $slug = $label;
        $text = "þ{{$label}}";
        if(\__site("dictionary_insert")===true){
          $glossary_id = $GLOBALS['PACMEC']['i18n'][$GLOBALS['PACMEC']["lang-detect"]];

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

function __site($option_name)
{
  if(!isset($GLOBALS['PACMEC']['site']->settings[$option_name])){
    return "NaN";
  }
  return @html_entity_decode($GLOBALS['PACMEC']['site']->settings[$option_name]);
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

function pacmec_parse_value($option_value)
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

function siteinfo($option_name)
{
  if(!isset($GLOBALS['PACMEC']['site']->settings[$option_name])){
    return "NaN";
    return "NaN - {$option_name} - NaN";
  }
  return @html_entity_decode($GLOBALS['PACMEC']['site']->settings[$option_name]);
}

function isUser() : bool
{
	return !(isGuest());
}

function isGuest() : bool
{
	return !isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || $_SESSION['user']['id']<=0 ? true : false;
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

function __url($link_href)
{
  return (str_replace(array_keys($GLOBALS['PACMEC']['permanents_links']), array_values($GLOBALS['PACMEC']['permanents_links']), $link_href));
}

function validate_theme($theme):bool
{
  global $PACMEC;
  return in_array($theme, array_keys($PACMEC['themes']));
}

function activation_theme($theme)
{
  global $PACMEC;
  if(\validate_theme($theme)==true && $PACMEC['themes'][$theme]['active'] == false){
    require_once $PACMEC['themes'][$theme]['file'];
    //$PACMEC['themes'][$theme]['active'] = true;
    $PACMEC['themes'][$theme]['active'] = \do_action('activate_' . $theme);
    if($PACMEC['themes'][$theme]['active'] == true){
      $PACMEC['theme'] = $PACMEC['themes'][$theme];
    }
    return $PACMEC['themes'][$theme]['active'];
  }
  return false;
}

function register_activation_plugin($plugin, $function)
{
  return \add_action( 'activate_' . $plugin, $function );
}

function folder_theme($theme, $add_host=true) : string
{
  $R = $add_host == true ? \siteinfo('url') : "";
  if(isset($GLOBALS['PACMEC']['themes'][$theme])) $R .= "/.pacmec/themes/{$GLOBALS['PACMEC']['themes'][$theme]['text_domain']}";
  else $R .= "/.pacmec/themes/NODETECT";
  return $R;
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

function DevBy()
{
  return base64_decode("UHJvdWRseSBEZXZlbG9wZWQgYnkg") . base64_decode("RmVsaXBoZUdvbWV6");
}

function get_header()
{
  return \get_template_part("header");
}

/**
 * @param string $file <p>File</p>
 * @param array|object $attrs <p>attr</p>
**/
function get_template_part($file, $atts=null)
{
  try {
  	if(!is_file("{$GLOBALS['PACMEC']['theme']['path']}/{$file}.php") || !file_exists("{$GLOBALS['PACMEC']['theme']['path']}/{$file}.php")){
      throw new \Exception("No existe archivo. {$GLOBALS['PACMEC']['theme']['text_domain']} -> {$file}. {$GLOBALS['PACMEC']['theme']['path']}/{$file}.php", 1);
  	}
    if(isset($atts) && (is_array($atts) || is_object($atts))){
      foreach ($atts as $id_assoc => $valor) {
        if(!isset(${$id_assoc}) || ${$id_assoc} !== $valor){
          ${$id_assoc} = $valor;
        }
      }
    }
  	require_once "{$GLOBALS['PACMEC']['theme']['path']}/{$file}.php";
  } catch(\Exception $e) {
    echo("Error critico en tema: {$e->getMessage()}");
  }
}

function language_attributes()
{
	return "class=\"".siteinfo('html_type')."\" lang=\"{$GLOBALS['PACMEC']['lang-detect']}\"";
}

function pageinfo($key)
{
	return isset($GLOBALS['PACMEC']['route']->{$key}) ? $GLOBALS['PACMEC']['route']->{$key} : siteinfo($key);
}

/**
  * HTML
  */
function pacmec_head()
{
  do_action('meta_head');
  \stable_usort($GLOBALS['PACMEC']['website']['styles']['head'], 'pacmec_ordering_by_object_asc');
  \stable_usort($GLOBALS['PACMEC']['website']['scripts']['head'], 'pacmec_ordering_by_object_asc');
  \do_action( "head" );
  $a = "";
  foreach($GLOBALS['PACMEC']['website']['styles']['head'] as $file){ $a .= "\n\t\t".\PACMEC\Util\Html::tag($file['tag'], "", [], $file['attrs'], true)."\t"; }
  $a .= \PACMEC\Util\Html::tag('style', do_action( "head-styles" ), [], ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], false) . "\t";
  foreach($GLOBALS['PACMEC']['website']['scripts']['head'] as $file){ $a .= "\n\t\t".\PACMEC\Util\Html::tag($file['tag'], "", [], $file['attrs'], false)."\t"; }
  echo "\n\t\t<script type=\"text/javascript\">\n\t\t";
  echo '/* Scripts PACMEC */'."\n";
  echo "\n\t\t</script>";
  echo "{$a}";
  echo "\n\t\t<script type=\"text/javascript\" src=\"https://api-secure.solvemedia.com/papi/challenge.ajax?k=WUaM7W3EDjF716DvSF8VbMnPj1Kag7GL\"></script>";
  #echo "<script type=\"text/javascript\" src=\"http://api.solvemedia.com/papi/challenge.ajax\"></script>\n";
  echo "\n\t\t<script type=\"text/javascript\">";
  do_action( "head-scripts" );
  echo "\n\t\t</script>\n";
  return true;
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

function stable_usort(&$array, $cmp)
{
  $i = 0;
  $array = array_map(function($elt)use(&$i)
  {
      return [$i++, $elt];
  }, $array);
  usort($array, function($a, $b)use($cmp)
  {
      return $cmp($a[1], $b[1]) ?: ($a[0] - $b[0]);
  });
  $array = array_column($array, 1);
}

function pacmec_ordering_by_object_asc($a, $b)
{
  if(is_object($a)) $a = array($a);
  if(is_object($b)) $b = array($b);
  if ($a['ordering'] == $b['ordering']) {
      return 0;
  }
  return ($a['ordering'] > $b['ordering']) ? -1 : 1;
}

function isStore()
{
	return isset($GLOBALS['PACMEC']['route']->in_store) ? $GLOBALS['PACMEC']['route']->in_store : false;
}

function isAbout()
{
	return ($GLOBALS['PACMEC']['route']->path == \__url("/%pacmec_aboutus%")) ? true : false;
}

function pacmec_load_menu($menu_slug="")
{
  try {
    $m_s = $menu_slug;
    if(isset($GLOBALS['PACMEC']['menus'][$m_s])){
      return $GLOBALS['PACMEC']['menus'][$m_s];
      throw new \Exception("El menu ya fue cargado.");
    } else {
      //echo "menu: {$m_s}\n";
      $model_menu = new \PACMEC\System\Menu(["by_slug"=>$m_s]);
      //$model_menu = new \PACMEC\Menu();
      //$model_menu->getBy('slug', $m_s);
      if($model_menu->id>0){
        return $model_menu;
      } else {
        throw new \Exception("ÞERROR:(Menu [{$menu_slug}] no encontrado)");
      }
    }
    if($menu == null){
      throw new \Exception("ÞERROR:(Menu no invalido)");
    } else {
      return "repair: ".json_encode($meu);
    }
  } catch (\Exception $e) {
    echo $e->getMessage();
    return false;
  }
}

function isHome()
{
	return isset($GLOBALS['PACMEC']['route']->in_home) ? $GLOBALS['PACMEC']['route']->in_home : false;
}

function get_footer()
{
  return get_template_part("footer");
}

function PowBy()
{
  #return "&#169; ".infosite('sitename')." . " . infosite("footer_by") . base64_decode("IHwg") . base64_decode("UHJvdWRseSBEZXZlbG9wZWQgYnkgPGEgaHJlZj0iaHR0cHM6Ly9tYW5hZ2VydGVjaG5vbG9neS5jb20uY28vIj4") . base64_decode("TWFuYWdlciBUZWNobm9sb2d5PC9hPg");
   return "&#169; ".siteinfo('name')." . " . siteinfo("footer_by") . base64_decode("IHwg") . base64_decode("UHJvdWRseSBEZXZlbG9wZWQgYnkgPGEgaHJlZj0iaHR0cHM6Ly9naXRodWIuY29tL2ZlbGlwaGVnb21leiI+") . base64_decode("RmVsaXBoZUdvbWV6PC9hPg");
}

function pacmec_foot()
{
  \stable_usort($GLOBALS['PACMEC']['website']['styles']['foot'], 'pacmec_ordering_by_object_asc');
  \stable_usort($GLOBALS['PACMEC']['website']['scripts']['foot'], 'pacmec_ordering_by_object_asc');
  $a = "";
	foreach($GLOBALS['PACMEC']['website']['styles']['foot'] as $file){ $a .= "\n\t\t".\PACMEC\Util\Html::tag($file['tag'], "", [], $file['attrs'], true)."\t"; }
  $a .= \PACMEC\Util\Html::tag('style', do_action( "footer-styles" ), [], ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], false) . "\t";
	foreach($GLOBALS['PACMEC']['website']['scripts']['foot'] as $file){ $a .= "\n\t\t".\PACMEC\Util\Html::tag($file['tag'], "", [], $file['attrs'], false)."\t"; }
  // $a .= \PACMEC\Util\Html::tag('script', do_action( "footer-scripts" ), [], ["type"=>"text/javascript", "charset"=>"UTF-8"], false);
  echo "{$a}";
  echo "\n\t\t<script type=\"text/javascript\">";
    echo '
      function pacmec_run(){
        $notifications = $(".pacmec-change-status-notification-fast").on("click", (elm)=>{
          let data = $(elm.currentTarget).data();
          if(data.notification_id){
            $("#pacmec-change-status-notification-fast-icon-"+data.notification_id).attr("class", "fa fa-spinner fa-spin")
            let url = "'.\siteinfo('url').'/?controller=Pacmec&action=notifications_change_status_fast&notification_id="+data.notification_id+"&redirect="+location.href;
            // console.log("url", url);
            fetch(url)
            .then(response => response.json())
            .then(r => {
              console.log("r", r);
              if(r.error == false){
                $("#pacmec-change-status-notification-fast-icon-"+data.notification_id).attr("class", r.data);
                $(".pacmec-user-notifications-count").each((a,b) => {
                  $child = $(b);
                  console.log("a", a);
                  console.log("b", b);
                  console.log("$child", $child);
                  console.log("$child.text()", $child.text());
                  $count = parseInt($child.text());
                  if(Number.isInteger($count)){
                    $child.text($count-1);
                  }
                });
              } else {
                console.log("error", r);
                console.error(r);
              }
            });
          }
        });
      }
      window.addEventListener("load", pacmec_run)
    ';
  \do_action( "footer-scripts" );
  echo "\n\t\t</script>";
  //echo "<script type=\"text/javascript\" src=\"https://api-secure.solvemedia.com/papi/challenge.precheck?k=".siteinfo('solvemedia_k_c')."\"></script>";
  \do_action( "footer" );
  echo "\n";
	return true;
}

function errorHtml(string $error_message="Ocurrio un problema.", $error_title="Error")
{
	// '<a href="/pacmec/hola-mundo">CONTÁCTENOS <i class="fa fa-angle-right" aria-hidden="true"></i></a>'
	return sprintf("<h1>%s</h1><p>%s</p>", $error_title, $error_message);
}

function userID() : int
{
  return isUser() ? $_SESSION['user']['id'] : 0;
}

function route_active()
{
	if(isset($GLOBALS['PACMEC']['route']->is_actived) && isset($GLOBALS['PACMEC']['route']->path)){
		return true;
	} else {
		return false;
	}
}

function the_content()
{
  do_action('page_body');
  //echo do_shortcode($GLOBALS['PACMEC']['route']->content);
  #foreach ($GLOBALS['PACMEC']['route']->components as $component) { echo do_shortcode(\PACMEC\Util\Shortcode::tag($component->component, "", [], $component->data, false) . "\n"); }
}

function pacmec_captcha_check($name)
{
  if(\siteinfo('captcha_a')==true && isset($GLOBALS['PACMEC']['input']["submit-{$name}"])){
    switch (strtolower(\siteinfo('captcha_t'))) {
      case 'solvemedia':
      case 'pacmec':
      case 'native':
        if(isset($GLOBALS['PACMEC']['input']["adcopy_challenge-{$name}"]) && isset($GLOBALS['PACMEC']['input']["adcopy_response"])){
          $solvemedia_response = \solvemedia_check_answer(siteinfo('solvemedia_k_v'), \getIpRemote(), $GLOBALS['PACMEC']['input']["adcopy_challenge-{$name}"], $GLOBALS['PACMEC']['input']["adcopy_response"], siteinfo('solvemedia_k_h'));
          if (!$solvemedia_response->is_valid || $solvemedia_response->is_valid == false) {
            return 'captcha_r_'.str_replace([' '], ['_'], $solvemedia_response->error);
            return "captcha_r_error";
          } else {
            return "captcha_r_success";
          }
        }
        return 'captcha_r_no_detect';
        break;
      default:
        return false;
        break;
    }
    return 'captcha_r_error';
  } else {
    return "captcha_disabled";
  }
}

function pacmec_captcha_widget_html($id, $name, $theme)
{
  if(siteinfo('captcha_a')==true){
    switch (strtolower(siteinfo('captcha_t'))) {
      case 'solvemedia':
      case 'pacmec':
      case 'native':
        return solvemedia_widget_get_html($id, $name, $theme);
        break;
      default:
        return "Tipo de captcha incorrecto.";
        break;
    }
  } else {
    return "<!-- // captcha no habilitado -->";
  }
}

function randString($length=11)
{
  $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $char = @str_shuffle($char);
  for($i = 0, $rand = '', $l = @strlen($char) - 1; $i < $length; $i ++) {
      $rand .= $char[@mt_rand(0, $l)];
  }
  return $rand;
}

function __url_s_($path)
{
  return str_replace(array_values($GLOBALS['PACMEC']['permanents_links']), array_keys($GLOBALS['PACMEC']['permanents_links']), $path);
}

function solvemedia_widget_get_html($content=null, $id=null, $theme='blank')
{
  $content = $id!==null?$content:"acwidget";
  $id = $id!==null?$id:\randString(11);
  $R = "";
  $R .= solvemedia_widget_do_html($content, $id, $theme);
  $R .= solvemedia_widget_do_script($content, $id, $theme);
  return $R;
}

function solvemedia_widget_do_html($content, $id, $theme='blank')
{
  $R = "";
  switch ($theme) {
    case 'custom-pacmec':
      $add = "
        <div class=\"pacmec-row row justify-content-md-center\">
          <div class=\"col col-lg-1\"></div>
          <div class=\"mauto\">
            <div class=\"row justify-content-md-center\">
              <div class=\"mauto\">
                <div id=\"adcopy-puzzle-image-{$id}\" style=\"height: 150px; width: 300px; text-align: left;\">&nbsp;</div>
                <div id=\"adcopy-puzzle-audio-{$id}\"></div>
              </div>
              <div class=\"col col-lg-1\">
                <div class=\"btn-toolbar\" role=\"toolbar\" aria-label=\"Toolbar with button groups\" id=\"adcopy-link-buttons-{$id}\">
                  <div class=\"btn-group-vertical mr-2\" role=\"group\" aria-label=\"First group\" id=\"adcopy-link-buttons-container-{$id}\">
                    <a href=\"javascript:ACPuzzle.moreinfo('{$id}')\" class=\"btn btn-sm btn-outline-secondary\" type=\"button\"><i class=\"fa fa-question\"></i></a>
                    <a href=\"javascript:ACPuzzle.change2audio('{$id}')\" id=\"adcopy-link-audio-{$id}\"   class=\"btn btn-sm btn-outline-secondary\" type=\"button\"><i class=\"fa fa-volume-up\"></i></a>
                    <a href=\"javascript:ACPuzzle.change2image('{$id}')\" id=\"adcopy-link-image-{$id}\"   class=\"btn btn-sm btn-outline-secondary\" type=\"button\"><i class=\"fa fa-text-width\"></i></a>
                    <a href=\"javascript:ACPuzzle.reload('{$id}')\"       id=\"adcopy-link-refresh-{$id}\" class=\"btn btn-sm btn-outline-secondary\" type=\"button\"><i class=\"fa fa-repeat\"></i></a>
                  </div>
                </div>
              </div>
            </div>
            <div class=\"row \" id=\"adcopy-instr-row-{$id}\">
              <div class=\"col col-lg-12\" id=\"adcopy-instr-row-{$id}\">
              <label for=\"adcopy_response-{$id}\" id=\"adcopy-instr-{$id}\"></label>
                <span id=\"adcopy-error-msg-{$id}\" style=\"display: none;\"></span>
                <div id=\"adcopy-pixel-image-{$id}\" style=\"display: none;\"></div>
                <div id=\"adcopy-pixel-audio-{$id}\" style=\"display: none;\"></div>
                <div id=\"adcopy-logo-cell-{$id}\" align=\"center\">
                  <span id=\"adcopy-logo-{$id}\">
                    <a id=\"adcopy-link-logo-{$id}\" title=\"\"></a>
                  </span>
                </div>
              </div>
              <div class=\"col col-lg-11\">
                <div class=\"input-group input-group-sm mb-3\" id=\"adcopy-response-cell-{$id}\">
                  <input class=\"pacmec-input pacmec-border pacmec-round-large\" id=\"adcopy_response-{$id}\" autocomplete=\"off\" name=\"adcopy_response\" size=\"20\" type=\"text\" required=\"\" />
                </div>
              </div>
              <div class=\"col col-lg-1\">
                <a href=\"javascript:ACPuzzle.moreinfo('{$id}')\"     id=\"adcopy-link-info-{$id}\" class=\"btn btn-sm btn-outline-secondary\"><i class=\"fa fa-question\"></i></a>
              </div>
            </div>
            <div class=\"row \">
              <div class=\"col col-lg-12\">
              <div id=\"adcopy_challenge_container-{$id}\">
                <input class=\"form-control\" id=\"adcopy_challenge-{$id}\" name=\"adcopy_challenge-{$id}\" type=\"hidden\" value=\"\" required=\"\" />
              </div>
              </div>
            </div>
          </div>
          <div class=\"col col-lg-1\"></div>
        </div>
          ";
      #
      $_retur = "
      <div class=\"pacmec-col s12 pacmec-center\">
        <div class=\"pacmec-row row justify-content-md-center\">
          <div class=\"pacmec-col s10 pacmec-center mauto\">
            <div class=\"pacmec-container pacmec-center pacmec-cp-img-box\" id=\"adcopy-puzzle-image-{$id}\"></div>
            <div id=\"adcopy-puzzle-audio-{$id}\"></div>
          </div>
          <div class=\"pacmec-col s2 pacmec-center col col-lg-1\">
            <div class=\"btn-toolbar\" role=\"toolbar\" aria-label=\"Toolbar with button groups\" id=\"adcopy-link-buttons-{$id}\">
              <div class=\"btn-group-vertical mr-2\" role=\"group\" aria-label=\"First group\" id=\"adcopy-link-buttons-container-{$id}\">
              <a href=\"javascript:ACPuzzle.moreinfo('{$id}')\"     class=\"pacmec-btn pacmec-button pacmec-round-xlarge pacmec-blue btn btn-sm btn-outline-secondary\" type=\"button\"><i class=\"fa fa-question\"></i></a>
              <a href=\"javascript:ACPuzzle.change2audio('{$id}')\" class=\"pacmec-btn pacmec-button pacmec-round-xlarge pacmec-blue btn btn-sm btn-outline-secondary\" id=\"adcopy-link-audio-{$id}\"   type=\"button\"><i class=\"fa fa-volume-up\"></i></a>
              <a href=\"javascript:ACPuzzle.change2image('{$id}')\" class=\"pacmec-btn pacmec-button pacmec-round-xlarge pacmec-blue btn btn-sm btn-outline-secondary\" id=\"adcopy-link-image-{$id}\"  type=\"button\"><i class=\"fa fa-text-width\"></i></a>
              <a href=\"javascript:ACPuzzle.reload('{$id}')\"       class=\"pacmec-btn pacmec-button pacmec-round-xlarge pacmec-blue btn btn-sm btn-outline-secondary\" id=\"adcopy-link-refresh-{$id}\" type=\"button\"><i class=\"fa fa-repeat\"></i></a>
              </div>
            </div>
          </div>
        </div>
        <div class=\"pacmec-row row\" id=\"adcopy-instr-row-{$id}\">
          <div class=\"pacmec-col s12 col col-lg-12\" id=\"adcopy-instr-row-{$id}\">
          <label for=\"adcopy_response-{$id}\" id=\"adcopy-instr-{$id}\"></label>
            <span id=\"adcopy-error-msg-{$id}\" style=\"display: none;\"></span>
            <div id=\"adcopy-pixel-image-{$id}\" style=\"display: none;\"></div>
            <div id=\"adcopy-pixel-audio-{$id}\" style=\"display: none;\"></div>
            <div id=\"adcopy-logo-cell-{$id}\" align=\"center\">
              <span id=\"adcopy-logo-{$id}\">
                <a id=\"adcopy-link-logo-{$id}\" title=\"\"></a>
              </span>
            </div>
          </div>
          <div class=\"pacmec-col s10 col col-lg-10\">
            <div class=\"pacmec-container3\" id=\"adcopy-response-cell-{$id}\" style=\"width:100%\">
              <input class=\"pacmec-input pacmec-border pacmec-border-0 pacmec-round-large\" id=\"adcopy_response-{$id}\" autocomplete=\"off\" name=\"adcopy_response\" size=\"20\" type=\"text\" required=\"\" style=\"width:100%;\" />
            </div>
          </div>
          <div class=\"pacmec-col s2 col col-lg-2\">
            <a href=\"javascript:ACPuzzle.moreinfo('{$id}')\"     id=\"adcopy-link-info-{$id}\" class=\"pacmec-btn pacmec-button pacmec-round-xlarge pacmec-gray btn btn-sm btn-outline-secondary\"><i class=\"fa fa-question\"></i></a>
          </div>
        </div>
        <div class=\"pacmec-row row\">
          <div class=\"pacmec-col s12 col col-lg-12\">
          <div id=\"adcopy_challenge_container-{$id}\">
            <input class=\"form-control\" id=\"adcopy_challenge-{$id}\" name=\"adcopy_challenge-{$id}\" type=\"hidden\" value=\"\" required=\"\" />
          </div>
          </div>
        </div>
      </div>
      <style>
      #adcopy-link-buttons-{$id} img {height:100% !important;width:auto !important;}
      .pacmec-cp-img-box img  {
        text-align: center;
        /* height: 150px; */
        /* width: 300px; */
        height: 100%;
        width: auto;
      }
      .pacmec-cp-img-box {
        height: 150px !important;
        width: 100% !important;
        text-align: center !important;
      }
      </style>
      ";

      $R .= \PACMEC\Util\Html::tag('div', \PACMEC\Util\Html::tag('div', $_retur, ['pacmec-container '], []), [], ["id"=>"adcopy-outer-{$id}"]);
      break;
    case 'custom':
      break;
    default:
      break;
  }
  return \PACMEC\Util\Html::tag('div', $R, [], ["id"=>$content, "style"=>"display:none"]);
}

function solvemedia_widget_do_script($content, $id, $theme='blank')
{
  if($theme=='custom-pacmec') $theme = 'custom';
  $pubkey = siteinfo('solvemedia_k_c');
  $R = "<script type=\"text/javascript\">
    window.addEventListener('load', function(){
      document.getElementById('{$content}').style.display = \"block\";
      ACPuzzle.create('{$pubkey}', '{$content}', {
        lang:  '".$GLOBALS['PACMEC']['lang-detect']."',
        size:  'standard',
        width: 'large',
        multi: true,
        id: '{$id}',
        type: 'img',
        theme: '{$theme}'
      });
    });
  </script>";
  return $R;
}
