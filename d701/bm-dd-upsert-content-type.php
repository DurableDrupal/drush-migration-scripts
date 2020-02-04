<?php
/**
 * dd-upsert-content-type.php
 *
 * Upsert content (by nid) for the content type specified on the command line,
 *   or all content types, to the structured content server
 *
 * Sample execution:
 *
 *   drush scr scripts/dd-upsert-content-type.php artist_page
 *   drush scr scripts/dd-upsert-content-type.php __all__
 *
 * Given a scs content type
 *   Config
 *     SCS
 *     get all fields maps:
 *       given a field name index, give Drupal field type
 *         using array made up of:
 *           core node fields (type, status, changed...)
 *           non-core field map fields
 *   get all nodes for the Drupal content type
 *   foreach node
 *     populate class (array)
 *       given Drupal field type, call function to get marshalled SCS field
 *     upsert
 *
 *
 * Thinking out loud:
 *
 * While we are interested only in semantic data,
 *   one of the problems is the pathological mix of modes
 *   in Drupal content type field types:
 *
 * - bona fide content attributes
 * - editing mode: drop down list `list_text` field types
 * - view mode: viewfield to show associated content
 *
 */

/**
 * Config
 */
// servidor de contenidos
define("SCS", "http://awebfactory.org:4021");

// map contant types on SCS to legacy content types
  // if no matching legacy content type, create it thusly:
  // "scs_content_type" => "scs_content_type"
  // so that appropriate data marshalling function is invoked
$content_types = array(
//  "simpleads" => "ads",
//  "simpleads_campaign" => "campaign",
  "artist_page" => "artist",
  "banner_image_" => "asset",
//  "digital_license_product" => "song",
//  "digital_license_product_album" => "album",
//  "email_for_download" => "registration-campaign-sample",
//  "event" => "booking",
//  "front_page_bottom_big_links" => "link-blocks",
//  "product_item" => "merchandise-product-item",
//  "promoted_store_content" => "promoted-store-content",
//  "related_album_ads_" => "album-ads",
  "video" => "video",
//  "__all__" => "all"
);

$args = drush_get_arguments();
$drupal_content_type = $args[2];
$scs_content_type = $content_types[$drupal_content_type];

switch ($scs_content_type) {
  // invoked by param __all__
  case 'all':
    print "all content types targeted on structured content server\n";
    break;
  // no such server side content type supported
  case '' :
    print "no such content type, exiting\n";
    break;
  // supported server side content type
  default:
    print "legacy content type: " . $drupal_content_type . ' targeting ' . $scs_content_type . " on Structured Content Server\n";
    $result = db_query("SELECT nid FROM node WHERE type = :nodeType ", array(':nodeType'=>$drupal_content_type));
    foreach ($result as $obj) {
      upsert_node($obj->nid, $scs_content_type);
    }
}

function upsert_node($nid, $scs_content_type) {
//  $node = node_load($nid);

//print "Node: " . $node->title . "\n";


  // marshall info
  switch($scs_content_type) {
    case 'artist':
      $n = node_load($nid);
      if ($n) upsert_artist($n);
      break;
    case 'video':
      $n = node_load($nid);
      if ($n) upsert_video($n);
      break;
    case 'asset':
      $n = node_load($nid);
      if ($n) upsert_asset($n);
      break;
    default:
      break;
  }
}

function _upsert_action() {
}

function upsert_artist($n) {
// print_r($n);
  // populate payload
  $artist = array (
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    artistBio => __get($n, 'body'),
    artistBanner => __get($n, 'field_artist_banner_image'),
    artistLogo => __get($n, 'field_artist_logo_image'),
    artistProfileMedia => __get($n, 'field_artist_profile_image'),
    artistGallery => __get($n, 'field_artist_gallery'),
    artistFacebook => __get($n, 'field_facebook'),
    artistTwitter => __get($n, 'field_twitter'),
    artistWebsite => __get($n, 'field_artist_website'),
    artistWebsiteMedia => __get($n, 'field_artist_website_image'),
    artistYouTube => __get($n, 'field_youtube'),
    artistMySpace => __get($n, 'field_my_space'),
    artistMusicLink => __get($n, 'field_music_link'),
    artistMusicLinkMedia => __get($n, 'field_music_link_image')
  );
// print "artist: \n";
// print_r($artist);

  // encode as JSON
  $json = json_encode($artist);
  // print $json;

  // set up url
  $url = SCS . '/api/artists';
  $metodo = 'PUT';
  // set up options and send request to JSON api on SCS
  $options = array (
    'method' => $metodo,
    'headers' => array (
        'Content-Type' => 'application/json',
    ),
    'data' => $json,
  );

  // perform upsert
  $result = drupal_http_request ( $url, $options );

  // inform of success or failure
  if ($result->code != 200) {
    print ($result->status_message);
  }
}

function upsert_video($n) {
// print_r($n);
  // populate payload
  $video = array (
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    video => __get($n, 'field_video'),
    videoImage => __get($n, 'field_video_image'),
    aboutThisVideo => __get($n, 'body'),
    videoAdvertCheck => __get($n, 'field_video_advert_check'),
    isFrontPage => __get($n, 'field_front_page'),
    tags => __get($n, 'field_tags'),
    musicGenre => __get($n, 'field_music_genre'),
    relatedAlbum => __get($n, 'field_related_album'),
    videoArtist => __get($n, 'field_video_artist'),
  );
// print "video: \n";
// print_r($video);

  // encode as JSON
  $json = json_encode($video);
  //print $json;

  // set up url
  $url = SCS . '/api/videos';
  $metodo = 'PUT';
  // set up options and send request to JSON api on SCS
  $options = array (
    'method' => $metodo,
    'headers' => array (
        'Content-Type' => 'application/json',
    ),
    'data' => $json,
  );

  // perform upsert
  $result = drupal_http_request ( $url, $options );

  // inform of success or failure
  if ($result->code != 200) {
    print ($result->status_message);
  }
}

function upsert_asset($n) {
// print_r($n);
  // populate payload
  $asset = array (
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    assetMedia => __get($n, 'field_banner_image'),
  );

  // encode as JSON
  $json = json_encode($asset);

  // set up url
  $url = SCS . '/api/assets';
  $metodo = 'PUT';
  // set up options and send request to JSON api on SCS
  $options = array (
    'method' => $metodo,
    'headers' => array (
        'Content-Type' => 'application/json',
    ),
    'data' => $json,
  );

  // perform upsert
  $result = drupal_http_request ( $url, $options );

  // inform of success or failure
  if ($result->code != 200) {
    print ($result->status_message);
  }
}

function __get($n, $field) {
  // grab map (array indexable by field name) 
  //   from core function field_info_field_map <- FieldInfo::getFieldMap (not available in Drupal 6) 
  //   to obtain field type from $field_map[field_name]['type']
  $upsert_field_map = field_info_field_map();

  switch ($field) {
    case 'nid':
    case 'vid':
    case 'type':
    case 'language':
    case 'title':
    case 'uid':
    case 'name':
    case 'created':
    case 'changed':
      return $n->{$field};
      break;
    case 'status':
      return ($n->status) ? TRUE:FALSE;
      break;
    case 'disabled':
      return ($n->status) ? FALSE:TRUE;
      break;
    case 'meta_data':
      return __get_meta_data($n);
    default:
      // get field type from field map info
      $type = $upsert_field_map[$field]['type'];

// print "field: " . $field . " field type: " . $type . "\n";

      // return result from function __get_{$n, $field}
      if ($type) {
        if ($type == 'file' || $type == 'image') return __get_media($n, $field);
        $function = '__get_' . $type;
        return $function($n, $field);
      }
      else {
        return '';
      }
      break;
  }
}

function __get_meta_data ($n) {
  $m = array(
    itemSlug => _slugify($n->title),
// TODO    itemSlugLegacy => _getAlias($n),
    itemName => __get($n, 'title'),
    language => __get($n, 'language'),
    published => __get($n, 'status'),
    publishedDate => __get($n, 'changed'),
    disabled => __get($n, 'disabled'),
    createdDate => __get($n, 'created'),
    modifiedDate => __get($n, 'changed'),
    revisionId => __get($n, 'vid'),
    // workflow state n/a in content model, no workflow module used
    // workflowState => __get($n, 'workflow_status') 
  );
  return $m;
}

function __get_text ($n, $field) {
}

function __get_text_long ($n, $field) {

}

function __get_text_with_summary ($n, $field) {
  $b = array(
      "summary" => array("label" => "summary", "value" => ((isset($n->{$field}['und'][0]['safe_summary'])) ?
      $n->{$field}['und'][0]['safe_summary'] : ''), "help" => ""),
      "body" => array("label" => "body", "value" => ((isset($n->{$field}['und'][0]['safe_value'])) ?
      $n->{$field}['und'][0]['safe_value'] : ''), "help" => ""),
  );
  return $b;
}

function __get_taxonomy_term_reference ($n, $field) {
  $tags = array();
  if (isset($n->{$field}['und'][0])) {
    $the_tags = $n->{$field}['und'];
    foreach ($the_tags as $a_tag) {
      $tid = $a_tag['tid'];
      $term = taxonomy_term_load($tid);
      $name = $term->name;
      $slug = _slugify($name);
      $tag = array(
        "idLegacy" => $tid,
        "tagSlug" => $slug,
        "tagName" => $name
      );
      array_push($tags, $tag);
    }
  }
  return (count($tags) > 1) ? $tags : $tags[0];
}

function __get_media ($n, $field) {
  if (isset($n->{$field}['und'][0])) {
    $medias = array();
    for ($i = 0; $i < count($n->{$field}['und']); $i++) {
      $media = array(
        "idLegacy" => $n->nid,
        "mediaLink" => array(
          "idLegacy" => (isset($n->{$field}['und'][$i]['fid'])) ?
          $n->{$field}['und'][$i]['fid'] : '',
          "uriLegacy" => (isset($n->{$field}['und'][$i]['uri'])) ?
          $n->{$field}['und'][$i]['uri'] : '',
          "linkUrl" => "",
          "linkFilePath" => (isset($n->{$field}['und'][$i]['filename'])) ?
          $n->{$field}['und'][$i]['filename'] : '',
          // This is for stuff like name, title attributes
          "linkAttributes" => array(
            array(
              "attrName" => "title",
              "attrValue" => (isset($n->{$field}['und'][$i]['title'])) ?
                $n->{$field}['und'][$i]['title'] : ''
            ),
            array(
              "attrName" => "alt",
              "attrValue" => (isset($n->{$field}['und'][$i]['alt'])) ?
                $n->{$field}['und'][$i]['alt'] : ''
            ),
          ),
        ),
        // not supported in legacy image content type
        "mediaCaption" => "",
        // not supported in legacy image content type
        "mediaCredits" => "",
        "mediaWidth" => (isset($n->{$field}['und'][$i]['width'])) ?
          $n->{$field}['und'][$i]['width'] : '',
        "mediaHeight" => (isset($n->{$field}['und'][$i]['height'])) ?
          $n->{$field}['und'][$i]['height'] : '',
        "mediaMime" => (isset($n->{$field}['und'][$i]['filemime'])) ?
          $n->{$field}['und'][$i]['filemime'] : '',
        "mediaSize" => (isset($n->{$field}['und'][$i]['filesize'])) ?
          $n->{$field}['und'][$i]['filesize'] : '',
        "mediaType" => (isset($n->{$field}['und'][$i]['type'])) ?
          $n->{$field}['und'][$i]['type'] : '',
      );
      array_push($medias, $media);
    }
  return (count($medias) > 1) ? $medias : $medias[0];
  }
}


/*
 * covered by media
function __get_file ($n, $field) {

}
 */

function __get_list_text ($n, $field) {
  if (isset($n->{field}['und'][0]['value'])) {
    return ($n->{field}['und'][0]['value']) ? TRUE :FALSE;
  }
  else {
    return FALSE;
  }
}

function __get_list_boolean ($n, $field) {
  if (isset($n->{field}['und'][0]['value'])) {
    return ($n->{field}['und'][0]['value']) ? TRUE :FALSE;
  }
  else {
    return FALSE;
  }
}

function __get_list_integer ($n, $field) {

}

function __get_link_field ($n, $field) {
  $l = array(
      "idLegacy" => $n->nid,
      "linkUrl" => (isset($n->{$field}['und'][0]['url'])) ?
      $n->{$field}['und'][0]['url'] : '',
      "linkAttributes" => array(
        array(
          "attrName" => "title",
          "attrValue" => (isset($n->{$field}['und'][0]['title'])) ?
            $n->{$field}['und'][0]['title'] : ''
        )
      )
  );
  return $l;

}

function __get_addressfield ($n, $field) {

}

function __get_commerce_line_item_reference ($n, $field) {

}

function __get_commerce_price ($n, $field) {

}

function __get_commerce_customer_profile_reference ($n, $field) {

}

function __get_commerce_product_reference ($n, $field) {

}

function __get_number_integer ($n, $field) {

}

function __get_entityreference ($n, $field) {

}

function __get_datestamp ($n, $field) {

}

function __get_viewfield ($n, $field) {

}

function __get_node_reference ($n, $field) {

}

function __get_datetime ($n, $field) {

}

function __get_fivestar ($n, $field) {

}

function __get_file_compressor ($n, $field) {

}

function _slugify($text) {
  return drupal_html_class(drupal_clean_css_identifier($text));
}

function _getAlias($n) {
/*
TODO

SELECT * 
FROM  `url_alias` 
WHERE  `source` =  'node/82'
*/
}
