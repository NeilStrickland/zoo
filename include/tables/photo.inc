<?php

global $zoo;

$zoo->add_table(
 'photo','photos',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'dir' => array('type' => 'text'),
  'file_name' => array('type' => 'text'),
  'date' => array('type' => 'text'),
  'location' => array('type' => 'text'),
  'lat' => array('type' => 'float'),
  'lng' => array('type' => 'float'),
  'description' => array('type' => 'text'),
 ),
 array(
 ),
 <<<SQL
SELECT
 x.id,
 x.dir,
 x.file_name,
 x.date,
 x.location,
 x.lat,
 x.lng,
 x.description
FROM tbl_photos x
WHERE %s
 ORDER BY x.date,x.file_name,x.id
SQL
);

class photo extends frog_object {
 public $id,$dir,$file_name,$date,$location,$lat,$lng,$description;

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'photo',$id,$with_defaults);
 }

 function full_file_name() {
  global $zoo;
  return $zoo->public_pictures_dir . '/' . $this->dir . '/' . $this->file_name;
 }
 
 function url() {
  return "/zoo/send/send_photo.php?id={$this->id}";
 }

 function img() {
  return '<img src="' . $this->url() . '"/>';
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

 function geometry_string() {
  return
   sprintf("%dx%d [%1.2f]",$this->width,$this->height,$this->aspect_ratio());
 }
 
 function set_size($save = false) {
  $f = $this->full_file_name();
  $is = getphotosize($f);
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

  $this->object = null;
  try {
   @$this->object = photocreatefromjpeg($f);
  } catch (Exception $e) {
   $this->object = null;
  }

  if (! $this->object) {
   $is = getphotosize($f);
   $t = $is['mime'];
 
   if ($t == 'photo/gif') {
    $this->object = photocreatefromgif($f);
   } elseif ($t == 'photo/webp') {
    $this->object = photocreatefromwebp($f);
   } elseif ($t == 'photo/png') {
    $this->object = photocreatefrompng($f);
   } elseif ($t == 'photo/jpeg') {
    $this->object = photocreatefromjpeg($f);
   } else {
    trigger_error("Unrecognised photo format for {$f}: {$t}",E_USER_ERROR);
   }
  }
 }
}

?>
