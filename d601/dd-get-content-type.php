<?php
/**
 * @file
 * dd-upsert-content-type.php
 *
 * Get and possibly migrate content (by nid) for the content type specified on the command line
 *
 * Sample execution (from doc root):
 *
 *   drush scr scripts/dd-get-content-type.php taxonomy
 *   drush scr scripts/dd-get-content-type.php <content-type> <action>
 *   drush scr scripts/dd-get-content-type.php video count
 *   drush scr scripts/dd-get-content-type.php destacado migrate
 * 
 * @param "taxonomy"
 *   All taxonomy terms will be returned in JSON format, by vocabulary (collection of taxonomy terms)
 * @param $content_type
 *   A valid content type for this Drupal system.
 *
 * @param $action
 *   A valid action, one of: count, title, dump, migrate
 *
 *   count: returns number of published nodes
 *   title: returns list of titles of published nodes and summary count for the content type
 *   dump: returns JSON dump of all published nodes and summary count for the content type
 *   migrate: assuming the SCS specified here, iterates over published content items and calls a migrate function to post data according to the target schema
 * 
 * We also have two dump alternatives, examples for json and php print_r:
 *
 *   drush scr scripts/dd-get-content-type.php banner dump-php
 *   drush scr scripts/dd-get-content-type.php banner dump-json
 */

/**
 * Config
 */

/**
 * Content types map
 *
 * Map contant types on SCS to legacy content types
 *
 * If no matching legacy content type, create it thusly:
 *   "scs_content_type" => "scs_content_type"
 *   so that appropriate data marshalling function may be invoked
 */
$content_types = array(
  "banner" => "banner",
  "destacado" => "destacado",
  "destacado_home" => "destacadohome",
   "evento_agenda" => "evento_agenda",
   "evento_global" => "evento_global",
   "medicamento" => "medicamento",
   "oferta_mes" => "oferta_mes",
   "paciente" => "paciente",
   "page" => "page",
   "palabra" => "palabra",
   "producto" => "producto",
   "profesional" => "profesional",
   "story" => "story",
   "video" => "video",
   "video_noticia" => "video_noticia",
//  "poll" => "poll",
//  "clasificado" => "clasificado",
//  "agenda" => "agenda",
//  "ecard" => "ecard",
//   "newsletter_user" => "newsletter_user",
//   "noticia_newsletter" => "noticia_newsletter",
//   "profile" => "profile",
//   "reminder" => "reminder",
//   "send_ecard" => "send_ecard",
//   "simplenews" => "simplenews",
);

$args = drush_get_arguments();
$drupal_content_type = $args[2];
$scs_content_type = $content_types[$drupal_content_type];
$action = $args[3];

// print("drupal content type: " . $drupal_content_type . "\n");
// print("scs content type: " . $scs_content_type . "\n");
// print("action: " . $action . "\n");

if ($scs_content_type) {
  switch ($action) {
    case 'count':
      print(count_nodes($drupal_content_type) . "\n");
      break;
    case 'title':
      print(list_node_titles($drupal_content_type) . "\n");
      break;
    case 'dump-php':
      print(dump_nodes($drupal_content_type, 'php') . "\n");
      break;
    case 'dump-json':
      print(dump_nodes($drupal_content_type, 'json') . "\n");
      break;
    case 'migrate':
      print(migrate_nodes($drupal_content_type, $scs_content_type) . "\n");
      break;
    default:
      print('unkown action: ' . $action . "\n");
  }
} elseif ($drupal_content_type == 'taxonomy') {
  if ($action == 'dump') {
    print(dump_tids() . "\n");
  } elseif ($action == 'migrate') {
    print(migrate_tids() . "\n");
  } else {
    print('unkown action: ' . $action . "\n");
  }
} elseif ($drupal_content_type == 'vocabulary') {
  if ($action == 'dump') {
    print(dump_vocabs() . "\n");
  } elseif ($action == 'migrate') {
    print(migrate_vocabs() . "\n");
  } else {
    print('unkown action: ' . $action . "\n");
  }
} else {
  print('unkown content type: ' . $drupal_content_type . "\n");
}

function count_nodes($drupal_content_type) {
  // $result = db_query('SELECT nid FROM node WHERE type = "%s" AND status = 1', $drupal_content_type);
  $result = db_query('SELECT nid FROM node WHERE type = "%s"', $drupal_content_type);
  $num = 0;
  while ($obj = db_fetch_object($result)) {
    $num++;
  }
  return($drupal_content_type . ': ' . $num . " items");
}

function list_node_titles($drupal_content_type) {
  // $result = db_query('SELECT title, nid FROM node WHERE type = "%s" AND status = 1', $drupal_content_type);
  $result = db_query('SELECT title, nid FROM node WHERE type = "%s"', $drupal_content_type);
  $num = 0;
  while ($obj = db_fetch_object($result)) {
    $num++;
    print($num . ': ' . $obj->title . ' nid: [' . $obj->nid . "]\n");
  }
  return("\nListed " . $num . " titles for content type " . $drupal_content_type . "\n");
}

function dump_nodes($drupal_content_type, $format = 'php') {
  // $result = db_query('SELECT nid FROM node WHERE type = "%s" AND status = 1', $drupal_content_type);
  $result = db_query('SELECT nid FROM node WHERE type = "%s"', $drupal_content_type);
  $num = 0;
  while ($obj = db_fetch_object($result)) {
    $num++;
    $node = node_load($obj->nid);
    // if web app use function drupal_json() which sets headers, then invokes drupal_to_js()
    // print($num . "\n");
    if ($format == 'json') {
      print(drupal_to_js($node));
    } else {
      print_r($node, false);
    }
    print("\n");
  }
  // return("\nListed " . $num . " objects for content type " . $drupal_content_type . "\n");
}

function migrate_nodes($drupal_content_type, $scs_content_type) {
  // $dbresult = db_query('SELECT nid FROM node WHERE type = "%s" AND status = 1', $drupal_content_type);
  $dbresult = db_query('SELECT nid FROM node WHERE type = "%s"', $drupal_content_type);
  $num = 0;
  while ($obj = db_fetch_object($dbresult)) {
    $num++;
    $node = node_load($obj->nid);
    $json = drupal_to_js($node);
    $url = variable_get('backend_url', null) . '/' . $scs_content_type;
    $metodo = 'POST';
    $reqresult = drupal_http_request ( 
      $url, 
      array ( 'Content-Type' => 'application/json' ),
      $metodo,
      $json,
      1,
      30.0
    );
									        
    print("\n" . "reqresult: " . print_r($reqresult->data, FALSE) . "\n");
    print("\n" . "reqresult: " . print_r($reqresult->error, FALSE) . "\n");
  }
  return("\nMigrated " . $num . " items for content type " . $drupal_content_type . " to " . $scs_content_type .  "\n");
}

function dump_tids() {
  $num = 0;
  $vocabularies = taxonomy_get_vocabularies();
  foreach($vocabularies as $key => $vocabulary) {
    $num++;
    // print(drupal_to_js($vocabulary));
    // print("\n");
    print(drupal_to_js(taxonomy_get_tree($key)));
    print("\n");
  }
  return("\nListed " . $num . "  vocabularies \n");
}

function migrate_tids() {
  $num = 0;
  $vocabularies = taxonomy_get_vocabularies();
  foreach($vocabularies as $key => $vocabulary) {
    $taxonomies = taxonomy_get_tree($key);
    foreach($taxonomies as $tkey => $taxonomy) {
      $num++;
      $json = drupal_to_js($taxonomy);
/* D6
 * drupal_http_request($url, $headers = array(), $method = 'GET', $data = NULL, $retry = 3, $timeout = 30.0)
 */
      $url = variable_get('backend_url', null) . '/taxonomy';
      $metodo = 'POST';
      $result = drupal_http_request ( 
        $url, 
        array ( 'Content-Type' => 'application/json' ),
        $metodo,
        $json,
        1,
        30.0
      );
    
      print("\n" . "result: " . print_r($result->data, FALSE) . "\n");
      print("\n" . "result: " . print_r($result->error, FALSE) . "\n");
    }
  }
  return("\nMigrated " . $num . " taxonomies \n");
}

function dump_vocabs() {
  $num = 0;
  $vocabularies = taxonomy_get_vocabularies();
  foreach($vocabularies as $key => $vocabulary) {
    $num++;
    print(drupal_to_js($vocabulary));
    print("\n");
    // print(drupal_to_js(taxonomy_get_tree($key)));
    // print("\n");
  }
  return("\nListed " . $num . "  vocabularies \n");
}

function migrate_vocabs() {
  $num = 0;
  $vocabularies = taxonomy_get_vocabularies();
  foreach($vocabularies as $key => $vocabulary) {
    $num++;
    $json = drupal_to_js($vocabulary);

/* D7
 *
 * 7.x common.inc    drupal_http_request($url, array $options = array())
 *
    // set up url
    $url = variable_get('backend_url', null) . '/vocabulary';
    $metodo = 'POST';
    // set up options and send request to JSON api on SCS
    $options = array (
      'method' => $metodo,
      'headers' => array (
        'Content-Type' => 'application/json',
      ),
      'data' => $json,
    );
    // perform post
    $result = drupal_http_request ( $url, $options );
*/

/* D6
 *
 * drupal_http_request($url, $headers = array(), $method = 'GET', $data = NULL, $retry = 3, $timeout = 30.0)
 *
 */
    $url = variable_get('backend_url', null) . '/vocabulary';
    $metodo = 'POST';
    $result = drupal_http_request ( 
      $url, 
      array ( 'Content-Type' => 'application/json' ),
      $metodo,
      $json,
      1,
      30.0
    );
    
    print("\n" . "result: " . $result . "\n");
  }
  return("\nMigrated " . $num . "  vocabularies \n");
}

