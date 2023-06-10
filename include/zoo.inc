<?php

//ini_set('display_errors','On');

$frog_dir = $_SERVER['DOCUMENT_ROOT'] . '/include/frog';
require_once($frog_dir . '/frog.inc');

require_once('zoo_class.inc');

$zoo = new zoo_class();
$zoo->name = 'zoo';
$zoo->include_dir = dirname(__FILE__);

$zoo->global_data_dir = getenv('DATADIR');
if (! $zoo->global_data_dir) {
 $zoo->global_data_dir = realpath($_SERVER['DOCUMENT_ROOT'] . '/../data');
}

$zoo->data_dir = $zoo->global_data_dir . "/zoo";
$cred = json_decode(trim(file_get_contents($zoo->data_dir . '/cred.txt')));
$zoo->db_name = isset($cred->db_name) ? $cred->db_name : 'zoo';
$db_user = isset($cred->db_user) ? $cred->db_user : 'zoo';
$db_pass = $cred->db_pass;
$zoo->connect($db_user,$db_pass);
$cred = null;
$db_user = null;
$db_pass = null;

$zoo->image_dir           = $zoo->data_dir . "/images";
$zoo->original_images_dir = $zoo->data_dir . '/original_images';
$zoo->temp_images_dir     = $zoo->data_dir . '/temp_images';

$zoo->register('data_source');
$zoo->register('data_record');
$zoo->register('species','species');
$zoo->register('image');
$zoo->register('quiz_group');
$zoo->register('quiz_group_membership');

$zoo->load_table('data_source');
$zoo->load_data_sources();

require_once('navigator.inc');

$zoo->html = new frog_html($zoo);
$zoo->nav  = new zoo_navigator($zoo);
           
?>
