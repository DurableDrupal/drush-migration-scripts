#!/usr/bin/drush

/*
print_r(block_block("list"));
$content = block_block("view", 4);
print_r($content);
*/

$custom_blocks = block_block("list");

foreach($custom_blocks as $key => $the_block) {
  print "---------------\n";
  print $key . ": " . $the_block["info"] . "\n";
  $content = block_block("view", $key);
  print $content["content"];
  print "---------------\n\n";
}
