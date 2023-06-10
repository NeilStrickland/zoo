<?php

require_once('include/zoo.inc');

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
 global $zoo;

 $zoo->attach_images();
 
 $temp_images_dir     = $zoo->data_dir . '/temp_images';
 $original_images_dir = $zoo->data_dir . '/original_images';
 $images_dir          = $zoo->data_dir . '/images';

 $ff = scandir($temp_images_dir);
 foreach ($ff as $f) {
  $f0 = $temp_images_dir . '/' . $f;
  if ($f != '.' && $f != '..' && is_dir($f0)) {
   if (count(scandir($f0)) == 2) {
    //    rmdir($f0);
   }
  }
 }
 
 $species_temp_dir    = $temp_images_dir . '/' . $species->genus . '_' . $species->species;
 $latest_dir          = $temp_images_dir . '/Latest';

 if (! (file_exists($species_temp_dir) && is_dir($species_temp_dir))) {
  mkdir($species_temp_dir);
 }

 file_put_contents($latest_dir . '/id.txt',$species->id);
 
 $species->load_images();
 $species->load_data_records();

 echo <<<HTML
<html>
<head>
 <title>{$species->binomial}</title>
 <link rel="stylesheet" href="css/tabber.css" TYPE="text/css" MEDIA="screen"/>
 <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
 <script type="text/javascript" src="js/find_images.js"></script>
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
  </tr>
  <tr>
   
HTML;
   
  foreach ($species->images as $i) {
   $i->set_size();
   $g = $i->geometry_string();
   
   echo <<<HTML
   <td>$g<br/>{$i->id}</td>
  
HTML;
  }
  
  echo <<<HTML
  </tr>
  <tr>
   
HTML;
   
  foreach ($species->images as $i) {
   
   echo <<<HTML
   <td>
    <a target="_blank" href="fix_image.php?id={$i->id}">Fix</a>&nbsp;
    <a href="javascript:delete_image({$i->id})">Delete</a>
   </td>
  
HTML;
  }
  
  echo <<<HTML
  </tr>
 </table>
 <br/>

HTML;
 } else {
  echo "No existing images<br/>";
 }

 if (isset($species->data_records_by_code['eol'])) {
  $r = $species->data_records_by_code['eol'];
  $eol_url = $r->image_url();
 } else {
  $eol_url="https://eol.org/search?q={$species->genus}+{$species->species}";
 }

 $wmc_url = 'https://commons.wikimedia.org/wiki/Category:' .
  urlencode(str_replace(' ','_',$species->common_name));

 $wmb_url = 'https://commons.wikimedia.org/wiki/Category:' .
  urlencode($species->genus . '_' . $species->species);

 $bnb_url = "https://www.bing.com/images/search?q=" .
  "{$species->genus}+{$species->species}" .
  "&qft=+filterui:license-L2_L3_L4_L5_L6_L7&FORM=IRFLTR";

 $bnc_url = "https://www.bing.com/images/search?q=" .
  "{$species->common_name}" .
  "&qft=+filterui:license-L2_L3_L4_L5_L6_L7&FORM=IRFLTR";

 $fbs_url = "https://www.fishbase.de/photos/thumbnailssummary.php?Genus=" .
         $species->genus . "&Species=" . $species->species;
 
 echo <<<HTML
 <div class="tabber" id="web_images_tabber">
  <div class="tabbertab">
   <h2>Wikimedia (binomial)</h2>
   <iframe id="wmb_iframe" style="width:100%; height:100%"></iframe>
  </div>
  <div class="tabbertab">
   <h2>Bing (binomial)</h2>
   <iframe id="bnb_iframe" style="width:100%; height:100%"></iframe>
  </div>
  <div class="tabbertab">
   <h2>Bing (common)</h2>
   <iframe id="bnc_iframe" style="width:100%; height:100%"></iframe>
  </div>
  <div class="tabbertab">
   <h2>Wikimedia (common)</h2>
   <iframe id="wmc_iframe" style="width:100%; height:100%"></iframe>
  </div>
  <div class="tabbertab">
   <h2>Fishbase</h2>
   <iframe id="fbs_iframe" style="width:100%; height:100%"></iframe>
  </div>
  <div class="tabbertab">
   <h2>EOL</h2>
   <iframe id="eol_iframe" style="width:100%; height:100%"></iframe>
  </div>
 </div>
<script type="text/javascript" src="/js/tabber/tabber.js"></script>
<script type="text/javascript">
document.getElementById('wmc_iframe').src='$wmc_url';
document.getElementById('wmb_iframe').src='$wmb_url';
document.getElementById('bnb_iframe').src='$bnb_url';
document.getElementById('bnc_iframe').src='$bnc_url';
document.getElementById('fbs_iframe').src='$fbs_url';
document.getElementById('eol_iframe').src='$eol_url';
</script>
</body>
</html>

HTML;

}


?>
