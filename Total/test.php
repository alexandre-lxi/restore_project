<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09/09/17
 * Time: 13:34
 */


$img = new Imagick();
$img->readImage('/var/www/iris/restore/tmp/36757.jpg');

echo $img->getNumberImages();
$img->clear();
