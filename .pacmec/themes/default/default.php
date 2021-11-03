<?php
/**
* Theme Name: Default
* Text Domain: default
* Description: Tema principal de PACMEC
* Version: 1.0.0
* Author: FelipheGomez
* Author URI: https://github.com/FelipheGomez
*
* @author     FelipheGomez <feliphegomez@pm.me>
* @package    PACMEC
* @package    Themes
* @category   Default
* @copyright  2021 FelipheGomez
* @version    1.0.0
*/

function Theme_Default_activation()
{
  try {
    require_once 'includes/shortcodes.php';
    /**
    * Head
    */
    add_style_head("//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i",                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.50, false);
    add_style_head("//fonts.googleapis.com/css?family=Dosis:200,300,400,500,600,700",                                    ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.49, false);
    add_style_head(\folder_theme("default")."/assets/css/font-awesome.min.css",                                          ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.48, false);
    add_style_head(\folder_theme("default")."/assets/css/ionicons.min.css",                                              ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.47, false);
    add_style_head(\folder_theme("default")."/assets/css/slick-theme.css",                                               ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.46, false);
    add_style_head(\folder_theme("default")."/assets/css/slick.css",                                                     ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.45, false);
    add_style_head(\folder_theme("default")."/assets/css/Pe-icon-7-stroke.min.css",                                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.44, false);
    add_style_head(\folder_theme("default")."/assets/css/owl.carousel.min.css",                                          ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.41, false);
    add_style_head(\folder_theme("default")."/assets/css/bootstrap-select.min.css",                                      ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.40, false);
    add_style_head(\folder_theme("default")."/assets/css/styles.css",                                                    ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.39, false);

    # add_scripts_head(\folder_theme("default")."/assets/",              ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    /**
    * Footer
    */
    add_scripts_foot(\folder_theme("default")."/assets/js/jquery-1.12.4.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/owl.carousel.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/bootstrap.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/vit-gallery.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/jquery.countTo.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/jquery.appear.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/isotope.pkgd.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/slick.min.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/bootstrap-select.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/jquery.littlelightbox.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    #add_scripts_foot("//maps.googleapis.com/maps/api/js?key=AIzaSyBDyCxHyc8z9gMA5IlipXpt0c33Ajzqix4",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);
    add_scripts_foot(\folder_theme("default")."/assets/js/function.js",                ["type"=>"text/javascript", "charset"=>"UTF-8"], 0.50, false);

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
\register_activation_plugin('default', 'Theme_Default_activation');
