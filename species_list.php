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
 $params->taxon_id = (int) get_optional_parameter('taxon_id',0);

 if ($params->taxon_id) {
  $t = $zoo->load('taxon',$params->taxon_id);
  $params->taxon = $t;
  if ($t) {
   $w = "x.{$t->trank}='{$t->name}'";
   $params->species = $zoo->load_where('species',$w);
  } else {
   $params->species = [];
  }
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

 $N->header('Species',['widgets' => ['autosuggest'],'scripts' => ['species_list']]);
 echo $N->top_menu();

 $species_sel = $H->species_selector('species_id','',['onchange' => 'edit_species()']);
 $taxon_sel = $H->taxon_selector('taxon_id','',['onchange' => 'document.main_form.submit()']);
 $cb = $H->checkbox('show_all',$params->show_all,['onchange' => 'document.main_form.submit()']);
 $nb = $H->popup_button('Add a new species','species_info.php?command=new');

 echo <<<HTML
<h1>Species</h1>
<br>
<form name="main_form">
<b>Search:</b><br/> $species_sel
<button type="button" onclick="edit_species()">Edit</button>
<button type="button" onclick="clear_species()">Clear</button>
<br/><br/>
<b>Taxa:</b><br/> $taxon_sel
<br/><br/>
Show all species: $cb
</form>
<br/>
$nb
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