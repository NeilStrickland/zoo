<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('include/zoo.inc');

$params = get_params();
find_photos($params);
view_photos_page($params);

exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $params = new stdClass();

 $q = 'SELECT DISTINCT dir FROM tbl_photos ORDER BY dir';
 $dirs0 = $zoo->get_all($q);
 $params->dirs = [];

 foreach ($dirs0 as $d0) {
  $d = $d0->dir;
  if ($d == '.' || $d == '..') { continue; }
  if (is_dir($zoo->public_pictures_dir . '/' . $d)) {
   $params->dirs[] = $d;
  }
 }

 $params->dir = get_optional_parameter('dir','');
 $params->full_dir = $zoo->public_pictures_dir . '/' . $params->dir;
 if (! ($params->dir && is_dir($params->full_dir))) {
  $params->dir = ''; 
  $params->full_dir = ''; 
 }

 $params->taxon_id = 0;
 $params->taxon = '';
 $params->trank = '';

 $taxon_id = (int) get_optional_parameter('taxon',0);
 if ($taxon_id) {
  $taxon = $zoo->load('taxon',$taxon_id);
  if ($taxon) {
   $params->taxon_id = $taxon->id;
   $params->taxon = $taxon->name;
   $params->trank = $taxon->trank;
  }
 } else {
  $t = get_optional_parameter('taxon_display','');
  if(preg_match('/^([A-Za-z]+) \(([A-Za-z]+)\)$/',$t, $m)) {
   $params->taxon_id = 0;
   $params->taxon = $m[1];
   $params->trank = $m[2];
  }
 }

 $params->location = get_optional_parameter('location','');

 return $params;
}

//////////////////////////////////////////////////////////////////////

function find_photos($params) {
 global $zoo;

 $params->photos = [];
 $params->unclassified = [];
 $params->species_by_id = [];

 if (! ($params->dir || $params->taxon || $params->location)) {
  return;
 }

 $w = '(x.ignore IS NULL OR x.ignore=0)';

 if ($params->dir) {
  $w .= " AND x.dir='{$params->dir}'";
 }
 if ($params->location) {
  $w .= " AND x.location LIKE '%{$params->location}%'";
 }

 if ($params->taxon) {
  $w .= <<<SQL
   AND EXISTS (
    SELECT ps.id FROM tbl_photo_species ps 
     LEFT JOIN tbl_species s ON ps.species_id=s.id 
     WHERE ps.photo_id=x.id 
     AND s.{$params->trank}='{$params->taxon}'
   )
SQL;
 }

 $photos = $zoo->load_where('photo',$w);
 $params->photos = [];
 foreach($photos as $p) {
  if ($p->file_exists()) {
   $params->photos[] = $p;
  }
 }
 $n = count($params->photos);
 $params->unclassified = [];
 $params->species_by_id = [];
 foreach ($params->photos as $x) {
  $x->load_species();
  foreach($x->species as $ps) {
   if (isset($params->species_by_id[$ps->species_id])) {
    $s = $params->species_by_id[$ps->species_id];
   } else {
    $s = $zoo->load('species',$ps->species_id);
    $s->extra->photos = [];
    $params->species_by_id[$s->id] = $s;
   }
   $s->extra->photos[] = $x;
  }
  if (count($x->species) == 0) {
   $params->unclassified[] = $x;
  }
 }
 usort($params->species_by_id, fn($a, $b) => strcmp($a->binomial, $b->binomial));
}

//////////////////////////////////////////////////////////////////////

function view_photos_page($params) {
 global $zoo;

 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('Photos',['widgets' => ['autosuggest']]);
 echo $N->top_menu();
 
 $ns = count($params->species_by_id);
 $nu = count($params->unclassified);

 echo <<<HTML
<br/>
<h1>Photos</h1>
<form name="main_form">
 <table class="edged">

HTML;

 echo $H->row('Directory:', 
              $H->selector('dir',$params->dirs,$params->dir,
                           ['onchange' => 'document.main_form.submit()',
                            'empty_option' => 1]));
 $opts = ['taxon' => $params->taxon,
          'trank' => $params->trank, 
          'onchange' => 'document.main_form.submit()'];
 echo $H->row('Taxon:', $H->taxon_selector('taxon',$params->taxon_id,$opts));
 echo $H->row('Location:', $H->location_selector('location',$params->location,['onchange' => 'document.main_form.submit()']));

 echo <<<HTML
 </table>
</form>
<br/>
{$ns} species, {$nu} unclassified photos
<br/>

HTML;

 foreach($params->species_by_id as $i => $s) {
  echo <<<HTML
 <h2>{$s->descriptor()}</h2>
 <table>

 HTML;

  $i = 0;
  foreach($s->extra->photos as $x) {
   if ($i == 0) {
    echo '<tr>';
   }
   $u = $x->url();
   echo <<<HTML
 <td><img src="$u" style="max-width:200px; max-height:150px;" onclick="window.open('$u')"/></td>

HTML;
   $i++;
   if ($i == 5) {
    echo '</tr>';
    $i = 0;
   }
  }
  if ($i != 0) {
   echo '</tr>';
  }
  echo <<<HTML
 </table>
 <br/>

HTML;
 }

 if ($params->unclassified) {
  echo <<<HTML
 <h2>Unclassified</h2>
 <table>

HTML;

  $i = 0;
  foreach($params->unclassified as $x) {
   if ($i == 0) {
    echo '<tr>';
   }
   $u = $x->url();
   echo <<<HTML
 <td><img src="$u" style="max-width:200px; max-height:150px;" onclick="window.open('$u')"/></td>
HTML;
   $i++;
   if ($i == 5) {
    echo '</tr>';
    $i = 0;
   }
  }
  if ($i != 0) {
   echo '</tr>';
  }

  echo <<<HTML
</table>

HTML;
 }
 $N->footer(); 
}