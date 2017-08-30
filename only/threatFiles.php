<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

include("/var/www/utils/getid3/getid3/getid3.php");

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'onlyfrance';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';


//$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

//$dirsource    = '/home/ubuntu/tri/toRestore/';
$dirsource = '/home/ubuntu/restore/toAnalyse/';


function testFile($file)
{
    $dThumb = '/home/ubuntu/restore/newdir/thumbdir/';
    $dWeb = '/home/ubuntu/restore/newdir/webdir/';

    $img = new Imagick();

    $img->readImage();
    $img->thumbnailImage()

    $img->clear();
}

function _readDir($dirsource)
{
    $files = scandir($dirsource);

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!is_dir($dirsource.$file)) {
            testFile($file);
        } else {
            _readDir($dirsource.$file.'/');
        }
    }
}

_readDir($dirsource);
