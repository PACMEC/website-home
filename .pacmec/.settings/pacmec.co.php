<?php
/**
 * @package    PACMEC
 * @category   AutoSettings
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_port', '3306');                                   // Base de datos: Puerto de conexion (Def: 3306)
define('DB_driver', 'mysql');                                // Base de datos: Controlador de la conexion (Def: mysql)
define('DB_host', 'localhost');                              // Base de datos: Servidor/Host de conexion (Def: localhost)
define('DB_user', 'admin_upacmec');                           // Base de datos: Usuario de conexion
define('DB_pass', 'zLHcZh0lHBKHGBnVdX6fZluUssg3wl');         // Base de datos: Contraseña del usuario
define('DB_database', 'dev_pacmec');                       // Base de datos: Nombre de la base de datos
define('DB_charset', 'utf8');                                // Base de datos: Caracteres def
define('DB_prefix', 'fg_');                                  // Base de datos: Prefijo de las tablas (Opcional)
