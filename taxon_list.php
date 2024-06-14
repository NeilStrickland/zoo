<?php

require_once('include/zoo.inc');

$params = get_params();
show_page($params);
exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $zoo->load_table('taxon');
 $params = new stdClass();

 $params->show = [];
 $params->tranks = [];
 foreach(taxon::$tranks as $r) {
  $d = ($r == 'genus') ? 0 : 1;
  $params->show[$r] = get_optional_parameter("show_$r",1) ? 1 : 0;
  if ($params->show[$r]) {
   $params->tranks[] = $r;
  }
 }

 if ($params->tranks) {
  $w = "x.trank in ('" . implode("','",$params->tranks) . "')";
  $params->taxa = $zoo->load_where('taxon', $w);
 } else {
  $params->taxa = [];
 }

 return $params;
}

//////////////////////////////////////////////////////////////////////

function show_page($params) {
 global $zoo;
 $H = $zoo->html;
 $N = $zoo->nav;

 $N->header('Taxa',['widgets' => ['autosuggest'],'scripts' => ['taxon_list']]);
 echo $N->top_menu();

 $taxon_sel = $H->species_selector('taxon_id','',['onchange' => 'edit_taxon()']);
 echo <<<HTML
<h1>Taxa</h1>
<br>
<form name="main_form">
<b>Search:</b><br/> $taxon_sel
<button type="button" onclick="edit_taxon()">Edit</button>
<br/><br/>
</form>
<br/>
HTML;

 echo $H->edged_table_start();
 echo $H->spacer_row(50,120,80,120,50,50);
 foreach($params->taxa as $t) {
  echo $H->tr($H->td($t->id) .
              $H->td($t->name) .
              $H->td($t->trank) .
              $H->td($t->parent_name) .
              $H->popup_td('Edit', "taxon_info.php?id={$t->id}") .
              $H->popup_td('Wiki', $t->wiki_url()));
 }

 echo $H->edged_table_end();

 $N->footer();
}