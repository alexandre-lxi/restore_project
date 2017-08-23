<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 18:47
 */


echo "<html>";
echo "<body>";

$fname = 'pictures/'.$_GET['img'];
$size = getimagesize($fname, $info);

//print_r($info);

if(isset($info['APP13']))
{
    $iptc = iptcparse($info['APP13']);
    var_dump($iptc);
}
else {echo "noiptc";}
echo "</body>";
echo "</html>";
