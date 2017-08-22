<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 13:12
 */

include("/var/www/utils/getid3/getid3/getid3.php");

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';


define("COLORSPACE_RGB", "RGB");
define("COLORSPACE_CMYK","CMYK");
define("COLORSPACE_GRAY","GRAY");
define ("zMAX_VIDEO_WIDTH", 640);
define ("zMAX_VIDEO_BITRATE", "700k");
define ("zMAX_AUDIOFLV_BITRATE", "-ar 44100 -ab 64");
define("zTOOLPATH", "/var/www/utils/");

function ztrace($log){
//    echo ($log."\n");
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
    if(isset($_SERVER['WINDIR']))
    {
        return getFileExtension($file)=="mp3" || getFileExtension($file)=="m4a";
    }
    $info = exec("file -bi '".$file."'");
    return strstr($info, "audio")!==false || getFileExtension($file)=="mp3" || getFileExtension($file)=="m4a" || getFileExtension($file)=="aif";
}

function isImage($file)
{
    if(isset($_SERVER['WINDIR']))
    {
        return getFileExtension($file)=="jpg" || getFileExtension($file)=="tif";
    }
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
    if(isset($_SERVER['WINDIR']))
        $pdf	= (getFileExtension($file)=="pdf" );
    return $pdf;										// NB: .ai are known as pdf file
}
function isSwf($file)
{
    $info 	= exec("file -bi '".$file."'");
    $swf	= strstr($info, "x-shockwave-flash")!==false;
    if(isset($_SERVER['WINDIR']))
        $swf	= (getFileExtension($file)=="swf" );
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

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);


    $sql = "select distinct i_code
    from restore_dbl 
    where i_code not in (select i_code from restore_dbl where restore = 1)
      and i_code = 4867";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 0;
    $isTreat = array();

    foreach ($rows as $row) {
        if (true){
            $sql = "SELECT distinct db.i_code, db.oldfile, imf.i_width, imf.i_height 
            FROM `total-refontedam`.restore_dbl db, `total-refontedam`.image_file imf, `total-refontedam`.container co
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = db.i_code
              AND i_code = :icode";

            $req = $pdo->prepare($sql);
            $req->bindValue(':icode', $row->i_code, PDO::PARAM_INT);
            $req->execute();

            $fnames = $req->fetchAll(PDO::FETCH_OBJ);

            foreach ($fnames as $fname) {
                $filename = str_replace('/home/ubuntu/restore/toAnalyse/', $dirsource, $fname->oldfile);

                if(isImage($filename) || isPdf($filename))
                {
                    $inData = getImageInfo($filename);

                    if (($inData['WIDTH'] = $fname->i_widht) && ($inData['WIDTH'] = $fname->i_height)){
                        echo $row->i_code . " ". $filename;
                        print_r($inData);
                    }
                }
            }
        }

        if (false) {
            $sql = "SELECT DISTINCT imf.s_filename
            FROM `total-refontedam`.restore_dbl db, `total-refontedam`.image_file imf, `total-refontedam`.container co
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = db.i_code
              AND oldfile= :oldf";

            $req = $pdo->prepare($sql);
            $req->bindValue(':oldf', $row->oldfile, PDO::PARAM_STR);
            $req->execute();

            $filenames = $req->fetchAll(PDO::FETCH_OBJ);

            if (count($filenames) == 1) {
                $fname = $filenames[0]->s_filename;

                echo "Single file=> Code:".$row->i_code." - ".$fname."\n";

                if (array_key_exists($fname, $isTreat)) {
                    $isTreat[$fname] = $isTreat[$fname] + 1;;
                } else {
                    $isTreat[$fname] = 1;
                    $sql = "UPDATE restore_dbl SET restore = 1 WHERE oldfile = :oldf";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':oldf', $row->oldfile, PDO::PARAM_STR);
                    $req->execute();
                }
            }
        }
    }

    echo "\n\n";
    print_r($isTreat);

    echo $nb;
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}