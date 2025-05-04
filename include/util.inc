<?php

function get_gps($filename) {
 $exif = exif_read_data($filename);
 if (! $exif) { return false; }

 if (! (isset($exif["GPSLatitude"]) &&
	isset($exif["GPSLongitude"]) &&
	isset($exif["GPSLatitudeRef"]) &&
	isset($exif["GPSLongitudeRef"]))) {
  return false;
 }

 $latitude  = tidy_gps($exif["GPSLatitude"] , $exif['GPSLatitudeRef'] );
 $longitude = tidy_gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
 return [$latitude, $longitude];
}

function tidy_gps($coordinate, $hemisphere) {
 if (is_string($coordinate)) {
  $coordinate = array_map("trim", explode(",", $coordinate));
 }
 for ($i = 0; $i < 3; $i++) {
  $part = explode('/', $coordinate[$i]);
  if (count($part) == 1) {
   $coordinate[$i] = $part[0];
  } else if (count($part) == 2) {
   $coordinate[$i] = floatval($part[0])/floatval($part[1]);
  } else {
   $coordinate[$i] = 0;
  }
 }
 list($degrees, $minutes, $seconds) = $coordinate;
 $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
 return $sign * ($degrees + $minutes/60 + $seconds/3600);
}

