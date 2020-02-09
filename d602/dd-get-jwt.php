#!/usr/bin/drush

$tokenfile = fopen("scripts/data/token.txt", "r") or die("Unable to open file!");
$token = fread($tokenfile,filesize("scripts/data/token.txt"));
fclose($tokenfile);
print ("token: " . $token);

$url = variable_get('backend_url', null) . '/articles';
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
