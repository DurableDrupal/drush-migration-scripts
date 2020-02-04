#!/usr/bin/drush
/**
 * .scripts/dd-list-content-types-and-item-count.php > scripts/data/inv-content-types-with-item-count.txt
 */
$the_content_types = node_get_types();
foreach($the_content_types as $key => $value) {
  print($key);
  $node_type = $key;
  $num = 0;
  $num_pub = 0;
  $result = db_query('SELECT nid, title, status FROM node WHERE type = "%s" ', $node_type);
  while ($obj = db_fetch_object($result)) {
// print_r($obj);
    $num++;
    if($obj->status > 0) {$num_pub++;}
  }
  print(': ' . $num_pub . ' of ' . $num . " published items\n");
}

