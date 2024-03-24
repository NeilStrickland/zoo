<?php

require_once('include/zoo.inc');
require_once('../include/Mobile_Detect.inc');

$quiz_group_id =
 (int) get_optional_parameter('id',0);

$command = get_restricted_parameter('command',
 array('choose','view','view_missing','tree','try','offline'),
 'try');

$g = null;

if ($quiz_group_id) {
 $g = $zoo->load('quiz_group',$quiz_group_id);
}

if (! $g) { $command = 'choose'; }

if ($command == 'try') {  
 try_quiz($g,0);
} else if ($command == 'offline') {  
 try_quiz($g,1);
} else if ($command == 'view' || $command == 'view_missing') {
 view_quiz($g,$command);
} else if ($command == 'tree') {
 tree_view_quiz($g);
} else {
 choose_quiz();
}

//////////////////////////////////////////////////////////////////////

function choose_quiz() {
 global $zoo;

 $quizzes = $zoo->load_all('quiz_groups');

 $script = <<<JS

function do_command(c,id) {
 window.open('quiz.php?id=' + id + '&command=' + c);
}

JS;
 
 $zoo->nav->header('All quizzes',array('inline_script' => $script));

 echo <<<HTML
<body>
 <h1>All quizzes</h1>
 <br/>
HTML
;

 $detect = new Mobile_Detect;

 if ($detect->isMobile()) {
 echo <<<HTML
 <table width="100%" class="edged">

HTML
  ;

 foreach($quizzes as $q) {
  echo <<<HTML
  <tr>
   <td colspan="4">{$q->name}</td>
  </tr>
  <tr>
   <td width="25%" class="command" onclick="do_command('try',{$q->id})">Try</td>
   <td width="25%" class="command" onclick="do_command('offline',{$q->id})">Offline</td>
   <td width="25%" class="command" onclick="do_command('view',{$q->id})">View</td>
   <td width="25%" class="command" onclick="do_command('view_missing',{$q->id})">Add images</td>
  </tr>

HTML
;
 }

 echo <<<HTML
 </table>

HTML;
 } else {
 echo <<<HTML
 <table class="edged">

HTML
;

 foreach($quizzes as $q) {
  echo <<<HTML
  <tr>
   <td width="300">{$q->name}</td>
   <td class="command" onclick="do_command('try',{$q->id})">Try</td>
   <td class="command" onclick="do_command('offline',{$q->id})">Offline</td>
   <td class="command" onclick="do_command('view',{$q->id})">View</td>
   <td class="command" onclick="do_command('view_missing',{$q->id})">Add images</td>
  </tr>

HTML
;
 }

 echo <<<HTML
 </table>

HTML
  ;
 }
 
 echo <<<HTML
</body>

HTML;

 $zoo->nav->footer();
}

//////////////////////////////////////////////////////////////////////

function wrap_style($css) {
 return <<<HTML
<style type="text/css">
$css
</style>

HTML;
}

function wrap_script($js) {
 return <<<HTML
<script type="text/javascript">
$js
</script>
HTML;
}

function try_quiz($group,$offline = false) {
 global $zoo;

 $all_species = $group->load_members();

 $detect = new Mobile_Detect;

 if ($offline) {
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . $group->name . '.html"');
  $tabber_css = wrap_style(file_get_contents('css/tabber.css'));
  $zoo_css    = wrap_style(file_get_contents('css/zoo.css'));
  $quiz_css   = wrap_style(file_get_contents('css/quiz_mobile.css'));
              
  $tabber_js  = wrap_script(file_get_contents('js/tabber.js'));
  $quiz_js    = wrap_script(file_get_contents('js/quiz.js'));
  
 } else {
  $tabber_css = <<<HTML
<link rel="stylesheet" href="css/tabber.css" type="text/css"/>

HTML;

  $zoo_css = <<<HTML
<link rel="stylesheet" href="css/zoo.css" type="text/css"/>

HTML;

  if ($detect->isMobile()) {
   $quiz_css = <<<HTML
<link rel="stylesheet" href="css/quiz_mobile.css" type="text/css"/>

HTML;
  } else {
  $quiz_css = <<<HTML
<link rel="stylesheet" href="css/quiz_desktop.css" type="text/css"/>

HTML;
  }

  $tabber_js = <<<HTML
<script type="text/javascript" src="js/tabber.js"></script>

HTML;

  $quiz_js = <<<HTML
<script type="text/javascript" src="js/quiz.js"></script>

HTML;
  
 }
 
 echo <<<HTML
<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$group->name}</title>
{$tabber_css}
{$zoo_css}
{$quiz_css}
<script type="text/javascript">

image_data = null;

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
    { 'id' : {$species->id},
      'order' : '{$species->order}',
       'family' : '{$species->family}',
       'genus' : '{$species->genus}',
       'species' : '{$species->species}',
       'common_name' : "{$species->common_name}",
       'common_group' : '{$species->common_group}',
       'images' : $ii,
       'url' : '{$u}' 
    },

HTML;
  }
 }

 echo <<<HTML
null];

HTML;

 if ($offline) {

  echo <<<HTML
var image_data = {
	  
HTML;

  $comma = '';
  foreach ($all_species as $species) {
   foreach($species->images as $i) {
    $d = base64_encode(file_get_contents($i->full_file_name()));
    $d = 'data: image/jpg;base64,' . $d;
    echo <<<HTML
$comma
{$i->id} : '$d'
HTML;
   $comma = ',';
   }
  }

  echo <<<HTML

};

HTML;
 }
 
 echo <<<HTML

</script>
{$quiz_js}
</head>
<body onload="quiz.init();tabberAutomaticOnLoad();">
<h1>{$group->name}</h1>
<div id="tabber_div" class="tabber">
 <div id="questions_div" class="tabbertab">
  <h2 id="questions_h2">Questions</h2>

  <div id="species_picture_div" style="width: 400px; overflow: hidden;">
   <img id="species_picture" width="400px" src=""/>
  </div>
  <br/>
  <div>
   Name: <br/>
   <input type="text" onkeypress="return quiz.handle_keypress(event)" id="answer_box"/>
   <br/>
   <table style="width:100%">
    <tr>
     <td class="command" style="width:50%; height:30px;" onclick="quiz.toggle_options()">Options</td>
     <td class="command" style="width:50%; height:30px;" onclick="quiz.show_question()">Next question</td>
    </tr>
   </table>
  </div>
  <br/>
  <div id="options_box" style="width: 100%; display: none">
  </div>
  <div id="mark_box">
  &nbsp;
  </div>
  <div  style="width: 100%;">
   <table class="edged">
    <tr>
     <td width="200">Correct</td>
     <td width="100" id="num_good_td">&nbsp;</td>
     <td width="100" id="percent_good_td">&nbsp;</td>
    </tr>
    <tr>
     <td width="200">Partly correct</td>
     <td width="100" id="num_ok_td">&nbsp;</td>
     <td width="100" id="percent_ok_td">&nbsp;</td>
    </tr>
    <tr>
     <td width="200">Incorrect</td>
     <td width="100" id="num_bad_td">&nbsp;</td>
     <td width="100" id="percent_bad_td">&nbsp;</td>
    </tr>
    <tr>
     <td width="200">Total</td>
     <td width="100" id="total_num_td">&nbsp;</td>
     <td width="100" id="total_percent_td">&nbsp;</td>
    </tr>
   </table>
   <br/><br/>
  </div>
 </div>
 <div id="ok_div" class="tabbertab">
  <h2 id="ok_h2">Partially correct</h2>
  <ul id="ok_ul" style="list-style-type:none">
  </ul>
 </div>
 <div id="incorrect_div" class="tabbertab">
  <h2 id="incorrect_h2">Incorrect</h2>
  <ul id="incorrect_ul" style="list-style-type:none">
  </ul>
 </div>
</div>
{$tabber_js}
</body>
</html>

HTML;

}

//////////////////////////////////////////////////////////////////////

function view_quiz($group,$command) {
 global $zoo;


 if ($command == 'view_missing') {
  $zoo->attach_images(1);

  $all_species = $group->load_members();

  $n0 = count($all_species);
  
  $ss = array();
  foreach($all_species as $s) {
   $no_images = 1;
   $bad_images = 0;
   foreach($s->images as $i) {
    $no_images = 0;
    if (abs(3 * $i->aspect_ratio() - 4) > 0.02) {
     $bad_images = 1;
    }
   }

   if ($no_images || $bad_images) {
    $ss[] = $s;
   }
  }

  $n1 = count($ss);
  $n2 = $n0 - $n1;

  $count_msg = <<<HTML
<br/>
This quiz has $n0 species.  Of these, $n1 have no images (or have images that need processing).  
<br/>

HTML;
  
  $all_species = $ss;
 } else {

  $count_msg = '';
  
  if ($command == 'view') {
   $zoo->attach_images(1);
  }
  $all_species = $group->load_members();
 }
 
 echo <<<HTML
<html>
<head>
<script type="text/javascript" src="js/frog.js"></script>
<script type="text/javascript" src="js/tabber.js"></script>
<link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
<link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
<script type="text/javascript">
 
function remove_membership(id) {
 var tr = document.getElementById('quiz_group_membership_tr_' + id);
 var x = frog.create_xhr();
 var u = 'ajax/delete_quiz_group_membership.php?id=' + id;
 try {
  x.open('GET',u,false);
 } catch(e) {
  alert('XHR could not connect');
 }

 try {
  x.send(null);
 } catch(e) {
  alert('XHR send failed');
 }

 tr.style.display = 'none';
}

function find_images(i,g,s) {
 window.open('find_images.php?id=' + i,'Find images');
 var u = 'https://www.google.com/search?hl=en&q=' + 
         g + '+' + s + '&btnG=Search+Images&gbv=2&tbm=isch';
 window.open(u,'Google images');
}

</script>
</head>
<body>
<h1>{$group->name}</h1>
$count_msg
<div class="tabber">
 <div class="tabbertab">
  <h2>Included species</h2>
  <table class="edged">

HTML;

 foreach($all_species as $s) {
  $ii = '';
  foreach($s->images as $i) {
   $u = $i->url();
   $v = 'fix_image.php?id=' . $i->id;
   $ii .= <<<HTML
<img width="180" src="$u" onclick="window.open('$v')"/>
HTML;
  }
  echo <<<HTML
   <tr id="quiz_group_membership_tr_{$s->id}">
    <td width="200" valign="top">
     {$s->common_name}<br/>
     {$s->linked_binomial()}<br/>
     {$s->species_id}/{$s->id}<br/>
     <a href="javascript:find_images({$s->species_id},'{$s->genus}','{$s->species}')">Find images</a><br/>
     <a href="javascript:remove_membership({$s->id})">Remove</a>
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
