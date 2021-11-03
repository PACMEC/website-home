<?php
/**
* Theme Name: PACMEC
* Text Domain: pacmec
* Description: Tema principal de PACMEC
* Version: 1.0.0
* Author: FelipheGomez
* Author URI: https://github.com/FelipheGomez
*
* @author     FelipheGomez <info@pacmec.co>
* @package    PACMEC Admin
* @category   Themes
* @copyright  2021 FelipheGomez
* @version    1.0.0
*/

function Theme_PACMEC_activation()
{
  try {
    # require_once 'includes/shortcodes.php';
    /**
    * Head
    */
    add_style_head(\folder_theme("pacmec")."/assets/plugins/bootstrap/css/bootstrap.min.css",                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.40, false);
    add_style_head(\folder_theme("pacmec")."/assets/plugins/jvectormap/jquery-jvectormap-2.0.3.css",                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.39, false);
    add_style_head(\folder_theme("pacmec")."/assets/plugins/morrisjs/morris.css",                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.38, false);
    add_style_head(\folder_theme("pacmec")."/assets/css/main.css",                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.37, false);
    add_style_head(\folder_theme("pacmec")."/assets/css/color_skins.css",                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.36, false);
    #add_scripts_head(\folder_theme("pacmec")."/assets/js/jquery-1.11.1.min.js",              ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    /**
    * Footer
    */
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/libscripts.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.40, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/vendorscripts.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.39, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/jvectormap.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.38, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/morrisscripts.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.37, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/sparkline.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.36, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/knob.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.35, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/bundles/mainscripts.bundle.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.34, false);
		add_scripts_foot(\folder_theme("pacmec")."/assets/js/pages/charts/jquery-knob.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.33, false);
    /**
    * Revisar tablas necesarias
    */
    $tbls = [
      #"Tabla1",
    ];
    foreach ($tbls as $tbl) {
     if(!pacmec_tbl_exist($tbl)){
       throw new \Exception("Falta la tbl: {$tbl}", 1);
     }
    }
  } catch (Exception $e) {
    echo $e->getMessage();
    exit;
  }
}
\register_activation_plugin('pacmec', 'Theme_PACMEC_activation');
