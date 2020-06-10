<?php

class zoo_navigator extends frog_navigator {
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
 }
}
