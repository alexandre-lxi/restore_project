<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09/09/17
 * Time: 13:34
 */

$dirsource = '/media/sf_partage_ub/total/';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);


    $files = scandir($dirsource);


    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        $path_parts = pathinfo($file);

        $cocode = (int)$path_parts['filename'];

        $sql = "insert into restore_ok values (:i_code, 99)";
        $req = $pdo->prepare($sql);
        $req->bindValue(':i_code', $cocode, PDO::PARAM_INT);
        $req->execute();



    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}