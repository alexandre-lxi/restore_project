<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 18:47
 */

include 'iptc.php';

echo "<html>";
echo "<body>";



$fname = 'pictures/'.$_GET['img'];
$size = getimagesize($fname, $info);

$iptc = new iptc();
$iptc->setImg($fname);

$liptc = $iptc->readIPTC();

print_r($liptc);

echo "</body>";
echo "</html>";
