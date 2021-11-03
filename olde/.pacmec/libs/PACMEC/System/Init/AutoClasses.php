<?php
  /**
   * @package    PACMEC
   * @category   AutoClasses
   * @copyright  2021 FelipheGomez
   * @author     FelipheGomez <info@pacmec.co>
   * @license    license.txt
   * @version    1.0.0
   */
namespace PACMEC
{
  class AutoClasses
  {
    public function __construct()
    {
     if(!defined('PACMEC_PATH')) exit("PACMEC_PATH NO DEFINE");
     if(!defined('HOME_PATH')) exit("HOME_PATH NO DEFINE");
    }

    public function exec($class)
    {
     try {
       global $PACMEC;
       if (\class_exists($class)) return;
       #$class_r   = basename(str_replace("PACMEC","", str_replace(["\\", "\\\\"], "/", $class)));
       #$namespace = str_replace(["\\","//","\\\\","/"],"/", __NAMESPACE__);
       #$ruta_a    = PACMEC_PATH . "/" . (empty($namespace) ? "" : $namespace . "/") . "{$class_r}.php";

        $class_r   = str_replace("\\","/", $class);
        $class_r   = str_replace("PACMEC","", $class_r);
        $namespace = str_replace("\\","/", __NAMESPACE__);
        $ruta_a    = PACMEC_PATH . "/{$class_r}.php";
        $ruta_b    = PACMEC_PATH . "/{$class_r}.php";
        $ruta_c    = PACMEC_PATH . "/libs/" . "{$class_r}.php";
        $ruta_d    = PACMEC_PATH . "/libs/" . (empty($namespace) ? "" : $namespace . "/") . "{$class_r}.php";
        $file      = null;

        if (\is_file($ruta_a) && \file_exists($ruta_a)){
          $file = ($ruta_a);
        } elseif (\is_file($ruta_b) && \file_exists($ruta_b)){
          $file = ($ruta_b);
        } elseif (\is_file($ruta_c) && \file_exists($ruta_c)){
          $file = ($ruta_c);
        } elseif (\is_file($ruta_d) && \file_exists($ruta_d)){
          $file = ($ruta_d);
        } else {
            throw new \Exception("Archivo no encontrado. {$class}... ");
        }
        require_once $file;
        if (!\class_exists($class) && !\interface_exists($class)) {
          throw new \Exception("Class no encontrada. {$class}... ", 1);
        } else {
          $PACMEC['autoload']['classes'][$class] = $file;
        }
     } catch (\Exception $e) {
       return $e;
       #echo "Classe: {$class}\n<br>";
       #echo "class         : " . $class;
       #echo "\n<br>";
       #echo "__NAMESPACE__ : " . __NAMESPACE__;
       #echo "\n<br>";
       #echo "class r       : " . $class_r;
       #echo "\n<br>";
       #echo "namespace     : " . $namespace;
       #echo "\n<br>";
       #echo "ruta_a        : " . $ruta_a;
       #echo "\n<br>";
       #echo "ruta_b        : " . $ruta_b;
       #echo "\n<br>";
       #echo "ruta_c        : " . $ruta_c;
       #echo "\n<br>";
       #echo "ruta_d        : " . $ruta_d;
       #echo "\n<br>";
       echo "<code style=\"background:#CCC;padding:5px;\">";
         echo "<b>PACMEC-ERROR </b>:";
         echo " Classe: {$class} | ";
         echo ("{$e->getMessage()}\n<br>");
       echo "</code>";
       exit();
     }
    }
  }
};
