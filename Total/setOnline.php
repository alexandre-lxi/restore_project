<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21/08/17
 * Time: 09:01
 */

$VALEUR_hote = 'localhost';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$dirsource = '/var/www/projects/total-1410-refontedam/back/account/pictures/oridir/';
//$dest = '/home/ubuntu/restore/toAnalyse/newdir';

//$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/tmp/';
//$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/restore/oridir/';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT co.i_autocode, concat(co.i_autocode , imf.s_fileformat) filename
FROM image_file imf, `total-refontedam`.container co, `total-refontedam`.image_infofr info
WHERE co.i_autocode = imf.i_foreigncode
AND co.i_autocode = info.i_foreigncode
AND co.b_isonline = 0
and co.i_autocode in (select co_code from restore_container_online)
AND imf.s_fileformat IS NOT NULL ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 0;

    foreach ($rows as $row) {
        $fname = $dirsource.$row->filename;

        if (file_exists($fname)) {
            $sql = "UPDATE container SET b_isonline = 1 WHERE i_autocode = :id";
            $req = $pdo->prepare($sql);
            $req->bindValue(':id', $row->i_autocode, pdo::PARAM_INT);
            $req->execute();

            echo " OK ".$fname."\n";

            $log = "UPDATE container SET b_isonline = 0 WHERE i_autocode = ".$row->i_autocode.";\n";
//file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
            file_put_contents('/var/www/tmp_total/rollback2.sql', $log, FILE_APPEND);
        } else {
            $log = "exist = ".$row->i_autocode.";\n";
//file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
            file_put_contents('/var/www/tmp_total/execute2.sql', $log, FILE_APPEND);
        }
        $nb++;
    }

    echo $nb;
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

