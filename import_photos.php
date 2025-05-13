<?php

require_once('include/zoo.inc');

$params = get_params();
if ($params->command == 'select_file') {
 select_file_page($params);
} else if ($params->command == 'import') {
 handle_upload($params);
 do_import($params);
 report_page($params);
} else {
 error_page("Invalid command: $params->command");
}

exit;

function get_params() {
 global $zoo;  
 $params = new stdClass();
 $params->col_types = ['','dir','file_name','date','location','lat','lng','description'];

 $params->command = get_restricted_parameter('command', ['select_file','prepare_import','import'], 'select_file');
 $species = $zoo->load_all('species');
 $params->species_by_id = make_index($species,'id');
 $params->species_by_binomial = make_index($species,'binomial');
 $params->dir = get_optional_parameter('dir','');
 if ($params->dir) {
  $params->full_dir = $zoo->public_pictures_dir . '/' . $params->dir;
  $photos = $zoo->load_where('photo',"x.dir='{$params->dir}'");
  $params->photos_by_file_name = make_index($photos,'file_name'); 
 } else {
  $params->full_dir = '';
  $params->photos_by_file_name = [];
 }
 
 $params->dirs = [''];
 if ($zoo->public_pictures_dir && is_dir($zoo->public_pictures_dir)) {
  foreach(scandir($zoo->public_pictures_dir) as $d) {
   $fd = $zoo->public_pictures_dir . '/' . $d;
   if ($d != '.' && $d != '..' && is_dir($fd)) {
    $params->dirs[] = $d;
   }
  }
 }

 $params->col_type = [];
 for ($i = 0; $i < 10; $i++) {
  $params->col_type[$i] = get_restricted_parameter('col_type' . $i, $params->col_types, '');
 }

 return $params;
}

function select_file_page($params) {
 global $zoo;
 $N = $zoo->nav;
 $H = $zoo->html;

 $N->header('Import photos',['widgets' => ['tabber','autosuggest']]);
 echo $N->top_menu();

 $s = $H->selector('dir',$params->dirs,'');
 $i = $H->file_input('photos_file',['size' => 50]);
 $b = $H->command_button('Upload', 'document.main_form.submit();');

 echo <<<HTML
 <h1>Import photos</h1>
 <div class="text">
  This page can be used to create database records of photos taken by us.
  It is assumed that the relevant photos are contained in a subdirectory 
  of the public pictures directory on the machine on which this script
  is running.  Usually a file should be uploaded that contains the file
  names of the relevant photos together with descriptions.  In cases 
  where the description is the binomial name of a species that already
  exists in the database, the photo will be associated with that species.
  Photos that do not contain species should usually be omitted from the
  index file.  The index file should be in CSV format.  Each row 
  should contain a file name, a description, and optionally a location.
  (Further information will be extracted from the EXIF data in the photo.)
 </div>
 <br/>
<form name="main_form" action="import_photos.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="command" value="import">
 <table class="edged">
  <tr>
   <td>Photo directory:</td>
   <td>$s</td>
  </tr>
  <tr>
   <td>Index file:</td>
   <td>$i</td>
  </tr>
  <tr>
   <td></td>
   <td>$b</td>
  </tr>
</form>

HTML;

 $N->footer();
}

function handle_upload($params) {
 global $zoo;

 if (!isset($_FILES['photos_file'])) {
  error_page("No file uploaded");
  exit;
 }

 $f = $zoo->data_dir . '/photos.csv';
 move_uploaded_file($_FILES['photos_file']['tmp_name'], $f);
}

function do_import($params) {
 global $zoo;

 $f = $zoo->data_dir . '/photos.csv';
 $fh = fopen($f,'r');
 if (!$fh) {
  error_page("Could not open file");
  exit;
 }

 $params->old_photos = [];
 $params->updated_photos = [];
 $params->new_photos = [];

 while(($x = fgetcsv($fh)) !== FALSE) {
  if (count($x) < 2) {
   continue;
  }

  $p = $zoo->new_object('photo');
  $p->dir = $params->dir;
  $p->file_name = $x[0];
  $p->description = $x[1];
  if (count($x) > 2) {
   $p->location = $x[2];
  }

  if (isset($params->photos_by_file_name[$p->file_name])) {
   $p0 = $params->photos_by_file_name[$p->file_name];
   if (($p->location && ($p->location != $p0->location))  || 
       ($p->description && ($p->description != $p0->description))) {
    $p0->location = $p->location;
    $p0->description = $p->description;
    $p0->save();
    $p = $p0;
    $p->parse_species();
    $params->updated_photos[] = $p;
   } else {
    $p = $p0;
    $params->old_photos[] = $p;
   }
  } else {
   $p->save();
   $p->parse_species();
   $params->new_photos[] = $p;
  }
 }
}

function report_page($params) {
 global $zoo;
 $N = $zoo->nav;
 $H = $zoo->html;

 $N->header('Import photos',['widgets' => ['tabber','autosuggest']]);
 echo $N->top_menu();

 echo <<<HTML
 <h1>Import report</h1>
 <br/>
Directory: $params->dir
<br/>

HTML;

 if ($params->new_photos) {
  echo <<<HTML
<h2>New photos</h2>
<br/>
HTML;

  echo $H->edged_table_start();
  echo $H->tr($H->td('File name') . $H->td('Description'));
  foreach($params->new_photos as $p) {
   echo $H->tr($H->td($p->file_name) . $H->td($p->description));
  }
  echo $H->edged_table_end();
 } else {
  echo "<p>No new photos</p>";
 }

 if ($params->updated_photos) {
  echo <<<HTML
<h2>Updated photos</h2>
<br/>
HTML;

  echo $H->edged_table_start();
  echo $H->tr($H->td('File name') . $H->td('Description'));
  foreach($params->updated_photos as $p) {
   echo $H->tr($H->td($p->file_name) . $H->td($p->description));
  }
  echo $H->edged_table_end();
 }

 if ($params->old_photos) {
  echo <<<HTML
<h2>Unchanged photos</h2>
<br/>
HTML;

  echo $H->edged_table_start();
  echo $H->tr($H->td('File name') . $H->td('Description'));
  foreach($params->old_photos as $p) {
   echo $H->tr($H->td($p->file_name) . $H->td($p->description));
  }
  echo $H->edged_table_end();
 }

 $N->footer();
}