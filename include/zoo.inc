<?php

ini_set('display_errors','On');

require_once('frog/frog.inc');
require_once('zoo_class.inc');

$zoo = new zoo_class();
$zoo->name = 'zoo';
$zoo->include_dir = dirname(__FILE__);

$zoo->global_data_dir = getenv('DATADIR');
if (! $zoo->global_data_dir) {
 $zoo->global_data_dir = realpath($_SERVER['DOCUMENT_ROOT'] . '/../data');
}

$zoo->public_pictures_dir = getenv('PUBLICPICTURES');

$zoo->data_dir = $zoo->global_data_dir . "/zoo";
$zoo->db_type = 'sqlite';
$zoo->db_file = $zoo->data_dir . '/nps_zoo.db';
$zoo->connect();

$zoo->image_dir           = $zoo->data_dir . "/images";
$zoo->original_images_dir = $zoo->data_dir . '/original_images';
$zoo->temp_images_dir     = $zoo->data_dir . '/temp_images';
$zoo->sound_dir           = $zoo->data_dir . "/sounds";
$zoo->temp_sounds_dir     = $zoo->data_dir . '/temp_sounds';

$zoo->register('data_source');
$zoo->register('data_record');
$zoo->register('species','species');
$zoo->register('image');
$zoo->register('sound');
$zoo->register('quiz_group');
$zoo->register('quiz_group_membership');
$zoo->register('taxon','taxa');
$zoo->register('photo');
$zoo->register('photo_species');

$zoo->load_table('data_source');
$zoo->load_data_sources();

require_once('html.inc');
require_once('navigator.inc');

$zoo->html = new zoo_html($zoo);
$zoo->nav  = new zoo_navigator($zoo);
           
?>
