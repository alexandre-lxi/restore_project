<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 24/08/17
 * Time: 00:34
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
      WHERE s_format in ('tif','eps','psd','ai','fla')
      ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $nb = 0;


    foreach ($rows as $row){
        $fs = filesize($row->fname);

        if ($fs > 0){
            $sql = "update restore_files set fsize = :fsize where id = :id";

            $req = $pdo->prepare($sql);
            $req->bindValue(':fsize', $fs, PDO::PARAM_INT);
            $req->bindValue(':id', $row->id, PDO::PARAM_INT);

            $req->execute();
        }


    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}
