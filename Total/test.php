<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09/09/17
 * Time: 13:34
 */

$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toImport/toRestore/';
$dest = '/var/www/projects/total-1410-refontedam/back/account/pictures/';


    $files = scandir($dirsource);

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        $fname = basename($file);
        $cocode = explode('.', $fname);
        $ext = $cocode[1];
        $cocode = $cocode[0];

        $oldFile = $dirsource.$file;
        $newFile = $dest.'oridir/'.$file;
        $thumbFile = $dest.'thumbdir/'.$cocode.'.jpg';
        $webFile = $dest.'webdir/'.$cocode.'.jpg';


        $nbu = 0;
        $nbk = 0;

        if (file_exists($newFile)){
            echo "KNOWN ".$fname."\n";
            $nbk++;
        }else{
            echo "UNKNOWN ".$fname."\n";
            $nbu++;
        }

    }


