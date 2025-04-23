<?php

require_once('include/zoo.inc');

class species_editor extends frog_object_editor {
 function __construct() {
  global $zoo;
  parent::__construct($zoo,'species');
 }

 function associated_lists() {
  return [
   ['name'=>'quiz_group', 'type'=>'quiz_group_membership'],
   ['name'=>'image', 'type'=>'image'],
   ['name'=>'sound', 'type'=>'sound'],
   ['name'=>'data_record', 'type'=>'data_record']
  ];
 }

 function load_from_request() {
  $s = $this->object;
  $s->fill_from_request('',0,['genus','species','common_name','common_group']);
  foreach(['family','order','class','phylum','kingdom'] as $x) {
   $xd = $x . '_display';
   if (isset($_REQUEST[$xd])) {
    $s->$x = $_REQUEST[$xd];
   }
  }
 }

 function listing_url() {
  return 'species_list.php';
 }

 function edit_page_widgets() {
  return array('autosuggest','tabber');
 }

 function edit_page() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $s = $this->object;
  $s->load_associated();

  $this->edit_page_header();

  echo $N->top_menu();

  echo $H->tabber_start('species_info_tabber');
  
  $this->general_tab();
  if ($this->object->id) {
   $this->images_tab();
   $this->sounds_tab();
   $this->data_records_tab();
   $this->quiz_groups_tab();
   $this->photos_tab();
  }
  
  echo $H->tabber_end();

  echo <<<HTML
<script type="text/javascript">
 init();
</script>
HTML;

  $this->edit_page_footer();
 }

 function general_tab() {
  global $zoo;
  $H = $zoo->html;
  $s = $this->object;

  echo $H->tab_start('General');
  echo $H->edged_table_start();

  echo $H->row('Genus:',$H->text_input('genus',$s->genus));
  echo $H->row('Species:',$H->text_input('species',$s->species));
  echo $H->row('Common name:',$H->text_input('common_name',$s->common_name));
  echo $H->row('Common group:',$H->text_input('common_group',$s->common_group));
  echo $H->row('Family',$H->family_selector('family',$s->family));
  echo $H->row('Order',$H->order_selector('order',$s->order));
  echo $H->row('Class',$H->class_selector('class',$s->class));
  echo $H->row('Phylum',$H->phylum_selector('phylum',$s->phylum));
  echo $H->row('Kingdom',$H->kingdom_selector('kingdom',$s->kingdom));
  echo $H->edged_table_end();

  echo <<<HTML
<button id="search_button">Search</button>

HTML;

echo $H->tab_end();
 }

 function images_tab() {
  global $zoo;
  $H = $zoo->html;
  $s = $this->object;

  echo $H->tab_start('Images');
  echo $H->popup_button('Find more images','find_images.php?id=' . $s->id);
  echo $H->edged_table_start();
  echo "<tr>";
  foreach ($s->images as $x) {
   echo <<<HTML
  <td><img src="{$x->url()}" width="120" onclick="window.open('fix_image.php?id={$x->id}')"/></td>

HTML;
  }
  echo "</tr><tr>";
  foreach ($s->images as $x) {
   echo $H->td($x->id . '<br/>' . $x->geometry_string());
  }
  echo "</tr>";
  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function sounds_tab() {
  global $zoo;
  $H = $zoo->html;
  $s = $this->object;

  echo $H->tab_start('Sounds');
  echo $H->popup_button('Find more sounds','find_sounds.php?id=' . $s->id);
  echo $H->edged_table_start();
  foreach ($s->sounds as $x) {
   echo $H->row($x->id, $x->audio());
  }
  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function data_records_tab() {
  global $zoo;
  $H = $zoo->html;
  $s = $this->object;

  echo $H->tab_start('Data records');
  echo $H->edged_table_start();
  echo $H->spacer_row(50,120,250);
  foreach ($s->data_records as $x) {
   echo $H->row($x->id, $x->data_source_name, $x->linked_external_id());
  }
  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function quiz_groups_tab() {
  global $zoo;
  $H = $zoo->html;
  $s = $this->object;

  echo $H->tab_start('Quizzes');
  echo $H->edged_table_start();
  echo $H->spacer_row(50,250);
  foreach ($s->quiz_groups as $x) {
   echo $H->row($x->id, $x->quiz_group_name);
  }
  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function photos_tab() {
  global $zoo;
  $H = $zoo->html;
  $s = $this->object;

  echo $H->tab_start('Photos');
  foreach ($s->photos as $x) {
   $t = $x->photo_dir . '/' . $x->photo_file_name;
   if ($x->photo_description) { $t .= ': ' . $x->photo_description; }
   if ($x->photo_location) { $t .= ', ' . $x->photo_location; }

   echo $x->img() . '<br/>' . $t . '<br/><br/>' . PHP_EOL;
  }
  echo $H->tab_end();
 }

}

(new species_editor())->run();
