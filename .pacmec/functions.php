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
function getBrowser() {
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
      # echo "{$i}: ";
      # echo "{$lab} - ";
      # echo "{$_tag} ||| ";
      # echo "{$_val}\n";
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
