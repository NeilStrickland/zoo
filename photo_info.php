<?php

require_once('include/zoo.inc');

class photo_editor extends frog_object_editor {
 function __construct() {
  global $zoo;
  parent::__construct($zoo,'photo');
 }

 function associated_lists() {
  return [
   ['name'=>'species', 'plural'=>'species', 'type'=>'photo_species']
  ];
 }

 function edit_page_widgets() {
  return array('autosuggest','tabber');
 }

 function edit_page() {
  global $zoo;
  
  $H = $zoo->html;
  $N = $zoo->nav;
  $p = $this->object;

  $this->edit_page_header();

  echo $N->top_menu();

  echo $H->tabber_start('photo_info_tabber');
  
  $this->general_tab();
  if ($this->object->id) {
   $this->species_tab();
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
  $p = $this->object;
  $u = $p->url();
  echo $H->tab_start('General');
  
  echo <<<HTML
<br/><img src="$u" width="800"/><br/>

HTML;

  echo $H->edged_table_start();

  echo $H->row('Directory:',$H->text_input('dir',$p->dir));
  echo $H->row('File:',$H->text_input('dir',$p->file_name));
  echo $H->row('Date:',$H->text_input('date',$p->date));
  echo $H->row('Location:',$H->text_input('location',$p->location));
  echo $H->row('Latitude:',$H->text_input('lat',$p->lat));
  echo $H->row('Longitude:',$H->text_input('lng',$p->lng));
  echo $H->row('Description:',$H->text_input('description',$p->description));
  echo $H->row('Ignore:', $H->checkbox('ignore',$p->ignore));

  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function species_tab() {
  global $zoo;
  $H = $zoo->html;
  $p = $this->object;
  $p->extend_list('species',2);

  echo $H->tab_start('Species');
  echo $H->edged_table_start();

  foreach($p->species as $s) {
   echo $this->species_row($s);
  }
  echo $H->edged_table_end();
  echo $H->tab_end();
 }

 function species_row($s) {
  global $zoo;
  $H = $zoo->html;
  $p = $this->object;
  $a = $s->set_prefix(); 
  $h = $H->td($H->species_selector($a . '_species_id', $s->species_id)) . 
       $H->td($s->new_object_marker . $s->remover_box());
  return $H->tr($h);
 }
}

(new photo_editor())->run();
