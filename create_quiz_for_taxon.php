<?php

require_once('zoo.inc');

$params = get_params();

if ($params->level && $params->taxon) {
 create_quiz($params);
 report_result($params);
}

exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $params = new stdClass();

 $params->levels = array('class','order','family','genus','common_group');
 $params->level =
  get_restricted_parameter('level',$params->levels,'family');
 $params->taxon = get_optional_parameter('taxon','');

 return $params;
}

//////////////////////////////////////////////////////////////////////

function create_quiz($params) {
 global $zoo;
 
 $w = "x.{$params->level} = '{$params->taxon}'";
 $params->species = $zoo->load_where('species',$w);
 $params->num_species = count($params->species);
 $params->quiz = $zoo->new_object('quiz_group');
 $params->quiz->name = $params->taxon;
 $params->quiz->save();

 foreach ($params->species as $s) {
  $params->quiz->add_member($s->id);
 }
}
 
//////////////////////////////////////////////////////////////////////

function report_result($params) {
  echo <<<HTML
<html>
<head>
</head>
<body>
 <h1>Quiz created</h1>
 <table>
  <tr>
   <td>ID:</td>
   <td>{$params->quiz->id}</td>
  </tr>
  <tr>
   <td>Name:</td>
   <td>{$params->quiz->name}</td>
  </tr>
  <tr>
   <td>Species:</td>
   <td>{$params->num_species}</td>
  </tr>
 </table>
 <br/><br/>
 <table>
  <tr>
   <td>Class</td>
   <td>Order</td>
   <td>Family</td>
   <td>Genus</td>
   <td>Species</td>
   <td>Common name</td>
   <td>Common group</td>
  </tr>
  
HTML;

 foreach($params->species as $s) {
  echo <<<HTML
  <tr>
   <td>{$s->class}</td>
   <td>{$s->order}</td>
   <td>{$s->family}</td>
   <td>{$s->genus}</td>
   <td>{$s->species}</td>
   <td>{$s->common_name}</td>
   <td>{$s->common_group}</td>
  </tr>

HTML;
 }

 echo <<<HTML
 </table>
</body>
</html>

HTML;

}

?>
