<?php

require_once('include/zoo.inc');

$params = get_params();
if ($params->species_id) {
 rank_photos_page($params);
} else if ($params->dir) {
 choose_species_page($params);
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

 $params->species_id = (int) get_optional_parameter('species_id',0);
 $params->species = null;
 if ($params->species_id) {
  $params->species = $zoo->load('species',$params->species_id);
 }
 if (! $params->species) {
  $params->species_id = 0;
  $params->species = null;
 }

 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_dir_page($params) {
 global $zoo;

 $dirs0 = scandir($zoo->public_pictures_dir);
 $dirs = [''];
 foreach ($dirs0 as $d) {
  if (is_dir($zoo->public_pictures_dir . '/' . $d)) {
   $dirs[] = $d;
  }
 }

 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('Rank Photos');
 echo $N->top_menu();

 $dir_sel = $H->selector('dir',$dirs,$params->dir,['onchange' => 'document.main_form.submit()']);
 $sp_sel = $H->species_selector('species_id','',['onchange' => 'document.main_form.submit()']);

 echo <<<HTML
<h1>Rank Photos</h1>
<br/>
<form name="main_form">
Choose directory: $dir_sel
<br/><br/>
or a species : $sp_sel
</form>

HTML;
 
 $N->footer();
}

//////////////////////////////////////////////////////////////////////

function choose_species_page($params) {
 global $zoo;

 $photo_species = $zoo->load_where('photo_species',"p.dir='{$params->dir}'");
 $ids = [];
 foreach ($photo_species as $ps) {
  $ids[$ps->species_id] = 1;
 }
 $ids = array_keys($ids);
 if ($ids) {
  $species = $zoo->load_where('species',"x.id in (" . implode(',',$ids) . ")");
 } else {
  $species = [];
 }
 $n = count($species);

 $H = $zoo->html;
 $N = $zoo->nav;

 $d = $params->dir;
 if ($d) { $d = " ($d)"; }

 $N->header('Rank Photos');
 echo $N->top_menu();

 echo <<<HTML
<h1>Rank Photos$d</h1>
<br/>
$n species
<br/><br/>
HTML;
 
 echo $H->edged_table_start();

 foreach($species as $s) {
  echo <<<HTML
  <tr>
   <td>{$s->binomial}</td>
   <td>{$s->common_name}</td>
   <td class="command" onclick="window.open('rank_photos.php?species_id={$s->id}')">Rank photos</a></td>
  </tr>

HTML;
 }
 echo $H->edged_table_end();
 $N->footer();
}

//////////////////////////////////////////////////////////////////////

function rank_photos_page($params) {
 global $zoo;

 $params->species->load_photos();

 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('Rank Photos');
 echo $N->top_menu();

 echo <<<HTML
<h1>{$params->species->binomial} ({$params->species->common_name})</h1>
<br/>

HTML;

foreach ($params->species->photos as $x) {
 $t = $x->photo_dir . '/' . $x->photo_file_name;
 if ($x->photo_description) { $t .= ': ' . $x->photo_description; }
 if ($x->photo_location) { $t .= ', ' . $x->photo_location; }

 echo $x->img() . '<br/>' . $t . '<br/><br/>' . PHP_EOL;
}

 $N->footer(); 
}