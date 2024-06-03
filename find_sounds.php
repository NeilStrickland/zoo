<?php

require_once('include/zoo.inc');

$id = (int) get_optional_parameter('id',0);

if ($id) {
 $species = $zoo->load('species',$id);
} else {
 $species = null;
}

if ($species) {
 find_sounds_for($species);
} else {
 choose_species();
}

exit;

//////////////////////////////////////////////////////////////////////

function choose_species() {
 echo "Not yet implemented";
}

//////////////////////////////////////////////////////////////////////

function find_sounds_for($species) {
 global $zoo;

 $zoo->attach_sounds();
 
 $temp_sounds_dir     = $zoo->data_dir . '/temp_sounds';
 $sounds_dir          = $zoo->data_dir . '/sounds';

 $ff = scandir($temp_sounds_dir);
 foreach ($ff as $f) {
  $f0 = $temp_sounds_dir . '/' . $f;
  if ($f != '.' && $f != '..' && is_dir($f0)) {
   if (count(scandir($f0)) == 2) {
    //    rmdir($f0);
   }
  }
 }
 
 $species_temp_dir    = $temp_sounds_dir . '/' . $species->genus . '_' . $species->species;
 $latest_dir          = $temp_sounds_dir . '/Latest';

 if (! (file_exists($species_temp_dir) && is_dir($species_temp_dir))) {
  mkdir($species_temp_dir);
 }

 file_put_contents($latest_dir . '/id.txt',$species->id);
 
 $species->load_sounds();
 $species->load_data_records();

 echo <<<HTML
<html>
<head>
 <title>{$species->binomial}</title>
 <link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
 <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
 <script type="text/javascript" src="js/find_sounds.js"></script>
</head>
<body>
 <h1>{$species->binomial} ({$species->common_name})</h1>
 <br/>

HTML;

 if ($species->sounds) {
  echo <<<HTML
 <h2>Existing sounds</h2>
 <br/>
 <table class="edged">
  <tr>

HTML;

  foreach ($species->sounds as $x) {
   echo <<<HTML
   <td>{$x->audio()}</td>
  
HTML;
  }

  echo <<<HTML
  </tr>
  <tr>
   
HTML;
   
  foreach ($species->sounds as $x) {
   echo <<<HTML
   <td>{$x->id}</td>
  
HTML;
  }
  
  echo <<<HTML
  </tr>
  <tr>
   
HTML;
   
  foreach ($species->sounds as $x) {
   
   echo <<<HTML
   <td>
    <a href="javascript:delete_sound({$x->id})">Delete</a>
   </td>
  
HTML;
  }
  
  echo <<<HTML
  </tr>
 </table>
 <br/>

HTML;
 } else {
  echo "No existing sounds<br/>";
 }

 $wmc_url = 'https://commons.wikimedia.org/wiki/Category:' .
  urlencode(str_replace(' ','_',$species->common_name));

 $wmb_url = 'https://commons.wikimedia.org/wiki/Category:' .
 urlencode($species->genus . '_' . $species->species);

 echo <<<HTML
 <div class="tabber" id="web_images_tabber">
  <div class="tabbertab">
    <h2>Wikimedia (binomial)</h2>
    <iframe id="wmb_iframe" style="width:100%; height:100%"></iframe>
  </div>
  <div class="tabbertab">
    <h2>Wikimedia (common)</h2>
    <iframe id="wmc_iframe" style="width:100%; height:100%"></iframe>
  </div>
 </div>
<script type="text/javascript" src="/js/tabber/tabber.js"></script>
<script type="text/javascript">
document.getElementById('wmb_iframe').src='$wmb_url';
document.getElementById('wmc_iframe').src='$wmc_url';
</script>
</body>
</html>

HTML;

}


?>
