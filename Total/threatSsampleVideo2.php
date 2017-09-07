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
$dvideo = '/var/www/projects/total-1410-refontedam/back/account/pictures/video/';
//$dirsource = '/home/ubuntu/new_onlyfrance/test/';
//$dirsource    = '/home/ubuntu/new_onlyfrance/toAnalyse/';

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

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


try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT *
        FROM container co, image_file imf
        WHERE co.i_autocode = imf.i_foreigncode 
        and  s_fileformat IN ('.avi','.mpg','.mpeg','.m2v','.wmv','.mov','.flv','.mp4')
        and b_isintrash = 0
        order by 1"
    ;

    $req = $pdo->prepare($sql);
    $req->execute();
    
    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    foreach ($rows as $row) {
        $timestart=microtime(true);
        echo "Start: ".date("H:i:s", $timestart)."\n";

        $file = $dirsource.$row->i_autocode.$row->s_fileformat;

        if (!file_exists($file))
            continue;

        if (!isVideo($file))
            continue;

        $fname = $row->i_autocode.$row->s_fileformat;
        $cocode = $row->i_autocode;
        $videoDest = $dvideo.$cocode.'.mp4';

        echo $file."\n";

        if (file_exists($videoDest))
            continue;



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

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
    die();
}




