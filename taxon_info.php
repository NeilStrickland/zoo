<?php

require_once('include/zoo.inc');

class taxon_editor extends frog_object_editor {
 function __construct() {
  global $zoo;
  parent::__construct($zoo,'taxon');
 }

 function listing_url() {
  return 'taxon_list.php';
 }

 function edit_page_widgets() {
  return array('autosuggest','tabber');
 }

 function edit_page() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $t = $this->object;


  $this->edit_page_header();

  echo $N->top_menu();
  echo $H->tabber_start('taxon_info_tabber');

  $this->general_tab();
  if ($this->object->id) {
   $this->species_tab();
  }

  echo $H->tabber_end();
  $this->edit_page_footer();
 }
 
 function general_tab() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $t = $this->object;

  $t->load_children();
  $nc = count($t->children);
  $t->load_species();
  $ns = count($t->species);
  $cs = '';
  for ($i = 0; $i < min(5, $nc); $i++) {
   if ($i > 0) { $cs .= ', '; }
   $x = $t->children[$i];
   $cs .= '<a href="taxon_info.php?id=' . $x->id . '">' . $x->name . '</a>';
  }
  if ($nc > 5) { $cs .= ", ... ($nc total)"; }
  $pr = $t->parent_trank();
  if (! $pr && $t->parent_id) {
   $t->get_parent();
   if ($t->parent) { $pr = $t->parent->trank; }
  }

  $clash_report = '';
  if ($t->id && $t->name) {
   $clashes = $zoo->load_where('taxon', "x.name='{$t->name}' AND x.id<>{$t->id}");
   $clash_report = '';
   if ($clashes) {
    $clash_report = '<br/><br/><div class="error">Warning: There are ' . count($clashes) . 
                    " other taxa with this name:<br>";
    foreach($clashes as $c) {
     $clash_report .= '&nbsp;&nbsp;&nbsp;&nbsp;' .
                      '<a href="taxon_info.php?id=' . $c->id . '">' .
                      $c->name . ' (' . $c->trank . ')</a><br>';
    }
    $clash_report .= '</div><br>';
   }
  }
  
  echo $H->tab_start('General');
  echo $H->edged_table_start();

  echo $H->row('Name:',$H->text_input('name',$t->name));
  echo $H->row('Rank:',$H->trank_selector('trank',$t->trank));
  echo $H->row('Parent:',$H->taxon_selector('parent_id',$t->parent_id));
  echo $H->row('Children:',$cs);
  echo $H->row('Species:',$ns . ' species in this taxon');
  echo $H->edged_table_end();
  echo $H->popup_button('Wikipedia',$t->wiki_url()) . '&nbsp;&nbsp;' . 
       $H->popup_button('iNaturalist',$t->inaturalist_url());
  echo $clash_report;
  echo $H->tab_end();
 }

 function species_tab() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $t = $this->object;

  echo $H->tab_start('Species');
  echo $H->edged_table_start();
  echo $H->spacer_row(50,150,150,150,80,50);
  foreach($t->species as $s) {
  echo $H->tr($H->td($s->id) .
              $H->td($s->genus) .
              $H->td($s->species) .
              $H->td($s->common_name) .
              $H->td($s->common_group) .
              $H->popup_td('Edit', "species_info.php?id={$s->id}"));
   }
  echo $H->edged_table_end();
  echo $H->tab_end();
 }
}

(new taxon_editor())->run();
