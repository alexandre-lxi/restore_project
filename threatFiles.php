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
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';


//$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

//$dirsource    = '/home/ubuntu/tri/toRestore/';
$dirsource = '/home/ubuntu/restore/toAnalyse/recup_dir.8/';
$dest = '/home/ubuntu/restore/newdir';

//$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/tmp/';
//$dest = '/home/alex/Documents/IRIS/Clients/kwk/total/restore';

define("COLORSPACE_RGB", "RGB");
define("COLORSPACE_CMYK","CMYK");
define("COLORSPACE_GRAY","GRAY");
define ("zMAX_VIDEO_WIDTH", 640);
define ("zMAX_VIDEO_BITRATE", "700k");
define ("zMAX_AUDIOFLV_BITRATE", "-ar 44100 -ab 64");
define("zTOOLPATH", "/var/www/utils/");

function ztrace($log){
    echo ($log."\n");
    return true;
}

function getFileExtension($file, $withdot=false)
{
    if($withdot)
        return strtolower(substr($file, strrpos($file,".")));
    else
        return strtolower(substr($file, strrpos($file,".")+1));
}

function getId3Cover($filename, $dst)
{
    ztrace("getId3Cover : ".$filename." , ".$dst);
    $data = array();
    $getID3 = new getID3();
    $fileinfo = $getID3->analyze($filename);
    if (isset($fileinfo ['id3v2']['APIC'][0]['data'])) {
        if (function_exists("ztrace")) {
            ztrace("getId3Cover retreived cover, write it in ".$dst);
        }
        $picture = $fileinfo['id3v2']['APIC'][0]['data']; // binary image data
        $fp = fopen($dst, "w");
        fwrite($fp, $picture);
        fclose($fp);
    } else {
        ztrace("getId3Cover did NOT retreive cover, copy icon");
        $rtn = copy("/var/www/projects/total-1410-refontedam/back/ico/wav.jpg", $dst);
    }
}

function ProcessStichelbautWebImage($srcfile, $dstfile, $newsize, $w,$h,$d)
{
    global	$config;
    $profile 	= "/var/www/utils/icc/sRGBColorSpaceProfile.icm";
    $dim	 	= $newsize;
    $q			= $config['jpeg_web_quality'];
    if($w > $h)
    {
        $newh		= round($h * 0.94);
        $crop		= $w.'x'.$newh.'+0+0';
    }
    else
    {
        $neww		= round($w * 0.94);
        $crop		= $neww.'x'.$h.'+0+0';
    }
    if($newsize < 200)
    {
        ztrace("convert  ".$srcfile." -profile ".$profile." -quality ".$q."  -thumbnail ".$dim."x".$dim." ".$dstfile );
        return system("convert  ".$srcfile." -profile ".$profile." -quality ".$q."  -thumbnail ".$dim."x".$dim." ".$dstfile );
    }
    else
    {
        ztrace("convert  ".$srcfile." -profile ".$profile." -quality ".$q." -crop ".$crop." -thumbnail ".$dim."x".$dim." ".$dstfile );
        return system("convert  ".$srcfile." -profile ".$profile." -quality ".$q." -crop ".$crop." -thumbnail ".$dim."x".$dim." ".$dstfile );
    }
}

function isAudio($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "audio")!==false || getFileExtension($file)=="mp3" || getFileExtension($file)=="m4a" || getFileExtension($file)=="aif";
}

function isImage($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "image")!==false || getFileExtension($file)=="eps" || getFileExtension($file)=="tga";
}
function isVideo($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "video")!==false ||  (getFileExtension($file)=="mov") ||  (getFileExtension($file)=="flv") || (getFileExtension($file)=="wmv") || (getFileExtension($file)=="mpg") || (getFileExtension($file)=="mpeg");// <== this is because FLV are not yet recognized
}
function isText($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "text")!==false;
}
function isPdf($file)
{
    $info 	= exec("file -bi '".$file."'");
    $pdf	= strstr($info, "pdf")!==false;

    return $pdf;										// NB: .ai are known as pdf file
}
function isSwf($file)
{
    $info 	= exec("file -bi '".$file."'");
    $swf	= strstr($info, "x-shockwave-flash")!==false;

    return $swf;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function getAudioInfo($infile)
{
    $cmd = zTOOLPATH."midentify '".$infile."'";
    exec($cmd,$info);
    $data = array();
    foreach($info as $i)
    {
        $d = explode("=", $i);
        if(isset($d[0]) && isset($d[1]))
            $data[$d[0]] = $d[1];
        else if(isset($d[0]) && strlen($d[0]))
            $data[$d[0]] = 'unknown';
    }
    $data['EXTENSION'] 	= getFileExtension($infile);
    $data['FILESIZE']	= @filesize($infile);
    $data['LENGTH']		= isset($data['ID_LENGTH']) ? $data['ID_LENGTH'] : 0;
    $data['WIDTH']		= 0;
    $data['HEIGHT']		= 0;
    ztrace("getAudioInfo:\n".print_r($data,true));
    return $data;
}

function getVideoInfo($infile)
{
    /*
    ztrace("getVideoInfo");
    $info	= midentify($infile);
    $data 	= array();
    */

    $cmd = zTOOLPATH."midentify '".$infile."'";
    exec($cmd,$info);
    $data = array();
    foreach($info as $i)
    {
        $d = explode("=", $i);
        if(isset($d[0]) && isset($d[1]))
            $data[$d[0]] = $d[1];
        else if(isset($d[0]) && strlen($d[0]))
            $data[$d[0]] = 'unknown';
    }
    $data['EXTENSION'] 	= getFileExtension($infile);
    $data['FILESIZE']	= filesize($infile);
    $data['WIDTH']		= isset($data['ID_VIDEO_WIDTH']) ? $data['ID_VIDEO_WIDTH'] : 0;
    $data['HEIGHT']		= isset($data['ID_VIDEO_HEIGHT']) ? $data['ID_VIDEO_HEIGHT'] : 0;
    $data['LENGTH']		= isset($data['ID_LENGTH']) ? $data['ID_LENGTH'] : 0;
    ztrace("getVideoInfo:\n".print_r($data,true));
    return $data;
}

function getImageInfo($file)
{
    $cmd = "EXTENSION %e;";
    $cmd.= "NBLAYERS %n;";
    $cmd.= "IMAGEDEPTH %z;";
    $cmd.= "RESOLUTION %x;";
    $cmd.= "COLORSPACE %r;";
    $cmd.= "WIDTH %w;";
    $cmd.= "HEIGHT %h;";
    $cmd.= "FILESIZE %b;#snook#";
    //300 PixelsPerInch
    $time_start = microtime_float();
    ztrace("\ngetImageInfo : identify -format \"".$cmd ."\" " .$file);
    $info = exec("identify -format \"".$cmd ."\" \"" .$file."\"");
    $info = explode("#snook#",$info);		// this is to retrieve document size if multi layered (psd file...)
    $info = explode(";",$info[0]);
    $data = array();
    foreach($info as $i)
    {
        $d = explode(" ", $i);
        if(isset($d[0]) && isset($d[1]))
            $data[$d[0]] = $d[1];
        else if(isset($d[0]) && strlen($d[0]))
            $data[$d[0]] = 'unknown';
    }
    // post process fot colorspace
    if(isset($data['COLORSPACE']))
    {

        if(strstr(strtolower($data['COLORSPACE']),"cmyk")!==false)
            $data['COLORSPACE'] = 'CMYK';
        else if(strstr(strtolower($data['COLORSPACE']),"rgb")!==false)
            $data['COLORSPACE'] = 'RGB';
        else if(strstr(strtolower($data['COLORSPACE']),"gray")!==false)
            $data['COLORSPACE'] = 'GRAY';
    }
    if($data['FILESIZE']==0 || !strlen($data['FILESIZE']))
        $data['FILESIZE'] = filesize($file);
    if(!isset($data['WIDTH']))
        $data['WIDTH'] = 0;
    if(!isset($data['HEIGHT']))
        $data['HEIGHT'] = 0;
    if(!isset($data['LENGTH']))
        $data['LENGTH'] = 0;
    if(!isset($data['EXTENSION']))
        $data['EXTENSION'] = getFileExtension($file);

    $time_end = microtime_float();
    $time = $time_end - $time_start;
    ztrace("\nCatch image info in $time seconds");
    ztrace(print_r($data,true));
    return $data;
    /*	echo "\n\nNow try to get color profile name....\n";
    $time_start = microtime_float();
    echo "ICC profile is " .GetProfileName($file);
    $time_end = microtime_float();
    $time = $time_end - $time_start;
    echo "\n\nCatch profile in $time seconds\n";*/
}


function testFile($dirsource, $dest, $file, $pdo)
{
    $infile = $dirsource.$file;
    $inData = array();

    if(isImage($infile) || isPdf($infile))
    {
        $inData = getImageInfo($infile);
    }
    else if(isVideo($infile))
    {
        $inData = getVideoInfo($infile);
    }
    else if(isAudio($infile))
    {
        $inData = getAudioInfo($infile);
    }

    if(!isset($inData['FILESIZE']))
        $inData['FILESIZE'] = 0;
    if($inData['FILESIZE']==0 || !strlen($inData['FILESIZE']))
        $inData['FILESIZE'] = filesize($file);
    if(!isset($inData['WIDTH']))
        $inData['WIDTH'] = 0;
    if(!isset($inData['HEIGHT']))
        $inData['HEIGHT'] = 0;
    if(!isset($inData['LENGTH']))
        $inData['LENGTH'] = 0;
    if(!isset($inData['EXTENSION']))
        $inData['EXTENSION'] = getFileExtension($file);
    if(!isset($inData['COLORSPACE']))
        $inData['COLORSPACE'] = '';

    $fileExt = '.'.$inData['EXTENSION'];
    $fileSize = $inData['FILESIZE'];

    if (($fileExt == 'txt') || ($fileExt == 'TX?')) {
        return false;
    }

    try {
        $sql = "SELECT co.i_autocode, imf.s_fileformat
            FROM `total-refontedam`.image_file imf, `total-refontedam`.container co, `total-refontedam`.image_infofr info
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = info.i_foreigncode
              AND imf.s_fileformat = :extension
              AND i_filesize = :size";

        $req = $pdo->prepare($sql);
        $req->bindValue(':extension', strip_tags($fileExt), PDO::PARAM_STR);
        $req->bindValue(':size', $fileSize, PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $oldFile = $dirsource.$file;

        $sql = "insert INTO restore_files(fname, s_format, fsize, width, height, length, colorspace)
                    VALUES (:fname, :s_format, :fsize, :width, :height, :length, :colorspace)";
        $req = $pdo->prepare($sql);

        $req->bindValue(':fname', $oldFile, PDO::PARAM_STR);
        $req->bindValue(':s_format', $inData['EXTENSION'], PDO::PARAM_STR);
        $req->bindValue(':fsize', $inData['FILESIZE'], PDO::PARAM_INT);
        $req->bindValue(':width', $inData['WIDTH'], PDO::PARAM_INT);
        $req->bindValue(':height', $inData['HEIGHT'], PDO::PARAM_INT);
        $req->bindValue(':length', $inData['LENGTH'], PDO::PARAM_INT);
        $req->bindValue(':colorspace', $inData['COLORSPACE'], PDO::PARAM_STR);

        $req->execute();

        if (count($rows) == 1){
            $sql = "select id from restore_files where fname = :fname";
            $req = $pdo->prepare($sql);
            $req->bindValue(':fname', $oldFile, PDO::PARAM_STR);
            $req->execute();
            $id = $req->fetchAll(PDO::FETCH_OBJ);

            $sql = "INSERT INTO restore_file_co(rf_code, co_code, is_restored) VALUES (:rf_code, :co_code, :is_restored)";
            $req = $pdo->prepare($sql);

            $req->bindValue(':rf_code', $id[0]->id, PDO::PARAM_STR);
            $req->bindValue(':co_code', $rows[0]->i_autocode, PDO::PARAM_INT);
            $req->bindValue(':is_restored', true, PDO::PARAM_BOOL);

            $req->execute();

        }

    } catch (PDOException $Exception) {
        echo $Exception->getMessage().' : '.$Exception->getCode()."\n";
    }
}

function _readDir($dirsource, $dest, $pdo)
{
    $files = scandir($dirsource);

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!is_dir($dirsource.$file)) {
            testFile($dirsource, $dest, $file, $pdo);
        } else {
            _readDir($dirsource.$file.'/', $dest, $pdo);
        }
    }
}

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

_readDir($dirsource, $dest, $pdo);
