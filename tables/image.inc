<?php

global $zoo;

$zoo->add_table(
 'image','images',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'species_id' => array('type' => 'integer','link' => 'species'),
  'file_name' => array('type' => 'text'),
  'width' => array('type' => 'integer'),
  'height' => array('type' => 'integer')
 ),
 array(
  'class' => array('type' => 'text'),
  'order' => array('type' => 'text'),
  'family' => array('type' => 'text'),
  'genus' => array('type' => 'text'),
  'species' => array('type' => 'text'),
  'binomial' => array('type' => 'text'),
  'common_name' => array('type' => 'text'),
  'common_group' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.species_id,
 x.file_name,
 x.width,
 x.height,
 s.class,
 s.order,
 s.family,
 s.genus,
 s.species,
 CONCAT(s.genus,' ',s.species) AS binomial,
 s.common_name,
 s.common_group
FROM tbl_images x
 LEFT JOIN tbl_species s ON x.species_id = s.id
WHERE %s
 ORDER BY s.class,s.family,s.genus,s.species,s.id,x.id
SQL
);

class image extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'image',$id,$with_defaults);
 }

 function full_file_name() {
  global $zoo;
  return $zoo->image_dir . '/' . $this->file_name;
 }
 
 function url() {
  return "/zoo/send_image.php?id={$this->id}";
 }

 function small_img() {
  return '<img width="180" src="' . $this->url() . '"/>';
 }

 function tiny_img() {
  return '<img width="90" src="' . $this->url() . '"/>';
 }

 function aspect_ratio() {
  if (! ($this->width && $this->height)) {
   return 0;
  }

  return ((1.0 * $this->width)/($this->height));
 }

 function set_size($save = false) {
  $f = $this->full_file_name();
  $is = getimagesize($f);
  if ($is) {
   $this->width = $is[0];
   $this->height = $is[1];
   if ($save) {
    $this->save();
   }
  }
 }

 function load_object() {
  $f = $this->full_file_name();
  $is = getimagesize($f);
  $t = $is['mime'];

  if ($t == 'image/gif') {
   $this->object = imagecreatefromgif($f);
  } elseif ($t == 'image/jpeg') {
   $this->object = imagecreatefromjpeg($f);
  } elseif ($t == 'image/png') {
   $this->object = imagecreatefrompng($f);
  } else {
   trigger_error("Unrecognised photo format for {$f}: {$t}",E_USER_ERROR);
  }
 }
}

?>