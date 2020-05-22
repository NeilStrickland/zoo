<?php

require_once('zoo.inc');

$id = (int) get_optional_parameter('id',0);

if ($id) {
 $species = $zoo->load('species',$id);
} else {
 $species = null;
}

if ($species) {
 find_records_for($species);
} else {
 choose_species();
}

exit;

//////////////////////////////////////////////////////////////////////

function choose_species() {
 echo "Not yet implemented";
}

//////////////////////////////////////////////////////////////////////

function find_records_for($species) {
 $species->load_data_records();
 // $web_images = image_search($species->binomial);

 echo <<<HTML
<html>
<head>
 <title>{$species->binomial}</title>
 <script type="text/javascript" src="find_images.js"></script>
</head>
<body>
 <h1>{$species->binomial} ({$species->common_name})</h1>
 <br/>

HTML;

 if ($species->images) {
  echo <<<HTML
 <h2>Existing images</h2>
 <br/>
 <table class="edged">
  <tr>

HTML;

  foreach ($species->images as $i) {
   echo <<<HTML
   <td>{$i->small_img()}</td>
  
HTML;
  }

  echo <<<HTML
 </table>
 <br/>

HTML;
 }

 echo <<<HTML
 <h2>Web images</h2>
 <br/>
 <table class="edged">

HTML;

 $col = 0;
 $cols = 5;

 for($i = 0; $i < count($web_images); $i++) {
  $x = $web_images[$i];
  $thumb = $x->thumbnailUrl;
  $page = $x->hostPageDisplayUrl;

  $u = strtolower($page);
  if (strlen($u) >= 7 && substr($u,0,7) == 'http://') {
   $u = substr($u,7);
  }
  if (strlen($u) >= 8 && substr($u,0,8) == 'https://') {
   $u = substr($u,8);
  }

  $u = str_replace('commons.wikimedia.org/wiki/file','WC',$u);
  $u = str_replace('wikipedia.org','WP',$u);
  $u = str_replace('wikimedia.org','WM',$u);
  $u = str_replace('www.flickr.com/photos','FL',$u);
  $u = str_replace('flickr.com/photos','FL',$u);

  if (strlen($u) > 25) {
   $u = substr($u,0,25);
  }
  
  if (! (strlen($page) >= 4 && substr($page,0,4) == 'http')) {
   $page = 'http://' . $page;
  }

  if ($col == 0) {
   echo <<<HTML
  <tr>

HTML;
  }

  echo <<<HTML
   <td id="image_td_{$i}">
    <img id="image_img_{$i}" src="$thumb" width="200"/><br/>
    <a href="javascript:add_image({$species->id},$i)">Add</a>
   &nbsp;&nbsp;
    <a target="_blank" href="$page">Page</a><br/>
    $u
   </td>

HTML;

  $col++;
  if ($col == $cols) {
   echo <<<HTML
  </tr>

HTML;
   $col = 0;
  }
 }

 if ($col > 0) {
   echo <<<HTML
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