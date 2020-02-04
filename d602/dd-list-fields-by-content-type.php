#!/usr/bin/drush
/**
 * dd-list-fields-by-content-type.php > data/inv-content-type-fields-info.txt
 *
 */
$args = drush_get_arguments();

$types=node_get_types();

foreach($types as $type) {
  // get published node content item 
  $result = db_query('SELECT nid FROM node WHERE type = "%s" and status = 1', $type->type);
  // fetch a single result
  $nid = array_shift(db_fetch_array($result));
  $the_node = node_load($nid);
  print $type->type . ": " . $type->name . "\n";
  print "-------------------------\n";
  foreach($the_node as $field_name => $field_value) {
    print $field_name . ": " . gettype($field_value) . "\n";
  }
  // print_r($the_node);
  // $field_names = array_keys((array)$the_node);
  print "-------------------------\n";
  print "-------------------------\n\n";
}

/* 
$types=node_get_types();

foreach($types as $type) {
  print "-------------------------\n";
  print $type->type . ": " . $type->name . "\n";
  print "-------------------------\n";
  print_r(content_types($type->type));
  print "-------------------------\n";
  print "-------------------------\n\n";
}

print_r(content_fields());

*/

/****** research:

print "-------------------------\n";
print "From _content_type_info()\n";
print "-------------------------\n";
$type_info = _content_type_info();
print_r($type_info);
print "-------------------------\n";
print "-------------------------\nn";

print "-------------------------\n";
print "From _content_type_info()\n";
print "-------------------------\n";
$type_info = _content_type_info();
print_r($type_info);
print "-------------------------\n";
print "-------------------------\nn";

print "-------------------------\n";
print "From _node_types_build()\n";
print "-------------------------\n";
$the_types = _node_types_build();
print_r($the_types);
print "-------------------------\n";
print "-------------------------\nn";

print "-------------------------\n";
print "From node_get_types()\n";
print "-------------------------\n";
$types=node_get_types();
print_r($types);
print "-------------------------\n";
print "-------------------------\nn";

*/

/*
 * For Drupal 7, check out the field_info_instances function to retrieve a list of fields for a particular node content type.
 * Here is an example usage that will retrieve all the fields for a node content type.
 * $my_content_type_fields = field_info_instances("node", "my_node_content_type");

 * content_fields() is now (Drupal 7) field_info_fields();
 */

