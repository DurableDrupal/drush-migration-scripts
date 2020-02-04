#!/usr/bin/drush
/**
 * dd-list-taxonomy-info.php > data/inv-taxonomy-info-by-vocabulary.txt
 *
 */
$args = drush_get_arguments();

// get list of vocabularies

$vocabs = taxonomy_get_vocabularies();

foreach($vocabs as $key => $vocab) {
  // print_r($vocab);
  print "-------------------------\n";
  print $vocab->name . " (vid: " . $vocab->vid . ")\n";
  print "-------------------------\n";
  print "For content types: \n";
  print_r($vocab->nodes);
  print "-------------------------\n";
  $terms = taxonomy_get_tree($vocab->vid);
  print count($terms) . " terms for " . $vocab->name . " (vid: " . $vocab->vid . ")\n";
  print_r($terms);
  print "-------------------------\n";
  print "-------------------------\n\n";
}

// for each vocabulary, list each term, with total count

