<?php
/**
 * dd-list-unique-content-attributes.php
 *
 * Export content for all content types to structured content server
 *
 * Sample execution:
 *
 *   drush scr scripts/dd-list-unique-content-attributes.php
 *   drush scr scripts/dd-list-unique-content-attributes.php > scripts/data/inv-unique-content-attributes.txt
 */

$field_types = array();
$fields_by_type = array();
$field_map = field_info_field_map();

foreach ($field_map as $field_name => $field_data) {
  $type = $field_data['type'];
  $field_types[$type] = $type;
  $fields_by_type[$type][] = $field_name;
}

print 'field types';
print_r($field_types);
print 'fields by type';
print_r($fields_by_type);
print 'complete field map';
print_r($field_map);

/**
 * Helper function to return all fields of one type on one bundle.
 *     https://api.drupal.org/comment/60954#comment-60954
 */
/*
function fields_by_type_by_bundle($entity_type, $bundle, $type) {
	$chosen_fields = array();
	$fields = field_info_field_map();
	foreach ($fields as $field => $info) {
		if ($info['type'] == $type &&
				in_array($entity_type, array_keys($info['bundles'])) &&
				in_array($bundle, $info['bundles'][$entity_type]))
		{
			$chosen_fields[$field] = $field;
		}
	}
	return $chosen_fields;
}
*/
