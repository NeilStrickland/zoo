<?php

function wc_image_search($G,$s) {
 $url = "https://commons.wikimedia.org/w/api.php?" .
  "action=query&format=json&generator=images&prop=imageinfo&" .
  "gimlimit=500&redirects=1&" .
  "iiprop=timestamp|user|userid|url|descriptionshorturl|dimensions|mime|extmetadata&" .
  "titles={$G}+{$s}";

 $json = file_get_contents($url);
 $data = json_decode($json);
 $pages = $data->query->pages;
 $images = array();
 foreach($pages as $i => $page) {
  $image = new stdClass();
  $image->external_id = $page->pageid;
  $info = $page->imageinfo;
  $meta = $info->extmetadata;
  $image->url = $info->url;
  $image->page_url = $info->descriptionurl;
  $image->width = $info->width;
  $image->height = $info->height;
  $image->mime = $info->mime;
  $image->artist = $meta->Artist->value;
  $image->license = $meta->LicenseShortName->value;
  $image->license_url = $meta->LicenseUrl->value;
  $images[] = $image;
 }

 return $images;
}

function eol_image_search($eol_id) {
 $url = "http://www.eol.org/api/pages/1.0.json?" .
  "batch=false&taxonomy=false&" .
  "images_per_page=100&images_page=1&" .
  "videos_per_page=0&videos_page=0&" .
  "sounds_per_page=0&sound_page=0&" .
  "&texts_per_page=0&texts_page=0&" .
  "licenses=cc-by|cc-by-nc|cc-by-sa|cc-by-nc-sa&" .
  "&id=$id";

 $json = file_get_contents($url);
 $data = json_decode($json);
 $image_ids = array();
 foreach($data->dataObjects as $x) {
  if ($x->dataType == 'http://purl.org/dc/dcmitype/StillImage') {
   $image_ids[] = $x->identifier;
  }
 }

 $images = array();
 foreach($image_ids as $i) {
  $url = "http://www.eol.org/api/data_objects/1.0/" .
   $i . ".json?taxonomy=false";
  $json = file_get_contents($url);
  $data = json_decode($json);
  $info = $data->dataObjects[0];
  if ($info->identifier != $i) {
   continue;
  }

  $image = new stdClass();
  $image->external_id = $i;
  $image->url = $info->mediaURL;
  $image->page_url = $info->source;
  $image->width = 0;
  $image->height = 0;
  $image->mime = $info->mimeType;
  $image->artist = $info->rightsHolder;
  $image->license = '';
  $image->license_url = $info->license;

  $images[] = $image;
 }

 return $images;
}


?>