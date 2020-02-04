<?php
/**
 * dd-list-content-by-content-type.php
 *   list content types.
 *   see https://drupal.stackexchange.com/questions/97642/how-can-i-get-a-list-of-content-types-with-drush
 *
 * While this functionality is more readily available via
 *   drush views-data-export content_audit views_data_export_1 data/inv_content_audit.csv
 *   we still want to implement it as a way of preparing to upsert to SCS
 *   iinstead of merely listing data
 *
 * Sample execution:
 *
 *   drush scr scripts/dd-list-content-by-content-type.php
 *   drush scr scripts/dd-list-content-by-content-type.php > scrips/data/inv-content-by-conetent-type.txt
 *
 */

print("Machine name | Name  (Descrioption)\n");
print("----------------------------------\n");
$the_content_types = node_type_get_types();
//print_r($the_content_types);
foreach($the_content_types as $key => $value) {
  print($key . ': ' . $value->name . ' (' . $value->description . ")\n");
  print("----------------------------------\n");
  $node_type = $key;
  $result = db_query("SELECT nid FROM node WHERE type = :nodeType ", array(':nodeType'=>$node_type));
  $nids = array();
  foreach ($result as $obj) {
    $nids[] = $obj->nid;
    //print_r($obj);
    $node = node_load($obj->nid);
    print('nid ' . $node->nid . ': ' . $node->title . "\n");
  }
  print("----------------------------------\n");
}

