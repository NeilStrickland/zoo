<?php

require_once('include/zoo.inc');

$params = get_params();
if ($params->dir) {
 check_photo_dirs($params);
} else {
 choose_dir_page($params);
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

 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_dir_page($params) {
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

 $N->header('Check photo directories');
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

function check_photo_dirs($params) {
 global $zoo;

 $d = $params->dir;
 $de = $d . '/Extra';
 $db = $d . '/Best';
 $ppd = $zoo->public_pictures_dir;
 $photos = $zoo->load_where('photo'," x.dir='$d' or x.dir='$de' or x.dir='$db'");
 foreach($photos as $p) {
  $d0 = $p->dir;
  if ($d0 == $d) {
   $alt = [$db, $de];
  } elseif ($d0 == $de) {
   $alt = [$d, $db];
  } elseif ($d0 == $db) {
   $alt = [$d, $de];
  }

  if (! file_exists($ppd . '/' . $d0 . '/' . $p->file_name)) {
   foreach($alt as $a) {
    if (file_exists($ppd . '/' . $a . '/' . $p->file_name)) {
     $p->dir = $a;
     $p->save();
     echo "Moved {$p->file_name} from $d0 to $a<br/>\n";
     break;
    }
   }
  }
 }
}

