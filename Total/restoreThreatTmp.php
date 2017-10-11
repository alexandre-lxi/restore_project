<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 13:59
 */

include("/var/www/utils/getid3/getid3/getid3.php");

$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toImport/toRestore/';
$dest = '/var/www/projects/total-1410-refontedam/back/account/pictures/restoredir/';

define("COLORSPACE_RGB", "RGB");
define("COLORSPACE_CMYK","CMYK");
define("COLORSPACE_GRAY","GRAY");
define ("zMAX_VIDEO_WIDTH", 640);
define ("zMAX_VIDEO_BITRATE", "700k");
define ("zMAX_AUDIOFLV_BITRATE", "-ar 44100 -ab 64");
define("zTOOLPATH", "/var/www/utils/");

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

function ztrace($log){
    echo ($log."\n");
}

function convertFile($infile, $outfile, $param)
{
    global $m_processimage; // <==== stichelbaut

    if(isset($_SERVER['WINDIR']))
        $profPath = "c:/SITESWEB/utils/icc/";
    else if(file_exists("/var/www/utils/icc"))
        $profPath = "/var/www/utils/icc/";
    else if(file_exists("/home/www/utils/icc"))
        $profPath = "/home/www/utils/icc/";
    else if(file_exists("/html/utils/icc"))
        $profPath = "/html/utils/icc/";

    $sRgbProf = "sRGBColorSpaceProfile.icm";
    $cmykProf = "EuroscaleCoated.icc";
    ztrace("convertFile: " . $infile ." to ".$outfile."\n");
    //get input file information
    if(isImage($infile) || isPdf($infile))
    {
        $inData = getImageInfo($infile);
        //mail('dsnook@maload.com', 'image', print_r($inData,true));
    }
    else if(isVideo($infile))
    {
        $inData = getVideoInfo($infile);
        //mail('dsnook@maload.com', 'video', print_r($inData,true));
        //return false;
    }
    else if(isAudio($infile))
    {
        $inData = getAudioInfo($infile);
        //	mail('dsnook@maload.com', 'audio', print_r($inData,true));
    }
    else
    {
        $inData = array();
        //	mail('dsnook@maload.com', 'no', print_r($inData,true));
    }
    //create command $line prefix
    $newsize = (isset($param['newsize']) && $param['newsize']!==false) ? " -resize  ".$param['newsize']."x".$param['newsize']." " : "";
    $density = (isset($param['density']) && $param['density']!==false) ? " -density ".$param['density']." " : "";
    $quality = (isset($param['quality']) && $param['quality']!==false) ? " -quality ".$param['quality']." " : "";
    $convert = "convert ".$newsize.$quality;
    $rgbCmd  = " -profile ".$profPath.$sRgbProf;
    $cmykCmd = " -profile ".$profPath.$cmykProf." ".$rgbCmd;

    // currently only handle JPEG output file
    $fileExtension = getFileExtension($infile);

    switch($fileExtension)
    {
        case 'psd':
            // handle color space
            if($inData['COLORSPACE'] == COLORSPACE_RGB)
                $convert .= $rgbCmd;
            else if($inData['COLORSPACE'] == COLORSPACE_CMYK)
                $convert .= $cmykCmd ;
            else if($inData['COLORSPACE'] == COLORSPACE_GRAY)
                $convert .= $rgbCmd;

            if($inData['NBLAYERS']>1)
            {
                // WARNING: DON'T RESIZE PSD FILE but lower output layer (xxx-0.jpg)
                $keepconvert = $convert;
                $convert = "convert -quality 100 ".$density." ";
                // get unique tempo output file
                $tmpfile= tempnam ("./account/pictures/tmp", "imv4_");
                $convert .= " \"".$infile."\" \"".$tmpfile.".jpg\"";
                ztrace("First conversion in order to get the layer:\n".$convert);
                system($convert);
                // remove useless file
                $infile		= $tmpfile."-0.jpg";
                $convert 	= $keepconvert . " \"".$infile."\" \"".$outfile."\"";
                ztrace("Second conversion in order to resize:\n".$convert);
                system($convert);
                $i=0;
                while(true)
                {
                    if(file_exists($tmpfile."-".$i.".jpg"))
                        unlink($tmpfile."-".$i++.".jpg");
                    else
                        break;
                }
                if(file_exists($tmpfile)) unlink($tmpfile);
            }
            else
            {
                $convert .= " \"".$infile."\" \"".$outfile."\"";
                ztrace($convert);
                system($convert);
            }
            break;
        case 'tif':
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            if($m_processimage)
                ProcessStichelbautWebImage($infile, $outfile, $param['newsize'], $inData['WIDTH'],$inData['HEIGHT'], 148);
            else
            {
                //$convert .= " -flatten ";
                $convert .= $density ." -flatten -auto-orient ";
                if($inData['COLORSPACE'] == COLORSPACE_RGB)
                    $convert .= $rgbCmd;
                else if($inData['COLORSPACE'] == COLORSPACE_CMYK)
                    $convert .= $cmykCmd;
                else if($inData['COLORSPACE'] == COLORSPACE_GRAY)
                    $convert .= $rgbCmd;
                $convert .= " \"".$infile."\" \"".$outfile."\"";
                ztrace($convert);

                system($convert);
            }
            break;
        case 'eps':
        case 'pdf':
            ztrace("===== convertFile: EPS case");
            /*if($inData['WIDTH'] > $param['newsize'])
                $convert = zTOOLPATH."/nconvert/nconvert -out jpeg -o \"".$outfile."\" -ratio -resize ".$param['newsize']." 0  \"".$infile."\"";
            else if($inData['HEIGHT'] > $param['newsize'])
                $convert = zTOOLPATH."/nconvert/nconvert -out jpeg -o \"".$outfile."\" -ratio -resize 0 ".$param['newsize']." \"".$infile."\"";
            else // image is smaller: logo ??
                $convert = zTOOLPATH."/nconvert/nconvert -out jpeg -o \"".$outfile."\" -ratio \"".$infile."\"";	*/
            $keepInName 	= $infile;
            $keepOutName 	= $outfile;
            if(strstr($keepInName," ")!==false)
            {
                $infile = str_replace(" ", "", $infile);
                $outfile = str_replace(" ", "", $outfile);
                system("mv 	\"".$keepInName."\" ".$infile);
            }
            $convert = zTOOLPATH."/nconvert/nconvert -out jpeg -o \"".$outfile."\" -ratio -resize ".$param['newsize']." ".$param['newsize']."  \"".$infile."\"";
            ztrace($convert);
            system($convert);
            if(strstr($keepInName," ")!==false)
            {
                system("mv 	\"".$infile."\" \"".$keepInName."\"");
                system("mv 	\"".$outfile."\" \"".$keepOutName."\"");

                $infile 	= $keepInName;
                $outfile 	= $keepOutName;
            }
            break;
        //case 'pdf':
        case 'ai':
            ztrace("===== convertFile: PDF case");
            // need to perform a specific process if cmyk...see ghostscript
            // user needs to specify resolution
            // first solution : convert -density 300 -colorspace rgb test/pdf_cmyk_2L_0fx.pdf -strip out.jpg
            // second soluion: gs -dBATCH -dNOPAUSE -sDEVICE=jpeg -r300  -sOutputFile="out.jpg" test/pdf_cmyk_2L_0fx.pdf
            // with magick, inData['NBLAYER'] => nombre de page, pour pr�ciser la page on ajoute [npage]
            $convert .= $density ." -colorspace rgb \"".$infile."[0]\" -strip \"".$outfile."\"";
            ztrace($convert);
            system($convert);
            break;
        case 'mos': case 'hdr': case 'dng':
        $convert = zTOOLPATH."./dcraw -h -q 3 -w -c \"".$infile."\" | convert ".$newsize.$quality." - \"".$outfile."\"";
        ztrace($convert);
        system($convert);
        break;
        case 'avi':
        case 'mpg':
        case 'mpeg':
        case 'm2v':
        case 'wmv':
        case 'mov':
        case 'flv':
        case 'mp4':
            // get approx the middle of the video
            if(0)	// old method
            {
                $tc = intval($inData['ID_LENGTH']) / 2;
                $convert = "mplayer -ss ".$tc." -vo jpeg -nosound -frames 1 \"".$infile."\"";
                $curdir	= "/var/www/projects/total-1410-refontedam/restoreDir/scrypt/restore_project";
                $rndir	= "/var/www/projects/total-1410-refontedam/restoreDir/scrypt/restore_project/tmp/".rand(1000,9999);
                ztrace("try to create $rndir");
                if(!mkdir($rndir))
                    ztrace("unable to create $rndir");
                chdir($rndir);
                ztrace($convert);
                system($convert);
                if(file_exists($rndir."/00000001.jpg"))
                {
                    $rtn = rename($rndir."/00000001.jpg", $outfile);
                    ztrace("rename ".$rndir."/00000001.jpg to "."\"".$outfile."\" , res: " . $rtn);
                    chdir($curdir);
                    rmdir($rndir);
                }
            }
            else	// new method
            {
                try {
                    if ($inData['ID_VIDEO_WIDTH'] > zMAX_VIDEO_WIDTH && $inData['ID_VIDEO_HEIGHT'])    // we choose to resize if video width is larger than 400px
                    {
                        $ratio = $inData['ID_VIDEO_WIDTH'] / $inData['ID_VIDEO_HEIGHT'];
                        $inData['ID_VIDEO_WIDTH'] = zMAX_VIDEO_WIDTH;
                        $inData['ID_VIDEO_HEIGHT'] = round($inData['ID_VIDEO_WIDTH'] / $ratio);
                    }
                    if ($inData['ID_VIDEO_WIDTH'] % 2)
                        $inData['ID_VIDEO_WIDTH'] = $inData['ID_VIDEO_WIDTH'] - 1;    // only even numbers
                    if ($inData['ID_VIDEO_HEIGHT'] % 2)
                        $inData['ID_VIDEO_HEIGHT'] = $inData['ID_VIDEO_HEIGHT'] - 1;    // only even numbers
                    //$tc 		= round($inData['ID_LENGTH'] / 2);
                    if (!isset($param['capturetime']))
                        $tc = $inData['ID_LENGTH'] > 10 ? 10 : round($inData['ID_LENGTH'] / 2);
                    else
                        $tc = $param['capturetime'];

                    $convert = "ffmpeg -i \"".$infile."\" -ss ".$tc." -vframes 1 -s ".$inData['ID_VIDEO_WIDTH']."x".$inData['ID_VIDEO_HEIGHT']." out%d.jpg";
                    $curdir = "/var/www/projects/total-1410-refontedam/restoreDir/scrypt/restore_project";
                    $rndir = "/var/www/projects/total-1410-refontedam/restoreDir/scrypt/restore_project/tmp/".rand(1000, 9999);
                    ztrace("try to create $rndir");
                    if (!mkdir($rndir, 0777, true))
                        ztrace("unable to create $rndir");
                    chdir($rndir);
                    ztrace($convert);
                    system($convert);
                    if (file_exists($rndir."/out1.jpg")) {
                        $rtn = rename($rndir."/out1.jpg", $outfile);
                        ztrace("rename ".$rndir."/out1.jpg to "."\"".$outfile."\" , res: ".$rtn);
                        chdir($curdir);
                        rmdir($rndir);
                    } else
                        ztrace(" ## Unable to create thumb image from video");
                }catch (Exception $e){

                }
            }
            break;
        case 'mp3':
            getId3Cover($infile, $outfile);
            break;
        case 'wav':
        case 'm4a':
            $rtn = copy("/var/www/projects/total-1410-refontedam/back/ico/wav.jpg", $outfile);
            ztrace("copy /var/www/projects/total-1410-refontedam/back/ico/wav.jpg to ".$outfile." : ".($rtn ? 'ok' : 'failed!'));
            break;
        case 'xls':
        case 'doc':
        case 'ppt':
        case 'pptx':
        case 'docx':
        case 'dotx':
        case 'potx':
        case 'xlsx':
        case 'qxd':
        case 'swf':
        case 'exe':
        case 'zip':
            if (file_exists("/var/www/projects/total-1410-refontedam/back/ico/".$fileExtension.".jpg")) {
                $rtn = copy("/var/www/projects/total-1410-refontedam/back/ico/".$fileExtension.".jpg", $outfile);
                ztrace("copy /var/www/projects/total-1410-refontedam/back/ico/".$fileExtension.".jpg to ".$outfile." : ".($rtn ? 'ok' : 'failed!'));
            } else {
                $rtn = copy("/var/www/projects/total-1410-refontedam/back/ico/unknown.jpg", $outfile);
                ztrace("copy /var/www/projects/total-1410-refontedam/back/ico/unknown.jpg to ".$outfile." : ".($rtn ? 'ok' : 'failed!'));
            }
            break;
        default:
            //	$cmd = "convert -resize ".$param['newsize']."x".$param['newsize']." ".realpath("./ico")."/unknown.jpg ".$outfile);
            //	ztrace($convert);
            //	system($convert);
            $rtn = copy("/var/www//projects/total-1410-refontedam/back/ico/unknown.jpg", $outfile);
            ztrace("copy /var/www//projects/total-1410-refontedam/back/ico/unknown.jpg to ".$outfile." : ".($rtn?'ok':'failed!'));
            ztrace("Extension non référencée" .$fileExtension);
        /*fastMail("Extension non référencée sur ".$_SERVER['HTTP_HOST'].": $infile",
        "Fichier concerné:\n\n $infile \n\n".$fileExtension."\n\n".print_r($_SESSION), "debugmaload.com","dsnook@maload.com");	*/
    }
    return file_exists($outfile);
}


try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $files = scandir($dirsource);

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        $fname = basename($file);
        $cocode = explode('.',$fname);
        $ext = $cocode[1];
        $cocode = $cocode[0];



        $oldFile = $dirsource.$file;
        $newFile = $dest.'oridir/'.$file;
        $thumbFile = $dest.'thumbdir/'.$cocode.'.jpg';
        $webFile = $dest.'webdir/'.$cocode.'.jpg';
        $oldthumbfile =   '/var/www/projects/total-1410-refontedam/back/account/pictures/thumbdir/'.$cocode.'.jpg';

        echo $oldFile."\n";
        echo $newFile."\n";
        echo $thumbFile."\n";
        echo $webFile."\n";

        if (!copy($oldFile, $newFile)) {
            $log = "ERROR COPY#".$oldFile."=>".$newFile."\n";
            //file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
            file_put_contents('/var/www/projects/total-1410-refontedam/restoreDir/scrypt/log_restoreThreat.txt', $log, FILE_APPEND);
        }else{
            if (file_exists($newFile)){

                if (!file_exists($oldthumbfile)){
                    ztrace($newFile."=>".$thumbFile."=>".$webFile);
                    $param = array('newsize' => 600, 'quality' => 85, 'density' => '72x72');
                    $success = convertFile($newFile, $webFile, $param);        // create web image
                    $param = array('newsize' =>280, 'quality' => 85, 'density' => '72x72');
                    $success = convertFile($newFile, $thumbFile, $param);    // create thumbnail image
                    //fwrite($fp, "Item type: ".$pool->getItemType($item)."\n");


                    $sql = "insert into restore_ok values (:i_code, :hasThumb)";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':i_code', $cocode, PDO::PARAM_INT);
                    $req->bindValue(':hasThumb', 4, PDO::PARAM_INT);
                    $req->execute();
                }else{
                    ztrace($newFile."=> No convert");
                    $sql = "insert into restore_ok values (:i_code, 3)";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':i_code', $cocode, PDO::PARAM_INT);
                    $req->execute();
                }
            }
        }
    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}