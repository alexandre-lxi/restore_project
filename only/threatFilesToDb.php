<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

include "iptc.php";

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'onlyfrance';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';


//$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

//$dirsource    = '/home/ubuntu/tri/toRestore/';
$dirsource = '/home/ubuntu/restore_onlyfrance/';
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

function updateIPTC ($id, $fname, $pdo){
    $cds = array("005" => "ip_name",
        "010" => "ip_urgency",
        "015" => "ip_category",
        "020" => "ip_supcategories",
        "025" => "ip_keywords",
        "040" => "ip_instruction",
        "055" => "ip_created",
        "080" => "ip_byline",
        "085" => "ip_bylinetitle",
        "090" => "ip_city",
        "095" => "ip_state",
        "100" => "ip_country_code",
        "101" => "ip_country",
        "103" => "ip_reference",
        "105" => "ip_headline",
        "110" => "ip_credits",
        "115" => "ip_source",
        "116" => "ip_copyright",
        "120" => "ip_caption",
        "121" => "ip_captionwriter2",
        "122" => "ip_captionwriter");

    $iptc = new iptc();

    $iptc->setImg($fname);

    $liptc = $iptc->readIPTC();

    if ($liptc == false)
        return false;

    $sql = "insert into restore_file_iptc (rfid, ip_name, ip_urgency, ip_category, ip_supcategories, ip_instruction, ip_created, ip_byline, ip_bylinetitle, ip_city, ip_state, ip_country_code, ip_country, ip_reference, ip_headline, ip_credits, ip_source, ip_copyright, ip_caption, ip_captionwriter2, ip_captionwriter) 
          values (:rfid, :ip_name, :ip_urgency, :ip_category, :ip_supcategories, :ip_instruction, :ip_created, :ip_byline, :ip_bylinetitle, :ip_city, :ip_state, :ip_country_code, :ip_country, :ip_reference, :ip_headline, :ip_credits, :ip_source, :ip_copyright, :ip_caption, :ip_captionwriter2, :ip_captionwriter)";

    $req = $pdo->prepare($sql);
    $req->bindValue(':rfid', $id, PDO::PARAM_INT);

    foreach ($cds as $cd) {
        if ($cd != 'ip_keywords'){
            $req->bindValue(':'.$cd, $liptc[$cd], PDO::PARAM_STR);
        }
    }

    $req->execute();

    $sql = 'SELECT max(id) id FROM restore_file_iptc';
    $req = $pdo->prepare($sql);
    $req->execute();
    $idiptc = $req->fetchAll(PDO::FETCH_OBJ);
    $idiptc = $idiptc[0]->id;

    $sql = 'insert into restore_file_iptc_kwords (ipid, kword) values (:ipid, :kword)';
    $req = $pdo->prepare($sql);
    $req->bindValue(':ipid', $idiptc, PDO::PARAM_INT);

    foreach ($liptc['ip_keywords'] as $kword) {
        $req->bindValue(':kword', $kword, PDO::PARAM_STR);
        $req->execute();
    }

}


function testFile($dirsource, $dest, $file, $pdo)
{
    $infile = $dirsource.$file;
    //$infile = $file;
    $fileExt = getFileExtension($infile,true);

    if (($fileExt == '.txt') || ($fileExt == '.TX?') || ($fileExt == '.php') || ($fileExt == '.java')|| ($fileExt == '.h')
        || ($fileExt == '.html') || ($fileExt == '.xml') || ($fileExt == '.c') || ($fileExt == '.f') || ($fileExt == '.jsp')
        || ($fileExt == '.sh    ')) {
        return false;
    }

    $sql = "select * from restore_files2 where fname = :fname";
    $req = $pdo->prepare($sql);
    $req->bindValue(':fname', $infile, PDO::PARAM_STR);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    if (count($rows)>0){
        return false;
    }

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
        $inData['FILESIZE'] = filesize($infile);
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


    $fileSize = $inData['FILESIZE'];

    try {
        $sql = "SELECT co.i_autocode, imf.s_fileformat
            FROM image_file imf, container co, image_infofr info
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = info.i_foreigncode
              AND imf.s_fileformat = :extension
              AND i_filesize = :size";

        $req = $pdo->prepare($sql);
        $req->bindValue(':extension', strip_tags($fileExt), PDO::PARAM_STR);
        $req->bindValue(':size', $fileSize, PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $sql = "insert INTO restore_files2(fname, s_format, fsize, width, height, length, colorspace)
                    VALUES (:fname, :s_format, :fsize, :width, :height, :length, :colorspace)";
        $req = $pdo->prepare($sql);

        $req->bindValue(':fname', $infile, PDO::PARAM_STR);
        $req->bindValue(':s_format', $inData['EXTENSION'], PDO::PARAM_STR);
        $req->bindValue(':fsize', $inData['FILESIZE'], PDO::PARAM_INT);
        $req->bindValue(':width', $inData['WIDTH'], PDO::PARAM_INT);
        $req->bindValue(':height', $inData['HEIGHT'], PDO::PARAM_INT);
        $req->bindValue(':length', $inData['LENGTH'], PDO::PARAM_INT);
        $req->bindValue(':colorspace', $inData['COLORSPACE'], PDO::PARAM_STR);

        $req->execute();

        $sql = "select id from restore_files2 where fname = :fname";
        $req = $pdo->prepare($sql);
        $req->bindValue(':fname', $infile, PDO::PARAM_STR);
        $req->execute();
        $id = $req->fetchAll(PDO::FETCH_OBJ);
        $id = $id[0]->id;

        updateIPTC($id,$file, $pdo);

        if (count($rows) == 1){
            $sql = "INSERT INTO restore_file_co2(rf_code, co_code, is_restored) VALUES (:rf_code, :co_code, :is_restored)";
            $req = $pdo->prepare($sql);

            $req->bindValue(':rf_code', $id, PDO::PARAM_STR);
            $req->bindValue(':co_code', $rows[0]->i_autocode, PDO::PARAM_INT);
            $req->bindValue(':is_restored', 0, PDO::PARAM_BOOL);

            $req->execute();
        }

    } catch (PDOException $Exception) {
        echo $Exception->getMessage().' : '.$Exception->getCode()."\n";
    }
}

function _readDir($dirsource, $dest, $pdo)
{
    $files = scandir($dirsource);
    //$files = file('/home/ubuntu/lfiles.txt', FILE_IGNORE_NEW_LINES);

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

    _readDir($dirsource, $dest, $pdo);
//    $req = "truncate table restore_file_co;";
//    $pdo->exec($req);
//    $req = "truncate table restore_files;";
//    $pdo->exec($req);
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}


