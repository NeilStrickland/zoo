<?php

//header('Content-type: text/plain');

require_once('include/zoo.inc');

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

 $params->quiz_id = (int) get_optional_parameter('quiz_id',0);
 if ($params->quiz_id) {
  $params->quiz = $zoo->load('quiz_group',$params->quiz_id);
 } else {
  $params->quiz = null;
 }

 if (! $params->quiz) {
  $params->quiz_id = 0;
  $params->command = 'choose_file';
 }

 if (! isset($_FILES['species_file'])) {
  $params->command = 'choose_file';
 }

 $zoo->make_species_index();
 
 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_file_page($params) {
 global $zoo;

 $quizzes = $zoo->load_all('quiz_groups');
 $html = new frog_html($zoo);

 $opts = array('empty_option' => true, 'mode' => 'indirect','index' => 'id','display' => 'name');
 $sel = $html->selector('quiz_id',$quizzes,$params->quiz_id,$opts);
 
 echo <<<HTML
<html>
 <head>
  <title>Add to quiz</title>
  <link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
  <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
 </head>
 <body>
  <h1>Add species to quiz</h1>
  <br/><br/>
  <form name="main_form" enctype="multipart/form-data" 
   action="add_to_quiz.php" method="POST" target="_self">
   <input type="hidden" name="command" value="upload_file"/>
   <div class="text">
    You can use this page to upload a list of species to add to the specified quiz.
    Each line should be a comma-separated list of strings enclosed by double 
    quotes.  The first two fields are required and should contain the genus 
    and species names.  Any additional fields will be ignored.
   </div>

   <table class="edged">
    <tr>
     <td><b>Quiz:</b></td>
     <td>$sel</td>
    </tr>
    <tr>
     <td><b>File:</b></td>
     <td><input name="species_file" type="file" size="50"></td>
    </tr>
    <tr>
     <td colspan="2" class="command" onclick="document.main_form.submit()">Upload</td>
    </tr>
   </table>
  </form>
 </body>
</html>

HTML;

}

function species_table($species) {
 echo <<<HTML
<table>

HTML
  ;

 foreach ($species as $s) {
  echo <<<HTML
 <tr>
  <td style="width:300px">{$s->genus} {$s->species}</td>
 </tr>

HTML
   ;

 }
 
 echo <<<HTML
</table>

HTML
  ;
}

function handle_upload($params) {

 $quiz = $params->quiz;
 
 $quiz->load_members();

 if (! isset($_FILES['species_file'])) {
  $zoo->error_page('No file specified'); exit;
 }

 $F = $_FILES['species_file'];

 if ($F['error'] != UPLOAD_ERR_OK) {
  $zoo->error_page('File upload error'); exit;
 }

 $x = $quiz->add_members_from_file($F['tmp_name']);

 echo <<<HTML
<html>
<head>
<title>Species added</title>
<link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
</head>
<body>

<h1>Adding species to quiz: {$quiz->name}</h1>

<br/>
<h2>Newly added</h2>

HTML
  ;

 species_table($x->newly_added);

 if ($x->already_present) {
  echo <<<HTML
<br/>
<h2>Already present</h2>

HTML
   ;

  species_table($x->already_present);
 }

 if ($x->not_found) {
  echo <<<HTML
<br/>
<h2>Not found</h2>

HTML
   ;

  species_table($x->not_found);
 }

 echo <<<HTML
</body>
</html>

HTML
  ;
}
