<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('include/zoo.inc');

$params = get_params();

if ($params->dir) {
 find_photos($params);
 classify_photos_page($params);
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

 $params->include_classified = get_optional_parameter('include_classified',0) ? 1 : 0;
 $params->include_ignored = get_optional_parameter('include_ignored',0) ? 1 : 0; 

 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_group_page($params) {
 global $zoo;

 $q = 'SELECT DISTINCT dir FROM tbl_photos ORDER BY dir';
 $dirs0 = $zoo->get_all($q);
 $dirs = [''];

 foreach ($dirs0 as $d0) {
  $d = $d0->dir;
  if ($d == '.' || $d == '..') { continue; }
  if (is_dir($zoo->public_pictures_dir . '/' . $d)) {
   $dirs[] = $d;
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

 $params->photos = $zoo->load_where('photo'," x.dir='{$params->dir}'");
 $params->photos0 = [];
 $params->species0 = [];
 $params->species_by_id = [];

 $locations = [];

 foreach ($params->photos as $x) {
  if ($x->location) {
   $locations[$x->location] = 1;
  }
  $x0 = [$x->id, $x->camera, $x->dir, $x->file_name, $x->date, $x->location, $x->lat, $x->lng, $x->description, $x->ignore];
  $x->load_species();
  if ($x->ignore && ! $params->include_ignored) {
   continue;
  }
  foreach($x->species as $ps) {
   $x0[] = [$ps->id, $ps->species_id];
   if (isset($params->species_by_id[$ps->species_id])) {
    $s = $params->species_by_id[$ps->species_id];
    $s->extra->count++;
   } else {
    $s = $zoo->load('species',$ps->species_id);
    $s->extra->count = 1;
    $params->species_by_id[$s->id] = $s;
   }
  }
  if ($x->species && ! $params->include_classified) {
   continue;
  }
  $params->photos0[] = $x0;
 }
 usort($params->species_by_id, fn($a, $b) => $a->extra->count - $b->extra->count);
 foreach($params->species_by_id as $s) {
  $params->species0[] = [$s->id,$s->genus,$s->species,$s->common_name];
 }

 ksort($locations);
 $params->locations = [];
 foreach($locations as $l => $x) {
  $params->locations[] = $l;
 }
}

//////////////////////////////////////////////////////////////////////

function classify_photos_page($params) {
 global $zoo;

 $H = $zoo->html;
 $N = $zoo->nav;

 $photos0 = json_encode($params->photos0);
 $species0 = json_encode($params->species0);
 $locations = json_encode($params->locations);

$script = <<<JS
var photos0 = $photos0;
var species0 = $species0;
var locations = $locations;

JS;

 $ss = $H->species_selector('species_id',0,['id' => 'species_selector']);

 $N->header('Classify photos',
            ['widgets' => ['autosuggest'],
             'inline_script' => $script, 
             'scripts' => ['classify_photos'], 
             'onload' => 'zoo.classifier.init()']);
 echo $N->top_menu();

 $cbc = $H->checkbox('include_classified',$params->include_classified,['onchange' => 'document.control_form.submit()']);
 $cbi = $H->checkbox('include_ignored',$params->include_ignored,['onchange' => 'document.control_form.submit()']);
 echo <<<HTML
<h1>Classify photos</h1>
<br/>
<form name="control_form">
 <input type="hidden" name="dir" value="{$params->dir}"/>
 Include: already classified $cbc &nbsp;&nbsp; ignored $cbi
</form>
<br/>
<div id="selected_photo_div">
 <img style="max-width: 800px; max-height: 600px;" src="" id="selected_photo_img"/>
 <div>
  <table class="edged" style="width:800px;">
   <tr>
    <td>Number:</td>  
    <td id="selected_photo_number"></td>
    <td>Ignore:</td>  
    <td><input type="checkbox" id="selected_photo_ignore"/></td>
   </tr>
   <tr>
    <td>File:</td>  
    <td id="selected_photo_file_name"></td>
    <td>Directory:</td>  
    <td id="selected_photo_dir"></td>
   </tr>
   <tr>
    <td>Date:</td>  
    <td id="selected_photo_date"></td>
    <td>Camera:</td>  
    <td id="selected_photo_camera"></td>
   </tr>
   <tr>
    <td>Description:</td>  
    <td colspan="3"><input type="text" size="60" id="selected_photo_description"/></td>
   </tr>
   <tr>
    <td>Location:</td>  
    <td colspan="3">
     <input type="text" size="60" id="selected_photo_location"/>&nbsp;&nbsp;
     <a id="selected_photo_map_link" href="" style="display:none" target="_blank">Map</a><br/>
     <span id="locations_span"></span>
    </td>
   </tr>
   <tr>
    <td>Species:</td>  
    <td colspan="3" id="selected_photo_species"></td>
   </tr>
  </table>
 </div>
</div>
<br/>
<div id="recent_species_div">
</div>
<br/>
$ss  &nbsp;&nbsp;&nbsp; 
<button id="create_species_button">Add a new species</button>
HTML;

 $N->footer(); 
}