<?php

require_once('include/zoo.inc');

set_time_limit(0);

$params = get_params();

if ($params->command == 'upload_file') {
 handle_upload($params);
} else {
 choose_file_page($params);
}

exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $params = new stdClass();

 $params->command =
  get_restricted_parameter('command',
			   array('choose_file','upload_file'),
			   'choose_file');

 $params->force_wiki_download = false;

 $zoo->make_species_index();
 
 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_file_page($params) {
 global $cfg;

 echo <<<HTML
<html>
 <head>
  <title>Add species</title>
  <link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
  <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
 </head>
 <body>
  <h1>Add species to database</h1>
  <br/><br/>
  <form name="main_form" enctype="multipart/form-data" 
   action="add_species.php" method="POST" target="_self">
   <input type="hidden" name="command" value="upload_file"/>

   <div class="text">
    You can use this page to upload a list of species to add to the database.
    Each line should be a comma-separated list of strings enclosed by double 
    quotes.  The first two fields are required and should contain the genus 
    and species names.  Subsequent fields, if present, will be interpreted 
    as the common name, family, order and class.
   </div>

   <table class="edged">
    <tr>
     <td><b>File:</b></td>
     <td><input name="species_file" type="file" size="50"></td>
     <td class="command" onclick="document.main_form.submit()">Upload</td>
    </tr>
   </table>
  </form>
 </body>
</html>

HTML;

}

//////////////////////////////////////////////////////////////////////

function handle_upload($params) {
 parse_file($params);
 wiki_download($params);
 match_species($params);
 report_page($params);
}

//////////////////////////////////////////////////////////////////////

function parse_file($params) {
 global $zoo;
 
 if (! isset($_FILES['species_file'])) {
  error_page('No file specified'); exit;
 }

 $F = $_FILES['species_file'];

 if ($F['error'] != UPLOAD_ERR_OK) {
  error_page('File upload error'); exit;
 }

 $handle = fopen($F['tmp_name'],'r');

 if ($handle === FALSE) {
  error_page('Error opening uploaded file'); exit;
 }

 $params->species_to_add = array();

 while(($line = fgetcsv($handle)) !== FALSE) {
  $n = count($line);
  if ($n >= 2) {
   $x = new stdClass();
   $x->genus = $line[0];
   $x->species = $line[1];
   $x->common_name = '';
   $x->family = '';
   $x->order = '';
   $x->class = '';
   $x->wiki_family = '';
   $x->wiki_order = '';
   $x->wiki_class = '';
   
   if ($n > 2) { $x->common_name = $line[2]; }
   if ($n > 3) { $x->family      = $line[3]; }
   if ($n > 3) { $x->order       = $line[4]; }
   if ($n > 3) { $x->class       = $line[5]; }
   
   $params->species_to_add[] = $x;
  }
 }
}

//////////////////////////////////////////////////////////////////////

function wiki_download($params) {
 global $zoo;
 
 foreach($params->species_to_add as $s) {
  $s->wiki_file = $zoo->data_dir . '/wikipedia_pages/binomial/' .
                $s->genus . '_' . $s->species . '.html';

  if (file_exists($s->wiki_file)) {
   $s->wiki_html = file_get_contents($s->wiki_file);
  } else {
   $s->wiki_html = $zoo->fetch_wiki($s->genus,$s->species);

   $taxa = $zoo->extract_wiki_taxa($s->wiki_html);
   $s->wiki_family = $taxa->family;
   $s->wiki_order  = $taxa->order;
   $s->wiki_class  = $taxa->class;
  }
 }
}

//////////////////////////////////////////////////////////////////////

function match_species($params) {
 global $zoo;
 
 foreach ($params->species_to_add as $s) {
  $s->created = null;
  $s->existing = $zoo->find_species($s->genus,$s->species);

  $s->mismatch = '';
  if ($s->existing) {
   if ($s->wiki_family && ($s->wiki_family != $s->existing->family)) {
    $s->mismatch .= 'Family : ' . $s->wiki_family . ' vs ' . $s->existing->family . "\r\n";
   }
   if ($s->wiki_order && ($s->wiki_order != $s->existing->order)) {
    $s->mismatch .= 'Order : ' . $s->wiki_order . ' vs ' . $s->existing->order . "\r\n";
   }
   if ($s->wiki_class && ($s->wiki_class != $s->existing->class)) {
    $s->mismatch .= 'Class : ' . $s->wiki_class . ' vs ' . $s->existing->class . "\r\n";
   }
  }

  if (! $s->existing) {
   $s1 = $zoo->new_object('species');
   $s1->species = $s->species;
   $s1->genus   = $s->genus;
   $s1->family  = $s->family ? $s->family : $s->wiki_family;
   $s1->order   = $s->order  ? $s->order  : $s->wiki_order;
   $s1->class   = $s->class  ? $s->class  : $s->wiki_class;
   $s1->common_name = $s->common_name;
   $s1->save();
   $s->created = $s1;
  }
 }
}

//////////////////////////////////////////////////////////////////////

function report_page($params) {
 echo <<<HTML
<html>
<head>
<title>Species added</title>
<link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
</head>
<body>

<form name="main_form" enctype="multipart/form-data" 
 action="add_species.php" method="POST" target="_self">
<input type="hidden" name="command" value="upload_file"/>


<table class="edged">
 <tr>

HTML;

 foreach($params->species_to_add as $s) {
  $s1 = $s->existing ? $s->existing : $s->created;

  if ($s->existing) {
   $s->status = 'Old';
  } else {
   $s->status = 'New';
  }

  $s->has_wiki = $s->wiki_html ? 1 : 0;
  
  $h = '<tr>' .
     '<td>' . $s->genus . '</td>' .
     '<td>' . $s->species . '</td>' .
     '<td>' . $s1->family . '</td>' .
     '<td>' . $s1->order . '</td>' .
     '<td>' . $s1->class . '</td>' .
     '<td>' . $s1->common_name . '</td>' .
     '<td>' . $s->status . '</td>' .
     '<td>' . ($s->has_wiki ? 'Y' : 'N') . '</td>' .
     '</tr>' . "\r\n";

  echo $h;
 }
 
 echo <<<HTML
</table>
</form>
</body>
</html>

HTML;

}

?>
