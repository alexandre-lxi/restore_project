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
    $loff = array('docx', 'pptx', 'doc', 'ppt', 'xls', 'xlsx', 'pptx');
    $ext = getFileExtension($file);

    return in_array($ext, $loff);
}

function findByPixels($rfcode)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';


    $taux = 0.1;
    $vals[1] = array(
        'r'=> array('min'=>0,'max'=>0),
        'g'=> array('min'=>0,'max'=>0),
        'b'=> array('min'=>0,'max'=>0));
    $tCols[1] = array('p1_r','p1_g','p1_b');
    $ret = array();

    for ($i =1; $i <= 10; $i++){
        $tCols[$i] = array('p'.$i.'_r','p'.$i.'_g','p'.$i.'_b');
    }

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT rfcode , p1_r, p1_g, p1_b, p1_a, p2_r, p2_g, p2_b, p2_a, p3_r, p3_g, p3_b, p3_a, p4_r, p4_g, p4_b, p4_a, p5_r, p5_g, p5_b, p5_a, p6_r, p6_g, p6_b, p6_a, p7_r, p7_g, p7_b, p7_a, p8_r, p8_g, p8_b, p8_a, p9_r, p9_g, p9_b, p9_a, p10_r, p10_g, p10_b, p10_a
        FROM restore_nfile_colors where rfcode = :rfcode";
        $sql .= " and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" + ":"").$tCols[$i][0]." + ".$tCols[$i][1]." + ".$tCols[$i][2];
        }
        $sql .= ") <> 7650 and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" + ":"").$tCols[$i][0]." + ".$tCols[$i][1]." + ".$tCols[$i][2];
        }
        $sql .= ") <> 0 ";

        $req = $pdo->prepare($sql);
        $req->bindValue(':rfcode',$rfcode,PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 1;

        foreach ($rows as $row) {
            $nb++;

            for ($i =1; $i <= 10; $i++){
                $rcol = $tCols[$i][0];
                $rval = $row->$rcol;

                $gcol = $tCols[$i][1];
                $gval = $row->$gcol;

                $bcol = $tCols[$i][2];
                $bval = $row->$bcol;

                $vals[$i]['r']['min'] = $rval*(1-$taux);
                $vals[$i]['r']['max'] = $rval*(1+ $taux);
                $vals[$i]['g']['min'] = $gval*(1-$taux);
                $vals[$i]['g']['max'] = $gval*(1+ $taux);
                $vals[$i]['b']['min'] = $bval*(1-$taux);
                $vals[$i]['b']['max'] = $bval*(1+ $taux);
            }

            $sql = "SELECT distinct fname, icode
                    FROM restore_ofile_colors where true";

            $sql .= " and (";

            for ($i =1; $i <= 10; $i++){
                $sql .= (($i>1)?" and ":"").$tCols[$i][0]." between ".$vals[$i]['r']['min']." and ".$vals[$i]['r']['max'];
                $sql .= " and ".$tCols[$i][1]." between ".$vals[$i]['g']['min']." and ".$vals[$i]['g']['max'];
                $sql .= " and ".$tCols[$i][2]." between ".$vals[$i]['b']['min']." and ".$vals[$i]['b']['max'];
            }

            $sql .= ")";

            //print_r($sql);

            $reqSel = $pdo->prepare($sql);
            $reqSel->execute();

            $selRows = $reqSel->fetchAll(PDO::FETCH_OBJ);

            foreach ($selRows as $selRow) {
                $ret[] = $selRow->icode;
            }
        }
        return $ret;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return false;
    }
}

function controlPixels($rfcode, $cocode)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $tCols[1] = array('p1_r', 'p1_g', 'p1_b');
    for ($i = 1; $i <= 10; $i++) {
        $tCols[$i] = array('p'.$i.'_r', 'p'.$i.'_g', 'p'.$i.'_b');
    }

    $taux = 0.1;

    try {
        echo "controlPixels rf: ".$rfcode." co: ".$cocode."\n";

        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT * FROM restore_nfile_colors WHERE rfcode = :rfcode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
        $req->execute();
        $rfvals = $req->fetchAll(PDO::FETCH_OBJ);

        $sql = "SELECT * FROM restore_ofile_colors WHERE icode= :cocode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $req->execute();
        $covals = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 0;

        if ((count($rfvals) == 0) || (count($covals) == 0))
            return -2;

        $rfvals = $rfvals[0];
        $covals = $covals[0];

        for ($i = 1; $i <= 10; $i++) {
            $rcol = $tCols[$i][0];
            $r_rfval = $rfvals->$rcol;
            $r_coval = $covals->$rcol;

            $gcol = $tCols[$i][1];
            $g_rfval = $rfvals->$gcol;
            $g_coval = $covals->$gcol;

            $bcol = $tCols[$i][2];
            $b_rfval = $rfvals->$bcol;
            $b_coval = $covals->$bcol;

            echo $r_rfval."<=>".$r_coval."\n";
            echo $g_rfval."<=>".$g_coval."\n";
            echo $b_rfval."<=>".$b_coval."\n";

            if (
                ((($r_coval * (1 + $taux)) >= $r_rfval) && (($r_coval * (1 - $taux)) <= $r_rfval)) &&
                ((($g_coval * (1 + $taux)) >= $g_rfval) && (($g_coval * (1 - $taux)) <= $g_rfval)) &&
                ((($b_coval * (1 + $taux)) >= $b_rfval) && (($b_coval * (1 - $taux)) <= $b_rfval)))
                $nb++;
        }

        //echo $nb;

        if ($nb >= 9)
            return 1;
        else
            return 0;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return -1;
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

        $sqlInsertCoAn = "delete from restore_file_co_analyse2 where rf_code = :rfcode and co_code=:cocode";
        $reqInsetCoAn = $pdo->prepare($sqlInsertCoAn);
        $reqInsetCoAn->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
        $reqInsetCoAn->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $reqInsetCoAn->execute();


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
        and id between 155660 and 206770";

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
                    if (controlPixels($row->id, $rowCo->i_autocode)==1) {
                        $reason = 'IMAGE#ControlPixel#OK';
                        insertCo($row->id, $rowCo->i_autocode);
                        insertCoAn($row->id, $rowCo->i_autocode, $reason, 3);
                    } else {
                        $reason = 'IMAGE#ControlPixel#KO#With='.$row->width.'-'.$rowCo->i_width.'#Height='.$row->height.'-'.$rowCo->i_height;
                        insertCoAn($row->id, $rowCo->i_autocode, $reason);
                    }
                }
            } elseif (count($rowsCo) > 1) { //si multi
                foreach ($rowsCo as $rowCo) {
                    $cp = controlPixels($row->id, $rowCo->i_autocode);

                    if ($cp==1) {
                        $reason = 'IMAGE#Multi#ControPixel#OK';
                        insertCoAn($row->id, $rowCo->i_autocode, $reason, 3);
                        insertCo($row->id, $rowCo->i_autocode);
                    }elseif ($cp==-2) {

                        if (($rowCo->i_width == $row->width) && ($rowCo->i_height == $row->height)) {
                            $reason = 'IMAGE#Multi#ControPixel#KO';
                            insertCoAn($row->id, $rowCo->i_autocode, $reason, 3);
                            insertCo($row->id, $rowCo->i_autocode);
                        } else {
                            $reason = 'IMAGE#Multi#ControPixel#KO#Size#KO';
                            insertCoAn($row->id, $rowCo->i_autocode, $reason);
                        }
                    }else{
                        $reason = 'IMAGE#Multi#ControPixel#KO';
                        insertCoAn($row->id, $rowCo->i_autocode, $reason);
                    }
                }
            } else { //Si 0
                $fbps= findByPixels($row->id);

                if ((count($fbps)>=1) && (count($fbps)<=5)) {
                    foreach ($fbps as $fbp) {
                        $sqlCo2 = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length, i_filesize
                                FROM image_file imf, container co, image_infofr info
                                WHERE co.i_autocode = imf.i_foreigncode
                                  AND co.i_autocode = info.i_foreigncode                  
                                  AND co.i_autocode = :cocode
                                  AND co.i_autocode NOT IN (SELECT co_code FROM restore_file_co2)";
                        $reqCo2 = $pdo->prepare($sqlCo2);
                        $reqCo2->bindValue(':cocode', $fbp, PDO::PARAM_INT);
                        $reqCo2->execute();
                        $co2Vals = $reqCo2->fetchAll(PDO::FETCH_OBJ);

                        if (count($co2Vals)==1){
                            $co2Val = $co2Vals[0];
                            $reason = 'IMAGE#FBP#Unique';
                            insertCoAn($row->id, $co2Val->i_autocode, $reason, 3);
                            insertCo($row->id, $co2Val->i_autocode);
                        }
                    }
                }
                if (count($fbps)>5) {
                    $reason = 'IMAGE#FBP#KO#ANALYSE';
                    insertCoAn($row->id, 1, $reason);
                }
            }
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

function threatVideo()
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
        AND s_format IN ('avi','mpg','mpeg','m2v','wmv','mov','flv','mp4')"
        ;

        $req = $pdo->prepare($sql);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);
        $nb = 0;

        $sqlCo = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length, co.s_reference
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
                if ((ceil($rowCo->f_length*0.95) <= ceil($row->length)) &&
                    (ceil($rowCo->f_length*1.05) >= ceil($row->length))) {
                    insertCo($row->id, $rowCo->i_autocode);
                } else {
                    $reason = 'VIDEO#Count=1#Length='.$row->length.'-'.$rowCo->f_length;
                    insertCoAn($row->id, $rowCo->i_autocode, $reason);
                }
            } elseif (count($rowsCo) > 1) { //si multi
                $trr = false;

                foreach ($rowsCo as $rowCo) {
                    if ((ceil($rowCo->f_length*0.95) <= ceil($row->length)) &&
                        (ceil($rowCo->f_length*1.05) >= ceil($row->length))) {
                        insertCo($row->id, $rowCo->i_autocode);
                        $trr = true;
                    }
                }

                if (!$trr) {
                    $srefs = array();

                    foreach ($rowsCo as $rowCo) {
                        if (!in_array($rowCo->s_reference, $srefs)) {
                            $srefs[] = $rowCo->s_reference;
                        }
                    }

                    if (array_count_values($srefs) == 1) {
                        $reason = 'VIDEO#Multi#OK';
                        foreach ($rowsCo as $rowCo) {
                            insertCo($row->id, $rowCo->i_autocode);
                            insertCoAn($row->id, $rowCo->i_autocode, $reason, 3);
                        }
                    } else {
                        $reason = 'VIDEO#Multi';
                        foreach ($rowsCo as $rowCo) {
                            insertCoAn($row->id, $rowCo->i_autocode, $reason);
                        }
                    }
                }
            } else { //Si 0
                $sqlCoVi = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length, co.s_reference
                    FROM image_file imf, container co, image_infofr info
                    WHERE co.i_autocode = imf.i_foreigncode
                      AND co.i_autocode = info.i_foreigncode
                      AND i_width = :width
                      AND i_height = :height
                      AND ceil(f_length) = :length
                      AND s_fileformat = :fformat";
                $reqCoVi = $pdo->prepare($sqlCoVi);

                $reqCoVi->bindValue(':fformat', '.'.$row->s_format, PDO::PARAM_STR);
                $reqCoVi->bindValue(':width', $row->width, PDO::PARAM_INT);
                $reqCoVi->bindValue(':height', $row->height, PDO::PARAM_INT);
                $reqCoVi->bindValue(':length', ceil($row->length), PDO::PARAM_INT);

                $reqCoVi->execute();

                $rowsCoVi = $reqCoVi->fetchAll(PDO::FETCH_OBJ);

                if (count($rowsCoVi) == 1) {
                    $rowCoVi = $rowsCoVi[0];

                    insertCo($row->id, $rowCoVi->i_autocode);
                    $reason = 'VIDEO#BySizeAndLength#OK';
                    insertCoAn($row->id, $rowCoVi->i_autocode, $reason, 3);
                } elseif (count($rowsCoVi) > 1) {
                    $fileNames = array();

                    foreach ($rowsCoVi as $rowCoVi) {
                        if (!in_array($rowCoVi->s_reference, $fileNames)) {
                            $fileNames[] = $rowCoVi->s_reference;
                        }
                    }

                    foreach ($rowsCoVi as $rowCoVi) {
                        if (array_count_values($fileNames) == 1) {
                            insertCo($row->id, $rowCoVi->i_autocode);
                            $reason = 'VIDEO#BySizeAndLength#Multi#OK';
                            insertCoAn($row->id, $rowCoVi->i_autocode, $reason, 3);
                        } else {
                            $reason = 'VIDEO#BySizeAndLength#Multi';
                            insertCoAn($row->id, $rowCoVi->i_autocode, $reason);
                        }
                    }
                } else {
                    $reason = 'VIDEO#BySizeAndLength#0';
                    foreach ($rowsCoVi as $rowCoVi) {
                        insertCoAn($row->id, $rowCoVi->i_autocode, $reason);
                    }
                }
            }
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}


function threatOffice()
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
        AND s_format IN ('xls', 'doc','ppt','pptx','docx','dotx','potx','xlsx','qxd','swf','exe','zip')
        ";

        $req = $pdo->prepare($sql);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);
        $nb = 0;

        $sqlCo = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length, co.s_reference
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
                insertCo($row->id, $rowCo->i_autocode);
            } elseif (count($rowsCo) > 1) { //si multi
                $srefs = array();

                foreach ($rowsCo as $rowCo) {
                    if (!in_array($rowCo->s_reference, $srefs)){
                        $srefs[] = $rowCo->s_reference;
                    }
                }

                if (array_count_values($srefs) == 1){
                    $reason = 'OFFICE#Multi#OK';
                    foreach ($rowsCo as $rowCo) {
                        insertCo($row->id, $rowCo->i_autocode);
                        insertCoAn($row->id, $rowCo->i_autocode, $reason,3);
                    }
                }else{
                    $reason = 'OFFICE#Multi';
                    foreach ($rowsCo as $rowCo) {
                        insertCoAn($row->id, $rowCo->i_autocode, $reason);
                    }
                }
            }
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

function threatOthers()
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
        AND s_format not IN ('xls', 'doc','ppt','pptx','docx','dotx','potx','xlsx','qxd','swf','exe','zip',
                             'avi','mpg','mpeg','m2v','wmv','mov','flv','mp4',
                             'psd', 'tif', 'jpg', 'jpeg', 'png', 'gif', 'eps', 'pdf', 'ai')
        ";

        $req = $pdo->prepare($sql);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);
        $nb = 0;

        $sqlCo = "SELECT co.i_autocode, imf.i_width, imf.i_height, imf.f_length, co.s_reference
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
                insertCo($row->id, $rowCo->i_autocode);
            } elseif (count($rowsCo) > 1) { //si multi
                $srefs = array();

                foreach ($rowsCo as $rowCo) {
                    if (!in_array($rowCo->s_reference, $srefs)){
                        $srefs[] = $rowCo->s_reference;
                    }
                }

                if (array_count_values($srefs) == 1){
                    $reason = 'OFFICE#Multi#OK';
                    foreach ($rowsCo as $rowCo) {
                        insertCo($row->id, $rowCo->i_autocode);
                        insertCoAn($row->id, $rowCo->i_autocode, $reason,3);
                    }
                }else{
                    $reason = 'OFFICE#Multi';
                    foreach ($rowsCo as $rowCo) {
                        insertCoAn($row->id, $rowCo->i_autocode, $reason);
                    }
                }
            }
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

function threatOfficeAn(){
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "select DISTINCT rf_code
                from restore_file_co_analyse2 an
                where an.reason='OFFICE#Multi'
                and is_restored = 0";

        $req = $pdo->prepare($sql);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        foreach ($rows as $row) {
            $sqlDist = "select distinct s_reference from restore_files rf, restore_file_co_analyse2 an, container co, image_file imf
                        where rf.id = an.rf_code
                        and co.i_autocode = an.co_code
                        and co.i_autocode = imf.i_foreigncode
                        and b_isintrash = 0 
                        and an.rf_code = :rfcode";

            $reqDist = $pdo->prepare($sqlDist);
            $reqDist->bindValue(':rfcode', $row->rf_code, PDO::PARAM_STR);
            $reqDist->execute();

            if ($reqDist->rowCount() ==1){
                $sqlInsert = "insert into restore_file_co2
                            select rf_code, co_code, FALSE 
                            from restore_file_co_analyse2 an
                            where rf_code = :rfcode
                            and exists(select * from container where i_autocode = co_code and b_isintrash =0 )";
                $reqInsert = $pdo->prepare($sqlInsert);
                $reqInsert->bindValue(':rfcode', $row->rf_code, PDO::PARAM_INT);
                $reqInsert->execute();

                $sqlInsert = "update restore_file_co_analyse2
                            set is_restored = 3
                            where rf_code = :rfcode";
                $reqInsert = $pdo->prepare($sqlInsert);
                $reqInsert->bindValue(':rfcode', $row->rf_code, PDO::PARAM_INT);
                $reqInsert->execute();

            }
        }

    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

function threatImageAn(){
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "select DISTINCT rf_code
                from restore_file_co_analyse2 an
                where an.reason='IMAGE#Multi#ControPixel#KO'
                and is_restored = 0
                and to_restore is null";

        $req = $pdo->prepare($sql);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        foreach ($rows as $row) {
            $sqlDist = "select distinct s_reference from restore_files rf, restore_file_co_analyse2 an, container co, image_file imf
                        where rf.id = an.rf_code
                        and co.i_autocode = an.co_code
                        and co.i_autocode = imf.i_foreigncode
                        and b_isintrash = 0 
                        and an.rf_code = :rfcode";

            $reqDist = $pdo->prepare($sqlDist);
            $reqDist->bindValue(':rfcode', $row->rf_code, PDO::PARAM_STR);
            $reqDist->execute();

            if ($reqDist->rowCount() ==1){
                $sqlInsert = "insert into restore_file_co2
                            select rf_code, co_code, FALSE 
                            from restore_file_co_analyse2 an
                            where rf_code = :rfcode
                            and exists(select * from container where i_autocode = co_code and b_isintrash =0 )";
                $reqInsert = $pdo->prepare($sqlInsert);
                $reqInsert->bindValue(':rfcode', $row->rf_code, PDO::PARAM_INT);
                $reqInsert->execute();

                $sqlInsert = "update restore_file_co_analyse2
                            set is_restored = 3
                            where rf_code = :rfcode";
                $reqInsert = $pdo->prepare($sqlInsert);
                $reqInsert->bindValue(':rfcode', $row->rf_code, PDO::PARAM_INT);
                $reqInsert->execute();

            }
        }

    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}


//threatImage();
//print_r(findByPixels(773));
//threatOffice();
//threatOfficeAn();
//threatVideo();
//threatOthers();
threatImageAn();
//print_r(controlPixels(28704, 46740));

