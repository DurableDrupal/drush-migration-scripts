<?php
/**
 * dd-list-content-types.php
 *   list content types.
 *   see https://drupal.stackexchange.com/questions/97642/how-can-i-get-a-list-of-content-types-with-drush
 *
 * example execution:
 *
 *   drush scr scrips/dd-list-content-types.php
 *   drush scr scrips/dd-list-content-types.php > scrips/data/inv-content-types.txt
 *
 */

print("Machine name | Name  (Descrioption)\n");
print("----------------------------------\n");
$the_content_types = node_type_get_types();
//print_r($the_content_types);
foreach($the_content_types as $key => $value) {
  print($key . ': ' . $value->name . ' (' . $value->description . ")\n");
}


// simple machine name list
//$content_types = array_keys(node_type_get_types());
//sort($content_types);
//drush_print(dt("Machine name"));
//drush_print(implode("\r\n", $content_types));


