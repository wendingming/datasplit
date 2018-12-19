<?php
$bigImgPath = "./public/static/images/13039_ji4cy019.png";
$qCodePath = "./public/static/images/nopic.jpg";

$bigImg = imagecreatefromstring(file_get_contents($bigImgPath));
$qCodeImg = imagecreatefromstring(file_get_contents($qCodePath));

list($bigImgWidth, $bigImgHight, $bigImgType) = getimagesize($bigImgPath);
list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qCodePath);
$l = $bigImgWidth/2 - $qCodeWidth/2;
$t = $bigImgHight/2 - $qCodeHight/2;
//echo($l .'<br>' . $t);die;

imagecopymerge($bigImg, $qCodeImg, $l, $t, 0, 0, $qCodeWidth, $qCodeHight, 100);

list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);

imagejpeg($bigImg,'./public/static/images/test' .time(). '.jpg');

?>