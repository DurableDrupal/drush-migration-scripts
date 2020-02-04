<?php
/**
 * dd-list-content-type-objects.php
 *   list content types in full object notation via print_r().
 *   see https://drupal.stackexchange.com/questions/97642/how-can-i-get-a-list-of-content-types-with-drush
 *
 * example execution:
 *
 *   drush scr scripts/dd-list-content-type-objects.php
 *   drush scr scripts/dd-list-content-type-objects.php > scripts/data/inv-content-type-objects.txt
 *
 */

$the_content_types = node_type_get_types();
print_r($the_content_types);
