<?php

require_once('include/zoo.inc');

class data_source_editor extends frog_object_editor {
 function __construct() {
  global $zoo;
  parent::__construct($zoo,'data_source');
 }

 function associated_lists() {
  return [['name' => 'record', 'type' => 'data_record']];
 }

 function listing_url() {
  return 'data_source_list.php';
 }

 function edit_page_widgets() {
  return array('autosuggest','tabber');
 }

 function edit_page() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $q = $this->object;
  $q->load_records();

  $this->edit_page_header();

  echo $N->top_menu();

  echo $H->tabber_start('data_source_info_tabber');
  
  $this->general_tab();
  if ($this->object->id) {
   $this->records_tab();
  }
  
  echo $H->tabber_end();
  $this->edit_page_footer();
 }

 function general_tab() {
  global $zoo;
  $H = $zoo->html;
  $x = $this->object;

  echo $H->tab_start('General');
  echo $H->edged_table_start();
  echo $H->spacer_row(200,500);
  echo $H->row($H->bold('Name:'),$H->text_input('name',$x->name));
  echo $H->row($H->bold('Code:'),$H->text_input('code',$x->code));
  $explain = <<<HTML
The code is a string, typically at most three lower case letters, used to identify the data source.
HTML;
  echo $H->tr($H->td($explain,0,['colspan' => 2]));
  echo $H->row($H->bold('Default key:'),$H->text_input('default_key',$x->default_key));
  $explain = <<<HTML
The default key is not currently used.  In most cases it will be empty or equal to 'G_s'
HTML;
  echo $H->tr($H->td($explain,0,['colspan' => 2]));
  echo $H->row($H->bold('Home page:'),$H->text_input('home_page',$x->home_page,['size' => 50]));
  echo $H->row($H->bold('Species page format:'),$H->text_input('species_page_format',$x->species_page_format,['size' => 50]));
  $explain = <<<HTML
This should be a string used to construct a URL for a page giving information about a
species.  Substrings #g, #s and #i will be replaced by the genus, species and id of the
species respectively.  You can also use #G or #S for capitalised versions.
HTML;
  echo $H->tr($H->td($explain,0,['colspan' => 2]));

  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function records_tab() {
  global $zoo;
  $H = $zoo->html;
  $x = $this->object;
  
  echo $H->tab_start('Species');
  echo $H->edged_table_start();

  foreach($x->records as $r) {
   echo $H->row($x->linked_binomial($r),$r->species_common_name);
  }

  echo $H->edged_table_end();
  echo $H->tab_end();
 }
}

(new data_source_editor())->run();
