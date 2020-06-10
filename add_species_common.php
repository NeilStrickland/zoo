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
  <title>Add species by common name</title>
  <link rel="stylesheet" href="/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
  <link rel="stylesheet" href="zoo.css" TYPE="text/css"/>
 </head>
 <body>
  <h1>Add species to database</h1>
  <br/><br/>
  <form name="main_form" enctype="multipart/form-data" 
   action="add_species_common.php" method="POST" target="_self">
   <input type="hidden" name="command" value="upload_file"/>

   <div class="text">
    You can use this page to upload a list of species to add to the database.
    Each line should either be a common name or a wikipedia URL (including the 
    initial http).  
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
 report_page($params);
}

//////////////////////////////////////////////////////////////////////

function parse_file($params) {
 global $zoo;
 
 if (! isset($_FILES['species_file'])) {
  $zoo->error_page('No file specified'); exit;
 }

 $F = $_FILES['species_file'];

 if ($F['error'] != UPLOAD_ERR_OK) {
  $zoo->error_page('File upload error'); exit;
 }

 $handle = fopen($F['tmp_name'],'r');

 if ($handle === FALSE) {
  $zoo->error_page('Error opening uploaded file'); exit;
 }

 $params->species_to_add = array();

 while(! feof($handle)) {
  $line = trim(fgets($handle));
  if ($line) {
   $x = new stdClass();
   if (strlen($line) >= 4 && substr($line,0,4) == 'http') {
    $x->url = $line;
    $ss = explode('/',$line);
    $s = $ss[count($ss) - 1];
    $x->wiki_key = $s;
    $s = urldecode($s);
    $s = strtr($s,'_',' ');
    $s = trim($s);
    $x->common_name = $s;
   } else {
    $x->common_name = $line;
    $s = $line;
    $s = strtr($s,' ','_');
    $s = urlencode($s);
    $x->wiki_key = $s;
    $x->url = 'https://en.wikipedia.org/wiki/' . $s;
   }
   
   $x->full_wiki_file =
    $zoo->data_dir . '/wikipedia_pages/common/' . $x->wiki_key . '.html';

   $params->species_to_add[] = $x;
  }
 }

 fclose($handle);
}

//////////////////////////////////////////////////////////////////////

function wiki_download($params) {
 global $zoo;
 
 foreach($params->species_to_add as $s) {
  if (file_exists($s->full_wiki_file)) {
   $s->wiki_html = file_get_contents($s->full_wiki_file);
  } else {
   $s->wiki_html = $zoo->fetch_wiki_common($s->wiki_key);
  }
  
  $taxa = $zoo->extract_wiki_taxa($s->wiki_html);
  $s->wiki_species = $taxa->species;
  $s->wiki_genus   = $taxa->genus;
  $s->wiki_family  = $taxa->family;
  $s->wiki_order   = $taxa->order;
  $s->wiki_class   = $taxa->class;
  
  $s->wiki_common_name = '';

  if ($s->wiki_genus && $s->wiki_species) {
   $x = $zoo->extract_wiki_common_name($s->wiki_genus,$s->wiki_species,$s->wiki_html);
   $s->wiki_common_name = $x->common_name;
  }
 }
}

//////////////////////////////////////////////////////////////////////

function report_page($params) {
 echo <<<HTML
<html>
<head>
<title>Species added</title>
<link rel="stylesheet" href="/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="zoo.css" TYPE="text/css"/>
</head>
<body>

<form name="main_form" enctype="multipart/form-data" 
 action="add_species_common.php" method="POST" target="_self">
<input type="hidden" name="command" value="upload_file"/>
<pre>

HTML;

 foreach($params->species_to_add as $s) {
  echo <<<TEXT
"{$s->wiki_genus}","{$s->wiki_species}","{$s->wiki_common_name}","{$s->wiki_family}","{$s->wiki_class}","{$s->wiki_order}"

TEXT;
 }
 
 echo <<<HTML
</pre>
</form>
</body>
</html>

HTML;

}

?>
