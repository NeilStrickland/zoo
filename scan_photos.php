<?php

require_once('include/zoo.inc');

$params = get_params();
if ($params->dir) {
 find_photos($params);
 report_page($params);
} else {
 choose_group_page($params);
}

exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $params = new stdClass();

 $params->dir = get_optional_parameter('dir','');
 $params->full_dir = $zoo->public_pictures_dir . '/' . $params->dir;
 if (! ($params->dir && is_dir($params->full_dir))) {
  $params->dir = ''; 
  $params->full_dir = ''; 
 }

 $photos = $zoo->load_all('photos');
 $params->photos_by_full_name = [];
 foreach($photos as $p) {
  $f = $p->full_file_name();
  if (file_exists($f)) {
   $params->photos_by_full_name[$f] = $p;
  }
 }

 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_group_page($params) {
 global $zoo;

 $dirs0 = scandir($zoo->public_pictures_dir);
 $dirs = [''];
 foreach ($dirs0 as $d) {
  if ($d == '.' || $d == '..') { continue; }
  if (is_dir($zoo->public_pictures_dir . '/' . $d)) {
   $dirs[] = $d;
  }
  if (is_dir($zoo->public_pictures_dir . '/' . $d . '/Best')) {
   $dirs[] = $d . '/Best';
  }
  if (is_dir($zoo->public_pictures_dir . '/' . $d . '/Extra')) {
   $dirs[] = $d . '/Extra';
  }
 }

 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('Classify photos');
 echo $N->top_menu();

 $dir_sel = $H->selector('dir',$dirs,$params->dir,['onchange' => 'document.main_form.submit()']);

 echo <<<HTML
<h1>Classify photos</h1>
<br/>
<form name="main_form">
Choose directory: $dir_sel
</form>

HTML;
 
 $N->footer();
}

//////////////////////////////////////////////////////////////////////

function find_photos($params) {
 global $zoo;

 $params->new_photos = [];
 $full_dir = $zoo->public_pictures_dir . '/' . $params->dir;
 $photos0 = scandir($full_dir);
 foreach ($photos0 as $f) {
  $ff = $full_dir . '/' . $f;
  $e = strtolower(pathinfo($ff, PATHINFO_EXTENSION));
  if (is_file($ff) && ($e == 'jpg' || $e == 'jpeg')) {
   if (! isset($params->photos_by_full_name[$ff])) {
    $p = $zoo->new_object('photo');
    $p->dir = $params->dir;
    $p->file_name = $f;
    $p->get_exif();
    $p->save();
    $params->new_photos[] = $p;
   }
  }
 }
}

//////////////////////////////////////////////////////////////////////

function report_page($params) {
 global $zoo;

 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('New photos');
 echo $N->top_menu();

 echo "<h1>New photos</h1>";

 echo $H->edged_table_start();
 foreach($params->new_photos as $p) {
  echo $H->row($p->file_name, $p->date, $p->lat, $p->lng);
 }
 echo $H->edged_table_end();

 $N->footer(); 
}