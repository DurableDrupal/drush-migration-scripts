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
define("SCS", "http://durabledrupal.net:4064");

// map contant types on SCS to legacy content types
  // if no matching legacy content type, create it thusly:
  // "scs_content_type" => "scs_content_type"
  // so that appropriate data marshalling function is invoked
$content_types = array(
  "banner" => "banners",
  "destacado_home" => "destacados-home",
  "destacado" => "destacados",
  "evento_global" => "eventos-globales",
   "medicamento" => "drugs",
   "paciente" => "pacientes",
   "page" => "pages",
   "palabra" => "terminos",
   "producto" => "productos",
   "profesional" => "profesionales",
   "story" => "articulos",
   "video" => "videos",
   "video_noticia" => "videonoticias",
// postponed
//   "oferta_mes" => "oferta_mes",
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
    /* D7 
    $result = db_query("SELECT nid FROM node WHERE type = :nodeType ", array(':nodeType'=>$drupal_content_type));
    foreach ($result as $obj) {
      upsert_node($obj->nid, $scs_content_type);
    }
    */
    $result = db_query('SELECT nid FROM node WHERE type = "%s"', $drupal_content_type);
    while ($obj = db_fetch_object($result)) {
      upsert_node($obj->nid, $scs_content_type);
    }
}

function upsert_node($nid, $scs_content_type) {
  $node = node_load($nid);
 //  print "Node: " . $node->title . "\n";
 //  print "Node " . $node->nid . "\n";

  // marshall info
  switch($scs_content_type) {
    case 'banners':
      // if ($node) print_r(upsert_banner($node), false);
      if ($node) upsert_banner($node);
      break;
    case 'destacados-home':
      if ($node) upsert_destacado_home($node);
      break;
    case 'destacados':
      if ($node) upsert_destacado($node);
      break;
    case 'drugs':
      if ($node) upsert_medicamento($node);
      break;
    case 'eventos-globales':
      if ($node) upsert_evento_global($node);
      break;
    case 'pacientes':
      if ($node) upsert_paciente($node);
      break;
    case 'pages':
      if ($node) upsert_page($node);
      break;
    case 'terminos':
      if ($node) upsert_termino($node);
      break;
    case 'productos':
      if ($node) upsert_producto($node);
      break;
    case 'profesionales':
      if ($node) upsert_profesional($node);
      break;
    case 'articulos':
      if ($node) upsert_articulo($node);
      break;
    case 'videos':
      if ($node) upsert_video($node);
      break;
    case 'videonoticias':
      if ($node) upsert_video_noticia($node);
      break;
/*
    case 'video':
      $n = node_load($nid);
      if ($n) upsert_video($n);
      break;
*/
    default:
      print "no such content type, exiting\n";
      break;
  }
}

function _upsert_action() {
}

function upsert_banner($n) {
// print_r($n);
  // populate payload
  $banner = array (
    // nid
    idLegacy => __get($n, 'nid'),
    // meta_data, including path -> slug
    metaData => __get($n, 'meta_data'),
    // cck multi-option list of countries, array of strings 
    bannerPais => (isset($n->field_banner_pais) ? $n->field_banner_pais : null),
    // image
    bannerImage => __get_media($n, 'field_banner_image'),
    // link
    bannerLink => __get_link_field($n, 'field_banner_link'),
    // it is actually the Drupal tid of the taxonomy field in the case of this content type
    // for 
    bannerZona => __get_taxonomy_field($n, 'taxonomy'),
  );
  // upsert to SCS (api best practices demand plurals for endpoints)
  upsert($banner, 'banners');
// print $n->nid . "\n";
// print "banner: \n";
// print_r($banner);
//?
  return $banner;
}

function upsert_destacado_home($n) {
    $dh = array (
      // nid
      idLegacy => __get($n, 'nid'),
      // meta_data, including path -> slug
      metaData => __get($n, 'meta_data'),
      // cck multi-option list of countries, array of strings 
      destacadoHomePais => (isset($n->field_pais_multiple) ? $n->field_pais_multiple : null),
      // image
      destacadoHomeImage => __get_media($n, 'field_dh_foto'),
      // link
      destacadoHomeLink => __get_link_field($n, 'field_dh_link'),
      // it is actually the Drupal tid of the taxonomy field in the case of this content type
      // for 
      destacadoHomeTag => __get_taxonomy_field($n, 'taxonomy'),
    );
    // upsert to SCS (api best practices demand plurals for endpoints)
    upsert($dh, 'destacados-home');
  return $dh;
}

function upsert_destacado($n) {
    $destacado = array (
      // nid
      idLegacy => __get($n, 'nid'),
      // meta_data, including path -> slug
      metaData => __get($n, 'meta_data'),
      // image
      destacadoImage => __get_media($n, 'field_destacado_foto'),
      // link
      destacadoLink => __get_link_field($n, 'field_destacado_link'),
      // it is actually the Drupal tid of the taxonomy field in the case of this content type
      // for 
      destacadoTag => __get_taxonomy_field($n, 'taxonomy'),
    );
    // upsert to SCS (api best practices demand plurals for endpoints)
    upsert($destacado, 'destacados');
  return $destacado;
}

function upsert_medicamento($n) {
    $drugs = array (
      idLegacy => __get($n, 'nid'),
      metaData => __get($n, 'meta_data'),
      drugPais => (isset($n->field_pais_multiple) ? $n->field_pais_multiple : null),
      drugDescription => $n->body,
      drugSummary => $n->teaser,
      drugPhoto => __get_media($n, 'field_medicamento_foto'),
      drugImage => __get_media($n, 'field_slide_image'),
      drugLink => __get_link_field($n, 'field_medicamento_link'),
      drugTags => __get_taxonomy_field($n, 'taxonomy'),
    );
    upsert($drugs, 'drugs');
  return $destacado;
}

function upsert_evento_global($n) {
  // prepare imgs
  // TODO eliminate dups
  $img1 = __get_media($n, 'field_slide_image');
  $img2 = __get_media($n, 'field_eg_image');
  $imgs = array($img1, $img2);
//  $all_imgs 
  $eg = array (
    // nid
    idLegacy => __get($n, 'nid'),
    // meta_data, including path -> slug
    metaData => __get($n, 'meta_data'),
    eventOrganizer => (isset($n->field_pais[0]['value']) ? $n->field_pais[0]['value'] : null),
    eventSpeaker => (isset($n->field_eg_dictante[0]['value']) ? $n->field_eg_dictante[0]['value'] : null),
    eventInfo => array(
      eventName => $n->title,
      eventImage => $imgs,
      // change whatever Drupal has to unix timestamp, then format that in proper format to feed to MongoDB
      eventDateStart => (isset($n->field_eg_fecha[0]['value']) ?  format_date(strtotime($n->field_eg_fecha[0]['value']) ,'custom','F j, Y', NULL, 'en') : null),
      eventDateEnd => (isset($n->field_eg_fecha[0]['value2']) ?  format_date(strtotime($n->field_eg_fecha[0]['value2']) ,'custom','F j, Y', NULL, 'en') : null),
      location => array(
        name => (isset($n->field_eg_direccion[0]['value']) ? $n->field_eg_direccion[0]['value'] : null),
        city => (isset($n->field_ciudad[0]['value']) ? $n->field_ciudad[0]['value'] : null),
        country => (isset($n->field_pais[0]['value']) ? $n->field_pais[0]['value'] : null),
      ),
      eventWebsite => __get_link_field($n, 'field_eg_website'),
    ),
      eventDescription => $n->body,
      eventSummary => $n->teaser,
      eventTags => __get_taxonomy_field($n, 'taxonomy'),
      eventEmailContact => (isset($n->field_eg_mail[0]['email']) ? $n->field_eg_mail[0]['email'] : null),
      eventTwitter => __get_link_field($n, 'field_eg_twitter'),
  );
  // upsert to SCS (api best practices demand plurals for endpoints)
  upsert($eg, 'eventos-globales');
return $dh;
}

function upsert_paciente($n) {
    $paciente = array (
      idLegacy => __get($n, 'nid'),
      metaData => __get($n, 'meta_data'),
      pacienteFechaNacimiento => (isset($n->field_paciente_cumple[0]['value']) ?  format_date(strtotime($n->field_paciente_cumple[0]['value']) ,'custom','F j, Y', NULL, 'en') : null),
      pacienteEmail => (isset($n->field_paciente_email[0]['email']) ? $n->field_paciente_email[0]['email'] : null),
    );
    upsert($paciente, 'pacientes');
  return $paciente;
}

function upsert_page($n) {
  // note: TODO: use __get_text_with_summary
  $page = array(
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    // will take from several fields, so just send content item
    pageMain => __get_text_long($n),
  );
  upsert($page, 'pages');
}

function upsert_termino($n) {
  // note: TODO: use __get_text_with_summary
  $termino = array(
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    // will take from several fields, so just send content item
    terminoDefinicion => __get_text_long($n),
  );
  upsert($termino, 'terminos');
}

function upsert_producto($n) {
  $producto = array(
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    productoPais => (isset($n->field_pais_multiple) ? $n->field_pais_multiple : null),
    productoDescription => $n->body,
    productoSummary => $n->teaser,
    productoNuevo => (isset($n->field_producto_nuevo[0]['value']) ? $n->field_producto_nuevo[0]['value'] : null),
    productoSemana => (isset($n->field_producto_semana[0]['value']) ? $n->field_producto_semana[0]['value'] : null),
    productoPhoto => __get_media($n, 'field_producto_foto'),
    productoVideo => __get_embedded_video($n, 'field_video_video'),
    productoSlideShow => (isset($n->field_slide_show[0]['value']) ? $n->field_slide_show[0]['value'] : null),
    productoSlideImage => __get_media($n, 'field_slide_image'),
    productoTags => __get_taxonomy_field($n, 'taxonomy'),
  );
  upsert($producto, 'productos');
}

function upsert_profesional($n) {
  // Options arrays for array_key_exists($value, $options) ? $options[$value] : null returns
  $anios_ejercicio = array(
    1 => '0-5',
    2 => '5-10',
    3 => '10-20',
    4 => 'Más de 20 años',
  );
  $ocupacion = array(
    1 => 'Odontólogo',
    2 => 'Estudiante Odontología',
    3 => 'Técnico de Laboratorio',
    4 => 'Estudiante Técnico de Laboratorio',
    5 => 'Asistente Dental',
    6 => 'Estudiante Asistente Dental',
    7 => 'Higienista',
    8 => 'Estudiante Higienista',
    9 => 'Secretaria',
  );
  $genero = array(
    'M' => 'Masculino',
    'F' => 'Femenino',
  );
  $boletin = array(
    'no' => 'No Recibir Novedades',
    'yes' => 'Deseo Recibir Novedades de Dental Tv Web',
  );
  $temas = array(
    0 => 'Implantes',
    1 => 'Endodóncis',
    2 => 'Periodoncis',
    3 => 'Laboratorios dentales',
    4 => 'Prevencios',
    5 => 'Láses',
    6 => 'Ortodoncis',
    7 => 'Estética dentas',
    8 => 'Operatoria dentas',
    9 => 'Odontopediatrís',
    10 => 'Urgencias',
    11 => 'Radiologís',
    12 => 'Estomatologís',
    13 => 'Productos odontológicos',
    14 => 'Últimas novedades',
    15 => 'Marketing en el consultoris',
    16 => 'Cirugís',
    17 => 'Prótesis',
    18 => 'Legales del consultoris',
    19 => 'Contables del consultoris',
    20 => 'Organización del consultoris',
    21 => 'Odontologia por CAD/CAM y digitas',
  );
  $temas_elegidos = Array();
  foreach($n->field_profesional_interes as $key => $option) {
    array_push($temas_elegidos, $temas[$option['value']]);
  }
  
  $profesional = array(
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    profesionalLocation => array(
      name => (isset($n->field_profesional_nya[0]['value']) ? $n->field_profesional_nya[0]['value'] : null),
      city => (isset($n->field_ciudad[0]['value']) ? $n->field_ciudad[0]['value'] : null),
      country => (isset($n->field_pais[0]['value']) ? $n->field_pais[0]['value'] : null),
    ),
    profesionalDescription => $n->body,
    profesionalSummary => $n->teaser,
    profesionalOcupacion => array_key_exists($n->field_profesional_ocupacion[0]['value'], $ocupacion) ? $ocupacion[$n->field_profesional_ocupacion[0]['value']] : null,
    profesionalAniosEjercicio => array_key_exists($n->field_profesional_anos[0]['value'], $anios_ejercicio) ? $anios_ejercicio[$n->field_profesional_anos[0]['value']] : null,
    profesionalGenero => array_key_exists($n->field_profesional_sexo[0]['value'], $genero) ? $genero[$n->field_profesional_sexo[0]['value']] : null,
    profesionalNacimiento => (isset($n->field_profesional_nacimiento[0]['value']) ?  format_date(strtotime($n->field_profesional_nacimiento[0]['value']) ,'custom','F j, Y', NULL, 'en') : null),
    profesionalBoletin => array_key_exists($n->field_profesional_boletin[0]['value'], $boletin) ? $boletin[$n->field_profesional_boletin[0]['value']] : null,
    // this is the only case where you need to return array of interests, see above
    profesionalInteres => $temas_elegidos,
  );
  upsert($profesional, 'profesionales');
}

function upsert_articulo($n){
  $articulo = array (
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    articuloBody => $n->body,
    articuloSummary => $n->teaser,
    articuloPais => (isset($n->field_pais[0]['value']) ? $n->field_pais[0]['value'] : null),
  );
  upsert($articulo, 'articulos');
}

function upsert_video($n) {
  $video = array (
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    videoPubDate =>  (isset($n->field_video_fecha[0]['value']) ?  format_date(strtotime($n->field_video_fecha[0]['value']) ,'custom','F j, Y', NULL, 'en') : null),
    videoDescripcion => $n->body,
    videoSummary => $n->teaser,
    videoDestacado => (isset($n->field_slide_show[0]['value']) ? $n->field_slide_show[0]['value'] : null),
    videoMedia =>  __get_embedded_video($n, 'field_video_video'),
    videoTags => __get_taxonomy_field($n, 'taxonomy'),
  );
  upsert($video, 'videos');
}

function upsert_video_noticia($n) {
  $video = array (
    idLegacy => __get($n, 'nid'),
    metaData => __get($n, 'meta_data'),
    videoNoticiaPubDate =>  (isset($n->field_nv_fecha[0]['value']) ?  format_date(strtotime($n->field_nv_fecha[0]['value']) ,'custom','F j, Y', NULL, 'en') : null),
    videoNoticiaDescripcion => $n->body,
    videoNoticiaSummary => $n->teaser,
    videoNoticiaDestacado => (isset($n->field_slide_show[0]['value']) ? $n->field_slide_show[0]['value'] : null),
    videoNoticiaMedia =>  __get_embedded_video($n, 'field_nv_video'),
    videoNoticiaTags => __get_taxonomy_field($n, 'taxonomy'),
  );
  upsert($video, 'videonoticias');
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

  /*
   * D7 doesn't have this
   * so just make the stupid case structure bigger, or better yet, call the __get_specific_field() function directly except for those below
   *  $upsert_field_map = field_info_field_map();
   */

  switch ($field) {
    case 'nid':
    case 'vid':
    case 'type':
    case 'language':
    case 'title':
    case 'uid':
    case 'name':
      return $n->{$field};
      break;
    case 'created':
    case 'changed':
      // Must be valid ISODate format for MongoDB
      return format_date($n->{$field} ,'custom','F j, Y', NULL, 'en');
      break;
    case 'status':
      return ($n->status == 1) ? TRUE:FALSE;
      break;
    case 'promote':
      return ($n->promote == 1) ? TRUE:FALSE;
      break;
    case 'sticky':
      return ($n->sticky == 1) ? TRUE:FALSE;
      break;
    case 'disabled':
      return ($n->status == 1) ? FALSE:TRUE;
      break;
    case 'path':
      if (isset($n->path)) {
        $thePath = explode('/', $n->path);
        if ($theSlug = end($thePath)) {
          return $theSlug;
        } else {
          return _slugify($n->title);
        }
      } else {
        return _slugify($n->title);
      }
      break;
    case 'meta_data':
      return __get_meta_data($n);
    default:
    /*
     * only D7
     */
      // get field type from field map info
      //// $type = $upsert_field_map[$field]['type'];

// print "field: " . $field . " field type: " . $type . "\n";

      // return result from function __get_{$n, $field}
      //// if ($type) {
      ////  if ($type == 'file' || $type == 'image') return __get_media($n, $field);
      ////  $function = '__get_' . $type;
      ////  return $function($n, $field);
      ////}
      ////else {
        return '';
      ////}
      break;
  }
}

function __get_meta_data ($n) {
  $m = array(
    itemSlug => __get($n, 'path'),
    itemSlugLegacy => __get($n, 'path'),
    itemName => __get($n, 'title'),
    itemType => __get($n, 'type'),
    language => __get($n, 'language'),
    published => __get($n, 'status'),
    promote => __get($n, 'promote'),
    sticky => __get($n, 'sticky'),
    publishedDate => __get($n, 'changed'),
    disabled => __get($n, 'disabled'),
    createdDate => __get($n, 'created'),
    modifiedDate => __get($n, 'changed'),
    revisionId => __get($n, 'vid'),
    metaTags => (isset($n->nodewords) ? $n->nodewords : null),
  );
  return $m;
}

// For model schema attribute TextLong
function __get_text_long ($n) {
/*
note: TODO: author ref, as well as other fields not required in this app, but which are present in SCS model schema
note: TODO: use __get_text_with_summary
*/
  $text = array(
    textTeaser => array("label" => "Teaser", "value" => isset($n->teaser) ? $n->teaser : null), 
    textBody => array("label" => "Body", "value" => isset($n->body) ? $n->body : null), 
  );
  return $text;
}

/* D7
function __get_text_with_summary ($n, $field) {
  $b = array(
      "summary" => array("label" => "summary", "value" => ((isset($n->{$field}['und'][0]['safe_summary'])) ?
      $n->{$field}['und'][0]['safe_summary'] : ''), "help" => ""),
      "body" => array("label" => "body", "value" => ((isset($n->{$field}['und'][0]['safe_value'])) ?
      $n->{$field}['und'][0]['safe_value'] : ''), "help" => ""),
  );
  return $b;
}
*/

/* D7
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
*/

function __get_taxonomy_field ($n, $field) {
  $tags = array();
  if (isset($n->{$field})) {
    $the_tags = $n->{$field};
    foreach ($the_tags as $a_tag) {
      $tid = $a_tag->tid;
      $vid = $a_tag->vid;
      $vname = __get_vname($vid);
      $name = $a_tag->name;
      $slug = _slugify($name);
      $tag = array(
        "idLegacy" => $tid,
        "vocabIdLegacy" => $vid,
        "vocabName" => $vname,
        "tagSlug" => $slug,
        "tagName" => $name
      );
      array_push($tags, $tag);
    }
  }
  return $tags;
}

// D7 has 'und', etc., see bm_ version
function __get_media ($n, $field) {
  if (isset($n->{$field}[0])) {
    $medias = array();
    for ($i = 0; $i < count($n->{$field}); $i++) {
      $media = array(
        "idLegacy" => $n->nid,
        "mediaLink" => array(
          "idLegacy" => (isset($n->{$field}[$i]['fid'])) ?
          $n->{$field}[$i]['fid'] : '',
          "uriLegacy" => (isset($n->{$field}[$i]['filepath'])) ?
          $n->{$field}[$i]['filepath'] : '',
          "linkUrl" => "",
          "linkFilePath" => (isset($n->{$field}[$i]['filename'])) ?
          $n->{$field}[$i]['filename'] : '',
          // This is for stuff like name, title attributes
          "linkAttributes" => array(
            array(
              "attrName" => "title",
              "attrValue" => (isset($n->{$field}[$i]['data']['title'])) ?
                $n->{$field}[$i]['data']['title'] : ''
            ),
            array(
              "attrName" => "alt",
              "attrValue" => (isset($n->{$field}[$i]['data']['alt'])) ?
                $n->{$field}[$i]['data']['alt'] : ''
            ),
          ),
        ),
        // not supported in legacy image content type
        "mediaCaption" => "",
        // not supported in legacy image content type
        "mediaCredits" => "",
        "mediaMime" => (isset($n->{$field}[$i]['filemime'])) ?
          $n->{$field}[$i]['filemime'] : '',
        "mediaSize" => (isset($n->{$field}[$i]['filesize'])) ?
          $n->{$field}[$i]['filesize'] : '',
      );
      array_push($medias, $media);
    }
  return (count($medias) > 1) ? $medias : $medias[0];
  }
}

function __get_embedded_video($n, $field) {
  if (isset($n->{$field}[0])) {
    $medias = array();
    for ($i = 0; $i < count($n->{$field}); $i++) {
      $media = array(
        "idLegacy" => $n->nid,
        "mediaLink" => array(
          "linkFilePath" => (isset($n->{$field}[$i]['value'])) ?
          $n->{$field}[$i]['value'] : '',
          "linkUrl" => (isset($n->{$field}[$i]['embed'])) ?
          $n->{$field}[$i]['embed'] : '',
        ),
      );
      array_push($medias, $media);
    }
  return (count($medias) > 1) ? $medias : $medias[0];
  }
}

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
      "linkUrl" => (isset($n->{$field}[0]['url'])) ?
      $n->{$field}[0]['url'] : '',
      "linkAttributes" => array(
        array(
          "attrName" => "title",
          "attrValue" => (isset($n->{$field}[0]['title'])) ?
            $n->{$field}[0]['title'] : ''
        ),
        array(
          "attrName" => "target",
          "attrValue" => (isset($n->{$field}[0]['attributes']['target'])) ?
            $n->{$field}[0]['attributes']['target'] : ''
        ),
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

function __get_vname($vid) {
  $vocabs = taxonomy_get_vocabularies();
  foreach ($vocabs as $vocab) {
    $key = $vocab->vid;
    $value = $vocab->name;
    if ($vid == $key) {
      return $value;
    }
  }
  // didn't find vocab
  return '';
}

function _slugify($text) {
  // D7
  // return drupal_html_class(drupal_clean_css_identifier($text));
  // backported to D6 https://www.drupal.org/files/1443012_backport_css_class_filter.patch
  return tao_drupal_html_class(tao_drupal_clean_css_identifier($text));
}

function __getAlias($n) {
/*
TODO

SELECT * 
FROM  `url_alias` 
WHERE  `source` =  'node/82'
*/
}

function __get_array_value($value, $options){
  return array_key_exists($value, $options) ? $options[$value] : null;
}

/*
 *  https://www.drupal.org/files/1443012_backport_css_class_filter.patch
 */
function tao_drupal_html_class($class) {
  return tao_drupal_clean_css_identifier(drupal_strtolower($class));
}
function tao_drupal_clean_css_identifier($identifier, $filter = array(' ' => '-', '_' => '-', '/' => '-', '[' => '-', ']' => '')) {
  // By default, we filter using Drupal's coding standards.
  $identifier = strtr($identifier, $filter);
  // Valid characters in a CSS identifier are:
  // - the hyphen (U+002D)
  // - a-z (U+0030 - U+0039)
  // - A-Z (U+0041 - U+005A)
  // - the underscore (U+005F)
  // - 0-9 (U+0061 - U+007A)
  // - ISO 10646 characters U+00A1 and higher
  // We strip out any character not in the above list.
  $identifier = preg_replace('/[^\x{002D}\x{0030}-\x{0039}\x{0041}-\x{005A}\x{005F}\x{0061}-\x{007A}\x{00A1}-\x{FFFF}]/u', '', $identifier);

  return $identifier;
}

function upsert($item, $api) {
  // encode as JSON
  // D7
  // $json = json_encode($item);
  // D6 may be buggy, use pure php instead
  // $json = drupal_to_js($item);
  $json = json_encode($item);
  // print $json . "\n";
  // set up url
  $url = SCS . '/api/' . $api;
  $method = 'PUT';
  // set up options and send request to JSON api on SCS

/* 
 * Perform upsert
 * 
 * D6
 * drupal_http_request($url, $headers = array(), $method = 'GET', $data = NULL, $retry = 3, $timeout = 30.0)
 */

  $result = drupal_http_request ( 
    $url, 
    array ( 'Content-Type' => 'application/json'),
    $method,
    $json,
    3,
    60.0
  );

  // inform of success or failure
  if ($result->code != 200) {
    print ($result->status_message);
    print_r($item, false);
    print $json . "\n";
  }
}
