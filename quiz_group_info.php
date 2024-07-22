<?php

require_once('include/zoo.inc');

class quiz_group_editor extends frog_object_editor {
 function __construct() {
  global $zoo;
  parent::__construct($zoo,'quiz_group');
 }

 function associated_lists() {
  return [['name' => 'member', 'type' => 'quiz_group_membership']];
 }

 function listing_url() {
  return 'quiz_group_list.php';
 }

 function edit_page_widgets() {
  return array('autosuggest','tabber');
 }

 function edit_page() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $q = $this->object;
  $q->load_members();

  $this->edit_page_header();

  echo $N->top_menu();

  echo $H->tabber_start('quiz_group_info_tabber');
  
  $this->general_tab();
  if ($this->object->id) {
   $this->members_tab();
  }
  
  echo $H->tabber_end();
  $this->edit_page_footer();
 }

 function general_tab() {
  global $zoo;
  $H = $zoo->html;
  $q = $this->object;

  echo $H->tab_start('General');
  echo $H->edged_table_start();

  echo $H->row('Name',$H->text_input('name',$q->name));

  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function members_tab() {
  global $zoo;
  $H = $zoo->html;
  $q = $this->object;
  
  echo $H->tab_start('Species');
  echo $H->edged_table_start();

  foreach($q->members as $m) {
   $s = $m->species;
   echo $H->row($m->linked_binomial(),$m->common_name);
  }

  echo $H->edged_table_end();
  echo $H->tab_end();
 }
}

(new quiz_group_editor())->run();
