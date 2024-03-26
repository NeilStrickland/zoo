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
  
  $this->standard_scripts = array('zoo','objects_auto');
  $this->standard_styles  = array('zoo');

  $this->standard_body_scripts = array(
   'autosuggest_setup'
  );
 }

 function top_menu() {
  $html = "<div class=\"topmenu\">";

  $html .= <<<HTML
<button type="button" onclick="document.location='index.php'">Home</button>
<button type="button" onclick="document.location='quiz.php'">Try quiz</button>
<button type="button" onclick="document.location='quiz_list.php'">Manage quizzes</button>
<button type="button" onclick="document.location='species_list.php'">Species</button>
<button type="button" onclick="document.location='find_images.php'">Images</button>
<button type="button" onclick="document.location='data_source_list.php'">Data sources</button>

HTML
        ;

  $html .= "</div>";
  return $html;
 }
}
