<?php
/**
 *
 * @package    PACMEC
 * @category   Menu
 * @copyright  2020-2021 FelipheGomez
 * @author     FelipheGomez <feliphegomez@pm.me>
 * @license    license.txt
 * @version    1.0.1
 */

namespace PACMEC\System;

class Menu extends BaseRecords {
  const TABLE_NAME = 'menus';
  const COLUMNS_AUTO_T  = [
  ];
	private $prefix = null;
	private $db = null;
	public $id;
	public $name;
	public $slug;
  function isHome()
  {
  	return ($GLOBALS['PACMEC']['path'] == siteinfo('homeurl'));
  }
	public $permission_access;
	public $items = [];

	public function __construct($args=[]){
		$args = (array) $args;
		parent::__construct("menus", true);
		if(isset($args['by_id'])) $this->getBy('id', $args['by_id']);
		if(isset($args['by_slug'])) $this->getBy('slug', $args['by_slug']);
	}

	public function getBy($column='id', $val=""){
		try {
      $sql = "SELECT * FROM `{$this->get_table()}` WHERE `{$column}`=? AND (`site` IN (?) OR `site` IS NULL) ORDER BY `site` DESC LIMIT 1";
			return $this->setAll($GLOBALS['PACMEC']['DB']->FetchObject($sql, [$val, $GLOBALS['PACMEC']['site']->id]));
		}
		catch(Exception $e){
			return $this;
		}
	}

	public function setAll($arg=[]){
		$arg = (array) $arg;
		foreach($arg as $k=>$v){
			if(isset($this->{$k})){
				switch ($k) {
					default:
						$this->{$k} = $v;
						break;
				}
			}
		}
		if($this->isValid()){
			$this->name = __at($this->name);
			$this->items = $this->loadItemsMenu($this->id);
		}
		return $this;
	}

	public static function validatePermission($item) : bool
	{
		if($item->permission_access !== null && !empty($item->permission_access)){
			return \validate_permission($item->permission_access);
		}
    if($item->guests == 1 && \isUser()) return false;
    if($item->users == 1 && !\isUser()) return false;
		return true;
	}

	private function loadItemsMenu($id=0, $parent = 0)
  {
    global $PACMEC;
		$r = [];
		foreach($GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM {$GLOBALS['PACMEC']['DB']->getPrefix()}menus_elements WHERE `menu`=? AND `index_id`=? ORDER BY `ordering`", [$id,$parent]) as $item){
			if(Self::validatePermission($item)){
        $item->title       = \__at($item->title);
        $item->extra_txt   = \__at($item->extra_txt);
        $item->tag_href = \PACMEC\System\Route::encodeURIautoT($item->tag_href);
				$childs = $this->loadItemsMenu($id, $item->id);
				$item->childs = [];
				if($childs !== false){
					foreach ($childs as $index => $child) {
						$child->tag_href = Route::encodeURIautoT($child->tag_href);
					}
					$item->childs = $childs;
				}
				$r[] = $item;
			}
		}
		return $r;
	}

	public function allLoad() : array {
		$r = [];
		foreach($GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$this->get_table()}` ", []) as $menu){
			$r[] = new Self($menu);
		}
		return $r;
	}
}
