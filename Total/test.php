<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09/09/17
 * Time: 13:34
 */

$dirsource = "/var/www/projects/total-1410-refontedam/back/account/pictures/restoredir/oridir/";
$webdirsource = "/var/www/projects/total-1410-refontedam/back/account/pictures/restoredir/webdir/";
$thumbdirsource = "/var/www/projects/total-1410-refontedam/back/account/pictures/restoredir/thumbdir/";
$todir = "/var/www/projects/total-1410-refontedam/back/account/pictures/oridir/";
$towebdir = "/var/www/projects/total-1410-refontedam/back/account/pictures/webdir/";
$tothumbdir = "/var/www/projects/total-1410-refontedam/back/account/pictures/thumbdir/";

$files = scandir($dirsource);

foreach ($files as $file) {
    if ($file == '.') continue;
    if ($file == '..') continue;

    if (!is_dir($dirsource.$file)) {
        if (file_exists($todir.$file)){
            echo 'exsist: '.$file."\n";
        }else{
            echo 'not exsist: '.$file."\n";
            rename($dirsource.$file, $todir.$file);
        }
    }
}

$files = scandir($webdirsource);

foreach ($files as $file) {
    if ($file == '.') continue;
    if ($file == '..') continue;

    if (!is_dir($webdirsource.$file)) {
        if (file_exists($towebdir.$file)){
            echo 'exsist: '.$file."\n";
        }else{
            echo 'not exsist: '.$file."\n";
            rename($webdirsource.$file, $towebdir.$file);
        }
    }
}


$files = scandir($thumbdirsource);

foreach ($files as $file) {
    if ($file == '.') continue;
    if ($file == '..') continue;

    if (!is_dir($thumbdirsource.$file)) {
        if (file_exists($tothumbdir.$file)){
            echo 'exsist: '.$file."\n";
        }else{
            echo 'not exsist: '.$file."\n";
            rename($thumbdirsource.$file, $tothumbdir.$file);
        }
    }
}



