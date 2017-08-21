<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21/08/17
 * Time: 09:01
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

//$dirsource = '/home/ubuntu/restore/toAnalyse/';
//$dest = '/home/ubuntu/restore/toAnalyse/newdir';

//$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/tmp/';
$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/restore/oridir/';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT co.i_autocode, concat(co.i_autocode , imf.s_fileformat) filename
FROM image_file imf, `total-refontedam`.container co, `total-refontedam`.image_infofr info
WHERE co.i_autocode = imf.i_foreigncode
  AND co.i_autocode = info.i_foreigncode
  and co.b_isonline = 1
  and imf.s_fileformat is not NULL ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 0;

    foreach ($rows as $row){
            $fname = $dirsource.$row->filename;

        if (!file_exists($fname)){
            $sql = "update container set b_isonline = 0 where i_autocode = :id";
            $req = $pdo->prepare($sql);
            $req->bindValue(':id', $row->i_autocode, pdo::PARAM_INT);
            //$req->execute();

            $log = "update container set b_isonline = 1 where i_autocode = ".$row->i_autocode;
            //file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
            file_put_contents('/var/www/rollback.sql', $log, FILE_APPEND);
        }
        $nb++;
    }

    echo $nb;
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

