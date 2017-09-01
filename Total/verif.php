<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 18:47
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

echo "<html>";
echo "<body>";


try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT * FROM restore_files, restore_file_co2
            WHERE rf_code = id
            and restore_file_co2.is_restored = 0
            and to_restore = 0";

    $req = $pdo->prepare($sql);

    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    foreach ($rows as $row) {
        $fname = str_replace('/home/ubuntu/restore/toAnalyse/', 'pictures/', $row->oldfile);

        echo "<table>";
        echo "<tr><td><img src='".$fname."' style='width: 500px;height: auto;'></td><td>".$row->i_code." : ".$row->s_filename."</td></tr>";
        echo "</table>";
    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

echo "</body>";
echo "</html>";
