<?php

require_once('zoo.inc');

$id = (int) get_optional_parameter('id',0);

if ($id) {
 $species = $zoo->load('species',$id);
} else {
 $species = null;
}

if ($species) {
 find_images_for($species);
} else {
 choose_species();
}

exit;

//////////////////////////////////////////////////////////////////////

function choose_species() {
 echo "Not yet implemented";
}

//////////////////////////////////////////////////////////////////////

function find_images_for($species) {
 $species->load_images();
 $web_images = image_search($species->binomial);

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

//////////////////////////////////////////////////////////////////////

function image_search($query) {
 $endpoint = 'https://api.cognitive.microsoft.com/bing/v7.0/images/search';
 $accessKey = '4d7575a7714f4279b05a2082e307e49c';

 $headers = "Ocp-Apim-Subscription-Key: $accessKey\r\n";
 $headers .= "X-MSEdge-ClientID: 32B8829893296809258289F492666943\r\n";

 $options = array ('http' => array ('header' => $headers,
				    'method' => 'GET'));

 $url = $endpoint;
 $url .= '?q=' . urlencode($query);
 $url .= '&imageType=Photo';
 $url .= '&minWidth=200';
 $url .= '&license=Share';

 $context = stream_context_create($options);
 $json = file_get_contents($url, false, $context);
 $result = json_decode($json);

 if ($result->_type != 'Images') {
  return array();
 }

 $images = $result->value;
 return $images;
}


?>