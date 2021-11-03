<?php
global $PACMEC;

$PACMEC["route"]['path']              = "default";
$PACMEC["route"]['theme']             = "default";
$PACMEC["route"]['layout']            = "none";
$PACMEC["route"]['content']           = "";


$PACMEC['session']                    = null;
$PACMEC['alerts']                     = [];


$PACMEC['dictionary']                 = [];
$PACMEC['website']                    = [
  "meta"    => [],
  "scripts" => ["head"=>[],"foot"=>[],"list" => []],
  "styles"  => ["head"=>[],"foot"=>[],"list" => []]
];
$PACMEC['themes']                     = [];
$PACMEC['gateways']                   = [
  'payments' => [],
  'shipping' => [],
];
