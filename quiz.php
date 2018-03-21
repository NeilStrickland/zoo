<?php

require_once('zoo.inc');

$quiz_group_id =
 (int) get_optional_parameter('id',0);

$command = get_restricted_parameter('command',
				    array('choose','view','tree','try'),
                                    'try');

$g = null;

if ($quiz_group_id) {
 $g = $zoo->load('quiz_group',$quiz_group_id);
}

if (! $g) { $command = 'choose'; }

if ($command == 'try') {  
 try_quiz($g);
} else if ($command == 'view') {
 view_quiz($g);
} else if ($command == 'tree') {
 tree_view_quiz($g);
} else {
 choose_quiz();
}

//////////////////////////////////////////////////////////////////////

function choose_quiz() {
 global $zoo;

 $quizzes = $zoo->load_all('quiz_groups');

 echo <<<HTML
<html>
<head>
</head>
<body>
 <table>

HTML;

 foreach($quizzes as $q) {
  echo <<<HTML
  <tr>
   <td><a href="quiz.php?id={$q->id}">{$q->name}</a></td>
  </tr>

HTML;
 }

 echo <<<HTML
 </table>
</body>
</html>

HTML;
}

//////////////////////////////////////////////////////////////////////

function try_quiz($group) {
 global $zoo;

 $all_species = $group->load_members();

 echo <<<HTML
<html>
<head>
<link rel="stylesheet" href="/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="zoo.css" TYPE="text/css"/>
<script type="text/javascript">

all_species=[

HTML;

 foreach ($all_species as $species) {
  $x = $zoo->load('species',$species->species_id);
  if ($x) {
   $u = $x->info_url();
  } else {
   $u = '';
  }

  if ($species->images) {
   $ii = array();
   foreach($species->images as $i) {
    $ii[] = $i->id;
   } 
   $ii = '[' . implode(',',$ii) . ']';
   
   echo <<<HTML
    { "id" : {$species->id},
      "order" : "{$species->order}",
       "family" : "{$species->family}",
       "genus" : "{$species->genus}",
       "species" : "{$species->species}",
       "common_name" : "{$species->common_name}",
       "common_group" : "{$species->common_group}",
       "images" : $ii,
       "url" : "{$u}" 
    },

HTML;
  }
 }

 echo <<<HTML
null];
</script>
<script type="text/javascript" src="quiz.js"></script>
</head>
<body onload="quiz.init();tabberAutomaticOnLoad();">
<h1>{$group->name}</h1>
<div class="tabber">
 <div class="tabbertab">
  <h2>Questions</h2>

  <div style="width: 440px; height: 270px; overflow: hidden;">
  <img id="species_picture" width="400" src=""/>
  </div>
  <br/><br/>
  <div style="width: 440px; height: 50px;">
  Name: &nbsp;&nbsp;
  <input type="text" size="60" onkeypress="return quiz.handle_keypress(event)" id="answer_box"/>
  </div>
  <div id="mark_box"  style="width: 440px; height: 100px;">
  &nbsp;
  </div>
  <div  style="width: 440px; height: 120px;">
   <table>
    <tr>
     <td width="100">Correct</td>
     <td width="100" id="num_good_td">&nbsp;</td>
     <td width="100" id="percent_good_td">&nbsp;</td>
    </tr>
    <tr>
     <td width="100">Partly correct</td>
     <td width="100" id="num_ok_td">&nbsp;</td>
     <td width="100" id="percent_ok_td">&nbsp;</td>
    </tr>
    <tr>
     <td width="100">Incorrect</td>
     <td width="100" id="num_bad_td">&nbsp;</td>
     <td width="100" id="percent_bad_td">&nbsp;</td>
    </tr>
    <tr>
     <td width="100">Total</td>
     <td width="100" id="total_num_td">&nbsp;</td>
     <td width="100" id="total_percent_td">&nbsp;</td>
    </tr>
   </table>
  </div>
 </div>
 <div class="tabbertab">
  <h2>Partially correct</h2>
  <ul id="ok_ul" style="list-style-type:none">
  </ul>
 </div>
 <div class="tabbertab">
  <h2>Incorrect</h2>
  <ul id="incorrect_ul" style="list-style-type:none">
  </ul>
 </div>
</div>
<script type="text/javascript" src="/js/tabber/tabber.js"></script>
</body>
</html>

HTML;

}

//////////////////////////////////////////////////////////////////////

function view_quiz($group) {
 global $zoo;

 $all_species = $group->load_members();

 echo <<<HTML
<html>
<head>
<script type="text/javascript" src="http://localhost/js/tabber/tabber.js"></script>
<link rel="stylesheet" href="http://localhost/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="zoo.css" TYPE="text/css"/>
</head>
<body>
<h1>{$group->name}</h1>
<div class="tabber">
 <div class="tabbertab">
  <h2>Included species</h2>
  <table class="edged">

HTML;

 foreach($all_species as $s) {
  $ii = '';
  foreach($s->images as $i) {
   $ii .= $i->small_img();
  }
  echo <<<HTML
   <tr>
    <td width="200" valign="top">
     {$s->common_name}<br/>
     {$s->linked_binomial()}<br/>
     {$s->species_id}<br/>
     <a target="_blank" href="find_images.php?id={$s->species_id}">Find images</a>
    </td>
    <td>
    {$ii}
    </td>
   </tr>

HTML;

 }
 
 echo <<<HTML
  </table>
 </div>
</div>
</body>
</html>

HTML;

}

//////////////////////////////////////////////////////////////////////

function tree_view_quiz($group) {
 global $zoo;

 $all_species = $group->load_members();

 echo <<<HTML
<html>
<head>
<script type="text/javascript" src="http://localhost/js/tabber/tabber.js"></script>
<link rel="stylesheet" href="http://localhost/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="zoo.css" TYPE="text/css"/>
</head>
<body>
<h1>{$group->name}</h1>
<div class="tabber">
 <div class="tabbertab">
  <h2>Included species</h2>
  <table class="edged">

HTML;
 
 $order = '';
 $family = '';
 $genus = '';

 foreach($all_species as $s) {
  $o = ''; $f = ''; $g = '';
  if ($s->order != $order) {
   $o = $s->order;
   $order = $o;
  }
  if ($s->family != $family) {
   $f = $s->family;
   $family = $f;
  }
  if ($s->genus != $genus) {
   $g = $s->genus;
   $genus = $g;
  }

  $i = '';
  if ($s->images) {
   $i = $s->images[0]->tiny_img();
  }
  echo <<<HTML
   <tr>
    <td width="100" valign="top">$o</td>
    <td width="100" valign="top">$f</td>
    <td width="100" valign="top">$g</td>
    <td width="200" valign="top">
     {$s->linked_species()}<br/>{$s->common_name}
    </td>
    <td>$i</td>
   </tr>

HTML;

 }
 
 echo <<<HTML
  </table>
 </div>
</div>
</body>
</html>

HTML;

}

?>
