<?php

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

$ii = image_search('Abudefduf');

echo <<<HTML
<html>
<head>

</head>
<body>
<table>

HTML;

foreach($ii as $i) {
 $thumb = $i->thumbnailUrl;
 $page = 'http://' . $i->hostPageDisplayUrl;

 echo <<<HTML
 <tr>
  <td><a target="_blank" href="$page"><img src="$thumb" width="200"/></a></td>
 </tr>

HTML;
}

echo <<<HTML
</table>
</body>
</html>

HTML;

?>