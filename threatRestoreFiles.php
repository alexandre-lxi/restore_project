<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26/08/17
 * Time: 13:21
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$vals[1] = array(
    'r'=> array('min'=>0,'max'=>0),
    'g'=> array('min'=>0,'max'=>0),
    'b'=> array('min'=>0,'max'=>0));
$tCols[1] = array('p1_r','p1_g','p1_b');


function getFileExtension($file, $withdot=false)
{
    if($withdot)
        return strtolower(substr($file, strrpos($file,".")));
    else
        return strtolower(substr($file, strrpos($file,".")+1));
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
function isVideoExt($file)
{
    $loff = array('avi','mpg', 'mpeg', 'm2v', 'wmv', 'mov', 'flv', 'mp4');
    $ext = getFileExtension($file);

    return in_array($ext, $loff);
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
function isOffice($file){
    $loff = array('docx', 'pptx', 'doc', 'ppt', 'xls','xlsx');
    $ext = getFileExtension($file);

    return in_array($ext, $loff);
}

function threatImage(){

}

function insertCo($req, $rfcode, $cocode){
    $req->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
    $req->bindValue(':cocode', $cocode, PDO::PARAM_INT);

    echo "Insert co rfcode:".$rfcode." cocode:".$cocode."\n";

    $req->execute();
}

function insertCoAn($req, $rfcode, $cocode, $reason, $isrestored = false){
    $req->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
    $req->bindValue(':cocode', $cocode, PDO::PARAM_INT);
    $req->bindValue(':reason', $reason, PDO::PARAM_STR);
    $req->bindValue(':isrestored', $isrestored, PDO::PARAM_BOOL);


    echo "Insert coAn rfcode:".$rfcode." cocode:".$cocode." reason: ".$reason."\n";

    $req->execute();
}

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sqlCo = "select *
                from image_file imf, container co, image_infofr info
                where co.i_autocode = imf.i_foreigncode
                  and co.i_autocode = info.i_foreigncode                  
                  and co.i_autocode not in (select co_code from restore_file_co2)
                  limit 100";
    $reqCo = $pdo->prepare($sqlCo);
    $coRows = $reqCo->fetchAll(PDO::FETCH_OBJ);

    $sqlRf = "SELECT *
      FROM restore_files
      WHERE fsize = :fsize
      and s_format = :fformat";

    $reqRf = $pdo->prepare($sqlRf);

    $nb = 0;

    $sqlInsertCo = "insert IGNORE into restore_file_co2 (rf_code, co_code, is_restored) values (:rfcode, :cocode, false)";
    $reqInsetCo = $pdo->prepare($sqlInsertCo);

    $sqlInsertCoAn = "insert IGNORE into restore_file_co_analyse2 (rf_code, co_code, is_restored, reason) values (:rfcode, :cocode, :isrestored, :reason)";
    $reqInsetCoAn = $pdo->prepare($sqlInsertCoAn);

    foreach ($coRows as $coRow){
        echo $coRow->s_filename;

        $fformat = explode('.',$coRow->s_format);
        $fformat = $fformat[1];

        $reqRf->bindValue(':fsize', $coRows->fsize, PDO::PARAM_INT);
        $reqRf->bindValue(':fformat', $fformat, PDO::PARAM_STR);
        $reqRf->execute();
        $rfRows = $reqRf->fetchAll(PDO::FETCH_OBJ);

        echo $coRow->i_autocode."\n";

        if (count($rfRows) == 1) {
            $rfRow = $rfRows[0];

            if (isImage($rfRow->fname) || isPdf($rfRow->fname)) {
                if (($coRow->i_width == $rfRow->width) && ($coRow->i_height == $rfRow->height)) {
                    insertCo($reqInsetCo, $rfRow->id, $coRow->i_autocode);
                } else {
                    $reason = 'IMAGE#Count=1#With='.$rfRow->width.'-'.$coRow->i_width.'#Height='.$rfRow->height.'-'.$coRow->i_height;
                    insertCoAn($reqInsetCoAn, $rfRow->id, $coRow->i_autocode, $reason);
                }
            }

            if (isOffice($rfRow->fname)) {
                insertCo($reqInsetCo, $rfRow->id, $coRow->i_autocode);
            }

            if (isVideo($rfRow->fname)){
                if ((ceil($coRow->f_length) == ceil($rfRow->length))) {
                    insertCo($reqInsetCo, $rfRow->id, $coRow->i_autocode);
                } else {
                    $reason = 'VIDEO#Count=1#Length='.$rfRow->length.'-'.$coRow->f_length;
                    insertCoAn($reqInsetCoAn, $rfRow->id, $coRow->i_autocode, $reason);
                }
            }
        } elseif (count($coRows) > 1) { //si multi
            echo 'CNT: '.count($coRows);

            foreach ($rfRows as $rfRow) {
                if (isImage($rfRow->fname)) {
                    $reason = 'IMAGE#Multi';
                    insertCoAn($reqInsetCoAn, $rfRow->id, $coRow->i_autocode, $reason);
                }
                if (isOffice($rfRow->fname)) {

                        $reason = 'OFFICE#Multi';
                        insertCoAn($reqInsetCoAn, $rfRow->id, $coRow->i_autocode, $reason);

                }

                if (isVideo($rfRow->fname)) {
                        $reason = 'VIDEO#Multi';
                        insertCoAn($reqInsetCoAn, $rfRow->id, $coRow->i_autocode, $reason);
                }
            }
        } else { //Si 0
            if (isVideo($coRow->s_filename)) {
                echo $coRow->s_filename;
//                $sqlCoVi = "select *
//                    from image_file imf, container co, image_infofr info
//                    where co.i_autocode = imf.i_foreigncode
//                      and co.i_autocode = info.i_foreigncode
//                      and i_width = :width
//                      and i_height = :height
//                      and ceil(f_length) = :length
//                      and s_fileformat = :fformat";
//                $reqCoVi = $pdo->prepare($sqlCoVi);
//
//                $reqCoVi->bindValue(':fformat', '.'.$row->s_format, PDO::PARAM_STR);
//                $reqCoVi->bindValue(':width', $row->width, PDO::PARAM_INT);
//                $reqCoVi->bindValue(':height', $row->height, PDO::PARAM_INT);
//                $reqCoVi->bindValue(':length', ceil($row->length), PDO::PARAM_INT);
//
//                $reqCoVi->execute();
//
//                $rowsCoVi = $reqCoVi->fetchAll(PDO::FETCH_OBJ);
//
//                if (count($rowsCoVi) == 1){
//                    $rowCoVi = $rowsCoVi[0];
//
//                    insertCo($reqInsetCo, $row->id, $rowCoVi->i_autocode);
//                    $reason = 'VIDEO#BySizeAndLength#';
//                    insertCoAn($reqInsetCoAn, $row->id, $rowCoVi->i_autocode, $reason, true);
//                }elseif (count($rowsCoVi) > 1){
//                    $reason = 'VIDEO#BySizeAndLength#Multi';
//                    foreach ($rowsCoVi as $rowCoVi) {
//                        insertCoAn($reqInsetCoAn, $row->id, $rowCoVi->i_autocode, $reason, true);
//                    }
//                }else{
//                    $reason = 'VIDEO#BySizeAndLength#0';
//                    foreach ($rowsCoVi as $rowCoVi) {
//                        insertCoAn($reqInsetCoAn, $row->id, $rowCoVi->i_autocode, $reason, true);
//                    }
//                }
            }
        }
    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}