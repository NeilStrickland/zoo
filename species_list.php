<?php

require_once('include/zoo.inc');

$params = get_params();
show_page($params);
exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $params = new stdClass();

 $params->show_all = get_optional_parameter('show_all',0) ? 1 : 0;
 $params->taxon = get_optional_parameter('taxon','');

 if ($params->taxon) {
  $t = $zoo->db->escape($params->taxon);

  $w = <<<SQL
x.class='$t' OR x.order='$t' OR x.family='$t' OR x.genus='$t'
SQL;
  $params->species = $zoo->load_where('species',$w);
 } else if ($params->show_all) {
  $params->species = $zoo->load_all('species');
 } else {
  $params->species = [];
 }

 return $params;
}

//////////////////////////////////////////////////////////////////////

function show_page($params) {
 global $zoo;
 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('Species',['widgets' => ['autosuggest']]);
 echo $N->top_menu();

 $species_sel = $H->species_selector('species_id','',['onchange' => 'edit_species()']);
 $taxon_sel = $H->taxon_selector('taxon_id','',['onchange' => 'document.main_form.submit()']);
 $cb = $H->checkbox('show_all',$params->show_all,['onchange' => 'document.main_form.submit()']);
 echo <<<HTML
<h1>Species</h1>
<br>
<form name="main_form">
<b>Search:</b><br/> $species_sel
<button type="button" onclick="edit_species()">Edit</button>
<br/><br/>
<b>Taxa:</b><br/> $taxon_sel
<br/><br/>
Show all species: $cb
</form>
<br/>
HTML;

 echo $H->edged_table_start();

 foreach($params->species as $s) {
  echo $H->tr($H->td($s->linked_binomial()) .
              $H->td($s->common_name) .
              $H->popup_td('Edit', "species_info.php?id={$s->id}"));
 }

 echo $H->edged_table_end();

 $N->footer();
}