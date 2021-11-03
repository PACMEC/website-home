<?php
/**
 *
 * @package    PACMEC
 * @category   Alerts
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
namespace PACMEC\System
{
  Class Alerts
  {
    public $type = "error";
    public $plugin = "undefined";
    public $message = "Message no defined";
    public $actions = [];

    public function __construct($values=[])
    {
      foreach ($values as $key => $value) {
        if(isset($this->{$key})) $this->{$key} = $value;
      }
    }

    public function __sleep()
    {
        return ['type', 'plugin', 'message', 'actions'];
    }

  	public function addAction($name="",$slug="",$text=""){
  		$this->actions[] = [
        "name" => $name,
        "slug" => $slug,
        "text" => $text,
      ];
  	}

  	public function getActions()
    {
  		return $this->actions;
  	}

    public function upAlert()
    {
      $GLOBALS['PACMEC']['alerts'][] = [
        "type"     => $this->type,
        "plugin"   => $this->plugin,
        "message"  => $this->message,
        "actions"  => $this->actions
      ];
    }

    static public function addAlert($values=[])
    {
      if(is_object($values)) $values = (array) $values;
      if(!is_array($values)) return false;
      if(isset($values['actions']) && !is_array($values['actions'])) return false;
      if(!isset($values['type'])) return false;
      if(!isset($values['message'])) return false;
      if(!isset($values['plugin'])) return false;
      $a = [];
      if(isset($values['type'])) $a['type'] = $values['type'];
      if(isset($values['plugin'])) $a['plugin'] = $values['plugin'];
      if(isset($values['message'])) $a['message'] = $values['message'];
      if(isset($values['actions'])) $a['actions'] = $values['actions'];
      $GLOBALS['PACMEC']['alerts'][] = $a;
      return in_array($a, $GLOBALS['PACMEC']['alerts']);
    }
  }
};
