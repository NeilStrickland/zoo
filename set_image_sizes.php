<?php

require_once('include/zoo.inc');

$images = $zoo->load_all('images');

$n = count($images);

echo <<<HTML
<table>

HTML;

for($i = 0; $i < $n; $i++) {
 $x = $images[$i];
 if ($x->width && $x->height) {
  continue;
 }
 $x->set_size(1);
 $img = $x->tiny_img();
 $ar = $x->aspect_ratio();
 
 echo <<<HTML
 <tr>
  <td>{$x->id}</td>
  <td>$img</td>
  <td>{$x->width}</td>
  <td>{$x->height}</td>
  <td>{$ar}</td>
 </tr>
  
HTML;
}

echo <<<HTML
</table>

HTML;
 
?>
