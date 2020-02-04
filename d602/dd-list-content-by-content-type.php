#!/usr/bin/drush

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
 * Sample execution from document root:
 *
 *   ./scripts/dd-list-content-by-content-type.php
 *   ./scripts/dd-list-content-by-content-type.php > scripts/data/inv-content-by-conetent-type.txt
 *
 * Sample execution from document root (full, with node_load):
 *
 *   ./scripts/dd-list-content-by-content-type.php full
 *   ./scripts/dd-list-content-by-content-type.php full > scripts/data/inv-content-by-conetent-type-full.txt
 */
$args = drush_get_arguments();
$full = $args[2];

print("Machine name | Name  (Description)\n");
print("----------------------------------\n");
// d7: $the_content_types = node_type_get_types();
$the_content_types = node_get_types();
//print_r($the_content_types);
foreach($the_content_types as $key => $value) {
  $node_type = $key;
  print($key . ': ' . $value->name . ' (' . $value->description . ")\n");
  print("----------------------------------\n");
  /* d6
   */
  $result = db_query('SELECT nid FROM node WHERE type = "%s"', $node_type);
  $nids = array();
  
  $ncount = 0;
  while ($obj = db_fetch_object($result)) {
    $ncount++;
    $nids[] = $obj->nid;
    $node = node_load($obj->nid);
    
    print('nid ' . $node->nid . ' [status ' . $node->status . ']: ' . $node->title . "\n");
    ($full) ? print_r($node) : NULL;
  }
  print('Count: ' . '[' . $node_type . ': ' . $ncount . ']' . "\n");
  
/* d7
  $result = db_query("SELECT nid FROM node WHERE type = :nodeType ", array(':nodeType'=>$node_type));
  $nids = array();
  foreach ($result as $obj) {
    $nids[] = $obj->nid;
    //print_r($obj);
    $node = node_load($obj->nid);
    print('nid ' . $node->nid . ': ' . $node->title . "\n");
  }
*/
  print("----------------------------------\n");
}

