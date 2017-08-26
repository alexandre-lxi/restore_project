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

function getFileExtension($file, $withdot = false)
{
    if ($withdot)
        return strtolower(substr($file, strrpos($file, ".")));
    else
        return strtolower(substr($file, strrpos($file, ".") + 1));
}

function isAudio($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "audio") !== false || getFileExtension($file) == "mp3" || getFileExtension($file) == "m4a" || getFileExtension($file) == "aif";
}

function isImage($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "image") !== false || getFileExtension($file) == "eps" || getFileExtension($file) == "tga";
}

function isVideo($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "video") !== false || (getFileExtension($file) == "mov") || (getFileExtension($file) == "flv") || (getFileExtension($file) == "wmv") || (getFileExtension($file) == "mpg") || (getFileExtension($file) == "mpeg");// <== this is because FLV are not yet recognized
}

function isText($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "text") !== false;
}

function isPdf($file)
{
    $info = exec("file -bi '".$file."'");
    $pdf = strstr($info, "pdf") !== false;

    return $pdf;                                        // NB: .ai are known as pdf file
}

function isSwf($file)
{
    $info = exec("file -bi '".$file."'");
    $swf = strstr($info, "x-shockwave-flash") !== false;

    return $swf;
}

function isOffice($file)
{
    $loff = array('docx', 'pptx', 'doc', 'ppt', 'xls', 'xlsx');
    $ext = getFileExtension($file);

    return in_array($ext, $loff);
}

function controlPixels($rfcode, $cocode){
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $tCols[1] = array('p1_r','p1_g','p1_b');
    for ($i =1; $i <= 10; $i++){
        $tCols[$i] = array('p'.$i.'_r','p'.$i.'_g','p'.$i.'_b');
    }

    try {
        echo "controlPixels rf: ".$rfcode." co: ".$cocode."\n";

        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "select * from restore_nfile_colors where rfcode = :rfcode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':rfcode',$rfcode,PDO::PARAM_INT);
        $req->execute();
        $rfvals = $req->fetchAll(PDO::FETCH_OBJ);

        $sql = "select * from restore_ofile_colors where icode= :cocode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':cocode',$cocode,PDO::PARAM_INT);
        $req->execute();
        $covals = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 0;

        if ((count($rfvals)==0) || (count($covals)==0))
            return false;

        $rfvals = $rfvals[0];
        $covals = $covals[0];

        for ($i=1; $i<=10; $i++){
            $rcol = $tCols[$i][0];
            $r_rfval = $rfvals->$rcol;
            $r_coval = $covals->$rcol;

            $gcol = $tCols[$i][1];
            $g_rfval = $rfvals->$gcol;
            $g_coval = $covals->$gcol;


            $bcol = $tCols[$i][2];
            $b_rfval = $rfvals->$bcol;
            $b_coval = $covals->$bcol;

            echo $r_rfval ."<=>".$r_coval ."\n";
            echo $g_rfval ."<=>".$g_coval ."\n";
            echo $b_rfval ."<=>".$b_coval ."\n";

            if (($r_coval == $r_rfval) && ($b_coval == $b_rfval) && ($g_coval == $g_rfval))
                $nb ++;
        }

        echo $nb;

        return $nb >= 9;

    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return false;
    }
}

function insertCo($rfcode, $cocode)
{

    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT * FROM from restore_file_co2 WHERE co_code = :cocode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $req->execute();
        $vals = $req->fetchAll(PDO::FETCH_OBJ);
        if (count($vals) == 0) {


            $sqlInsertCo = "INSERT IGNORE INTO restore_file_co2 (rf_code, co_code, is_restored) VALUES (:rfcode, :cocode, FALSE)";
            $reqInsetCo = $pdo->prepare($sqlInsertCo);

            $reqInsetCo->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
            $reqInsetCo->bindValue(':cocode', $cocode, PDO::PARAM_INT);
            $reqInsetCo->execute();
        }

        echo "Insert co rfcode:".$rfcode." cocode:".$cocode."\n";
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

function insertCoAn($rfcode, $cocode, $reason, $isrestored = false)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sqlInsertCoAn = "INSERT IGNORE INTO restore_file_co_analyse2 (rf_code, co_code, is_restored, reason) VALUES (:rfcode, :cocode, :isrestored, :reason)";
        $reqInsetCoAn = $pdo->prepare($sqlInsertCoAn);

        $reqInsetCoAn->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
        $reqInsetCoAn->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $reqInsetCoAn->bindValue(':reason', $reason, PDO::PARAM_STR);
        $reqInsetCoAn->bindValue(':isrestored', $isrestored, PDO::PARAM_INT);

        echo "Insert coAn rfcode:".$rfcode." cocode:".$cocode." reason: ".$reason."\n";

        $reqInsetCoAn->execute();
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

function threatImage()
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT *
        FROM restore_files
        WHERE id NOT IN (SELECT rf_code FROM restore_file_co2)
        AND s_format IN ('psd', 'tif', 'jpg', 'jpeg', 'png', 'gif', 'eps', 'pdf', 'ai')
        LIMIT 1000";

        $req = $pdo->prepare($sql);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);
        $nb = 0;

        $sqlCo = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length
                FROM image_file imf, container co, image_infofr info
                WHERE co.i_autocode = imf.i_foreigncode
                  AND co.i_autocode = info.i_foreigncode                  
                  AND i_filesize = :fsize
                  AND s_fileformat = :fformat
                  AND co.i_autocode NOT IN (SELECT co_code FROM restore_file_co2)";
        $reqCo = $pdo->prepare($sqlCo);

        foreach ($rows as $row) {
            $reqCo->bindValue(':fsize', $row->fsize, PDO::PARAM_INT);
            $reqCo->bindValue(':fformat', '.'.$row->s_format, PDO::PARAM_STR);
            $reqCo->execute();
            $rowsCo = $reqCo->fetchAll(PDO::FETCH_OBJ);

            echo $row->fname."\n";

            if (count($rowsCo) == 1) {
                $rowCo = $rowsCo[0];
                if (($rowCo->i_width == $row->width) && ($rowCo->i_height == $row->height)) {
                    insertCo($row->id, $rowCo->i_autocode);
                } else {
                    $reason = 'IMAGE#Count=1#With='.$row->width.'-'.$rowCo->i_width.'#Height='.$row->height.'-'.$rowCo->i_height;
                    insertCoAn($row->id, $rowCo->i_autocode, $reason);
                }
            } elseif (count($rowsCo) > 1) { //si multi
                echo 'CNT: '.count($rowsCo);
                foreach ($rowsCo as $rowCo) {
                    $reason = 'IMAGE#Multi';

                    if (($rowCo->i_width == $row->width) && ($rowCo->i_height == $row->height)) {
                        insertCoAn($row->id, $rowCo->i_autocode, $reason, 3);
                        insertCo($row->id, $rowCo->i_autocode);
                    } else {
                        insertCoAn($row->id, $rowCo->i_autocode, $reason);
                    }
                }
            } else { //Si 0

            }
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}


if (controlPixels(78,	5204)){
    echo " OK\n";
}else{
    echo " KO\n";
};

//
//try {
//    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);
//
//    $sql = "SELECT *
//      FROM restore_files
//      WHERE id NOT IN (SELECT rf_code FROM restore_file_co2)
//      LIMIT 1000
//      ";
//
//    $req = $pdo->prepare($sql);
//    $req->execute();
//
//    $rows = $req->fetchAll(PDO::FETCH_OBJ);
//    $nb = 0;
//
//    $sqlCo = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length
//                FROM image_file imf, container co, image_infofr info
//                WHERE co.i_autocode = imf.i_foreigncode
//                  AND co.i_autocode = info.i_foreigncode
//                  AND i_filesize = :fsize
//                  AND s_fileformat = :fformat
//                  AND co.i_autocode NOT IN (SELECT co_code FROM restore_file_co2)";
//    $reqCo = $pdo->prepare($sqlCo);
//
//    foreach ($rows as $row) {
//        $reqCo->bindValue(':fsize', $row->fsize, PDO::PARAM_INT);
//        $reqCo->bindValue(':fformat', '.'.$row->s_format, PDO::PARAM_STR);
//        $reqCo->execute();
//        $rowsCo = $reqCo->fetchAll(PDO::FETCH_OBJ);
//
//        echo $row->fname."\n";
//
//        if (count($rowsCo) == 1) {
//            $rowCo = $rowsCo[0];
//
//            if (isImage($row->fname) || isPdf($row->fname)) {
//                if (($rowCo->i_width == $row->width) && ($rowCo->i_height == $row->height)) {
//                    insertCo($row->id, $rowCo->i_autocode);
//                } else {
//                    $reason = 'IMAGE#Count=1#With='.$row->width.'-'.$rowCo->i_width.'#Height='.$row->height.'-'.$rowCo->i_height;
//                    insertCoAn($row->id, $rowCo->i_autocode, $reason);
//                }
//            }
//
//            if (isOffice($row->fname)) {
//                insertCo($row->id, $rowCo->i_autocode);
//            }
//
//            if (isVideo($row->fname)) {
//                if ((ceil($rowCo->f_length) == ceil($row->length))) {
//                    insertCo($row->id, $rowCo->i_autocode);
//                } else {
//                    $reason = 'VIDEO#Count=1#Length='.$row->length.'-'.$rowCo->f_length;
//                    insertCoAn($row->id, $rowCo->i_autocode, $reason);
//                }
//            }
//        } elseif (count($rowsCo) > 1) { //si multi
//            echo 'CNT: '.count($rowsCo);
//
//            if (isImage($row->fname)) {
//
//                foreach ($rowsCo as $rowCo) {
//                    $reason = 'IMAGE#Multi';
//
//                    if (($rowCo->i_width == $row->width) && ($rowCo->i_height == $row->height)) {
//                        insertCoAn($row->id, $rowCo->i_autocode, $reason, 3);
//                        insertCo($row->id, $rowCo->i_autocode);
//                    } else {
//                        insertCoAn($row->id, $rowCo->i_autocode, $reason);
//                    }
//                }
//            }
//
//            if (isOffice($row->fname)) {
//                foreach ($rowsCo as $rowCo) {
//                    $reason = 'OFFICE#Multi';
//                    insertCoAn($row->id, $rowCo->i_autocode, $reason);
//                }
//            }
//
//            if (isVideo($row->fname)) {
//                foreach ($rowsCo as $rowCo) {
//                    $reason = 'VIDEO#Multi';
//                    insertCoAn($row->id, $rowCo->i_autocode, $reason);
//                }
//            }
//        } else { //Si 0
//            if (isVideo($row->fname)) {
//                $sqlCoVi = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length, co.s_reference
//                    FROM image_file imf, container co, image_infofr info
//                    WHERE co.i_autocode = imf.i_foreigncode
//                      AND co.i_autocode = info.i_foreigncode
//                      AND i_width = :width
//                      AND i_height = :height
//                      AND ceil(f_length) = :length
//                      AND s_fileformat = :fformat";
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
//                if (count($rowsCoVi) == 1) {
//                    $rowCoVi = $rowsCoVi[0];
//
//                    insertCo($row->id, $rowCoVi->i_autocode);
//                    $reason = 'VIDEO#BySizeAndLength#';
//                    insertCoAn($row->id, $rowCoVi->i_autocode, $reason, 3);
//                } elseif (count($rowsCoVi) > 1) {
//                    $reason = 'VIDEO#BySizeAndLength#Multi';
//
//                    $fileNames = array();
//
//                    foreach ($rowsCoVi as $rowCoVi) {
//                        if (!in_array($rowCoVi->s_reference, $fileNames)) {
//                            $fileNames[] = $rowCoVi->s_reference;
//                        }
//                    }
//
//                    foreach ($rowsCoVi as $rowCoVi) {
//                        if (array_count_values($fileNames) == 1) {
//                            insertCo($row->id, $rowCoVi->i_autocode);
//                            insertCoAn($row->id, $rowCoVi->i_autocode, $reason, 3);
//                        } else {
//                            insertCoAn($row->id, $rowCoVi->i_autocode, $reason);
//                        }
//                    }
//                } else {
//                    $reason = 'VIDEO#BySizeAndLength#0';
//                    foreach ($rowsCoVi as $rowCoVi) {
//                        insertCoAn($row->id, $rowCoVi->i_autocode, $reason);
//                    }
//                }
//            }
//        }
//    }
//} catch (PDOException $Exception) {
//    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
//    // String.
//    echo $Exception->getMessage().' : '.$Exception->getCode();
//}