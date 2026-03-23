<?php

class zoo_navigator extends frog_navigator {
 public $base_url;
 
 function __construct() {
  global $zoo;

  parent::__construct($zoo);

  $this->frog_script_url = '/zoo/js/frog.js';
   
  $this->index_url = '/zoo/index.php';
  $this->base_url = '/zoo';
  $this->scripts_base_url = '/zoo/js';
  $this->styles_base_url  = '/zoo/css';

  $this->scripts_base_dir = dirname(__DIR__) . '/js';
  $this->styles_base_dir  = dirname(__DIR__) . '/css';
  
  $this->standard_scripts = array('objects_auto','zoo');
  $this->standard_styles  = array('zoo');

  $this->standard_body_scripts = array(
   'autosuggest_setup'
  );
 }

 function top_menu() {
  $html = "<div class=\"topmenu\">";

  $html .= <<<HTML
<a target="_blank" href="index.php">Home</a>
<a target="_blank" href="quiz.php">Try quiz</a>
<a target="_blank" href="quiz_group_list.php">Manage quizzes</a>
<a target="_blank" href="species_list.php">Species</a>
<a target="_blank" href="find_images.php">Images</a>
<a target="_blank" href="find_sounds.php">Sounds</a>
<a target="_blank" href="data_source_list.php">Data sources</a>

HTML
        ;

  $html .= "</div>";
  return $html;
 }
}
