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

$ffmpeg_path = 'ffmpeg'; //or: /usr/bin/ffmpeg , or /usr/local/bin/ffmpeg - depends on your installation (type which ffmpeg into a console to find the install path)
$vid = 'PATH/TO/VIDEO'; //Replace here!

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT *
      FROM restore_files
      WHERE s_format in ('jpg', 'png')
      and id not in (select rf_code from restore_file_co3)
      and id not in (select rf_code from restore_file_co2)     
      
      ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $nb = 0;

    $sql = "update restore_files set height = :height, width = :width, fsize = :fsize where id = :rfcode";
    $upd = $pdo->prepare($sql);

    $img = new Imagick();

    foreach ($rows as $row) {
        $vid = $row->fname;

        echo $vid."\n";

        if (file_exists($vid)) {
            $img->readImage($vid);

            if (($row->height <> $img->getImageHeight()) ||
                ($row->width <> $img->getImageWidth()) ||
                ($row->fsize <> $img->getImageLength())){
                echo "NEW width = ".$img->getImageWidth()." height = ".$img->getImageHeight()." size= ".$img->getImageLength()."\n";

                $upd->bindValue(':rfcode', $row->id, PDO::PARAM_INT);
                $upd->bindValue(':height', $img->getImageHeight(), PDO::PARAM_INT);
                $upd->bindValue(':width', $img->getImageWidth(), PDO::PARAM_INT);
                $upd->bindValue(':fsize', $img->getImageLength(), PDO::PARAM_INT);

                $upd->execute();
            }

            $img->clear();
        }
    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

