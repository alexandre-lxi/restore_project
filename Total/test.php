<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09/09/17
 * Time: 13:34
 */


$img = new Imagick();
//$img->readImage('/home/ubuntu/restore/toAnalyse/recup_dir.91/f275398656.psd');
$img->readImage('/home/ubuntu/restore/toAnalyse/recup_dir.9/f7503872.psd');
echo $img->getNumberImages();
$img->clear();
