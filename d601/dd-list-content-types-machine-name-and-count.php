<?php
$the_content_types = node_get_types();
foreach($the_content_types as $key => $value) {
  print($key);
  $node_type = $key;
  $num = 0;
  $result = db_query('SELECT nid FROM node WHERE type = "%s" ', $node_type);
  while ($obj = db_fetch_object($result)) {
    $num++;
  }
  print(': ' . $num . "\n");
}

