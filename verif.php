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

    $sql = "
select
  db.i_code, db.oldfile, db.restore, imf.s_filename, co.s_reference, imf.i_width, imf.i_height,  imf.i_filesize /1024/1024 fs,
  co.i_autocode, co.s_reference, co.b_isintrash, co.dt_created
from restore_dbl db, image_file imf, container co
where co.i_autocode = imf.i_foreigncode
  and co.i_autocode = db.i_code
and is_restored = 0 and restore = 1
and co.dt_created BETWEEN '2016-01-01' and '2017-01-01'
order by 1";

    $req = $pdo->prepare($sql);

    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    foreach ($rows as $row) {
        $oldFile = str_replace('/home/ubuntu/restore/toAnalyse/', 'pictures/', $row->oldfile);

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
