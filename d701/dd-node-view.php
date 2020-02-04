<?php
/**
 * dd-node-view.php
 * 
 * drush scr scripts/dd-node-view 68
 */

$args = drush_get_arguments();
$nid = $args[2];
$node = node_load($nid);
print_r($node);
