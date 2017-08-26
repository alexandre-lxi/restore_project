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

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT *
      FROM restore_files
      WHERE s_format in ('mp4')
        and id not in (select rf_code from restore_file_co2)
      limit 100
      ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $nb = 0;

    $sqlCo = "select *
                from image_file imf, container co, image_infofr info
                where co.i_autocode = imf.i_foreigncode
                  and co.i_autocode = info.i_foreigncode
                  and b_isintrash = 0
                  and i_filesize = :fsize
                  and s_fileformat = :fformat";
    $reqCo = $pdo->prepare($sqlCo);

    $sqlInsertCo = "insert into restore_file_co2 (rf_code, co_code, is_restored) values (:rfcode, :cocode, false)";
    $reqInsetCo = $pdo->prepare($sqlInsertCo);

    $sqlInsertCoAn = "insert into restore_file_co_analyse2 (rf_code, co_code, is_restored, reason) values (:rfcode, :cocode, false, :reason)";
    $reqInsetCoAn = $pdo->prepare($sqlInsertCoAn);

    foreach ($rows as $row){
        $reqCo->bindValue(':fsize', $row->fsize, PDO::PARAM_INT);
        $reqCo->bindValue(':fformat', '.'.$row->s_format, PDO::PARAM_STR);
        $reqCo->execute();
        $rowsCo = $reqCo->fetchAll(PDO::FETCH_OBJ);

        if (count($rowsCo) == 1){
            $rowCo = $rowsCo[0];
            if (($rowCo->i_width == $row->width) && ($rowCo->i_height == $row->height)){
                $reqInsetCo->bindValue(':rfcode', $row->id, PDO::PARAM_INT);
                $reqInsetCo->bindValue(':cocode', $rowCo->i_autocode, PDO::PARAM_INT);

                echo "Insert co rfcode:".$row->id." cocode:".$rowCo->i_autocode."\n";
            }else{
                $reqInsetCoAn->bindValue(':rfcode', $row->id, PDO::PARAM_INT);
                $reqInsetCoAn->bindValue(':cocode', $rowCo->i_autocode, PDO::PARAM_INT);

                $reason = 'Count=1#With='.$row->width.'-'.$rowCo->i_width.'#Height='.$row->height.'-'.$rowCo->i_height;
                $reqInsetCoAn->bindValue(':reason', $reason, PDO::PARAM_INT);

                echo "Insert coan rfcode:".$row->id." cocode:".$rowCo->i_autocode." reason: ".$reason."\n";
            }
        }elseif(count($rowsCo)>1){
            echo count($rowsCo)."\n";
        }

    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}