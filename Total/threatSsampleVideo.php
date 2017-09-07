<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

include "videoencoder.php";

//$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

//$dirsource    = '/home/ubuntu/tri/toRestore/';
$dirsource = '/var/www/projects/total-1410-refontedam/back/account/pictures/oridir/';
//$dirsource = '/home/ubuntu/new_onlyfrance/test/';
//$dirsource    = '/home/ubuntu/new_onlyfrance/toAnalyse/';

function getFileExtension($file, $withdot=false)
{
    if($withdot)
        return strtolower(substr($file, strrpos($file,".")));
    else
        return strtolower(substr($file, strrpos($file,".")+1));
}

function isImage($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "image")!==false || getFileExtension($file)=="eps" || getFileExtension($file)=="tga";
}

function isVideo($file){
    $info = exec("file -bi '".$file."'");
    return (strstr($info, "video") !== false)
        || (getFileExtension($file) == "mov")
        || (getFileExtension($file) == "flv")
        || (getFileExtension($file) == "wmv")
        || (getFileExtension($file) == "mpg")
        || (getFileExtension($file) == "mpeg");
}


function getTmpFilePath($extension)
{
    return "/var/www/projects/total-1410-refontedam/back/account/pictures/tmp/".md5(time().rand()).".".$extension;
}

function testFile($file)
{
    $dvideo = '/var/www/projects/total-1410-refontedam/back/account/pictures/video/';

    $timestart=microtime(true);
    echo "Start: ".date("H:i:s", $timestart)."\n";

    if (!isVideo($file))
        return false;

    $fname = basename($file);
    $cocode = explode('.',$fname);
    $cocode = $cocode[0];
    $videoDest = $dvideo.$cocode.'.mp4';

    echo $file."\n";

    if (file_exists($videoDest))
        return false;



    $pdest = getTmpFilePath("mp4");
    $param['s_format'] = 'mp4';
    $param['s_vbrate'] = '1200k';
    $param['s_abrate'] = '128k';
    $param['s_size'] = '960x540';
    $enc = new videoEncoder();
    $param['s_file'] = $file;
    $param['s_output'] = $pdest;
    $enc->encode($param['s_file'], $param['s_output'], $param);
    rename($pdest, $videoDest);

    echo "      Encode: ".date("H:i:s", microtime(true)- $timestart)."\n";


}

function _readDir($dirsource)
{
    $files = scandir($dirsource);
    $files = array('59956.mp4');

    $nb = 0;

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!file_exists($dirsource.$file))
            continue;

        if (!is_dir($dirsource.$file)) {
            testFile($dirsource.$file);
        } else {
            _readDir($dirsource.$file.'/');
        }

        $nb++;

        if ($nb>1)
            break;
    }
}

_readDir($dirsource);
