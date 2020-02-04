#!/usr/bin/drush

define("SCS", "http://awebfactory.org:4004");

$args = drush_get_arguments();
/*
print ("arg0: " . $args[0] . " ");
print ("arg1: " . $args[1] . " ");
print ("arg2: " . $args[2] . "\n");
*/

$tokenfile = fopen("scripts/data/token.txt", "r") or die("Unable to open file!");
$token = fread($tokenfile,filesize("scripts/data/token.txt"));
fclose($tokenfile);
print ("token: " . $token);

$url = SCS . '/articles';
$method = 'GET';
$result = drupal_http_request (
  $url,
  array(
    'Content-Type' => 'application/json',
    'authorization' => 'Bearer ' . $token
  ),
  $method,
  NULL,
  1,
  30.0
);

print("\n" . "result: " . $result->code . "\n" . $result->error . "\n" . $result->data);
