<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21/08/17
 * Time: 09:01
 */

include("/var/www/utils/getid3/getid3/getid3.php");

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$olddname = '/home/ubuntu/restore/olddir/thumdir/';

$img = new Imagick();

$lpixels = array(
    '1'=>array(5,15),
    '2'=>array(5,75),
    '3'=>array(5,185),
    '4'=>array(75,15),
    '5'=>array(75,40),
    '6'=>array(75,100),
    '7'=>array(75,185),
    '8'=>array(150,15),
    '9'=>array(150,75),
    '10'=>array(150,185),
);

$files = scandir($olddname);

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = 'insert into restore_ofile_colors (fname, icode, p1_r, p1_g, p1_b, p1_a, p2_r, p2_g, p2_b, p2_a, p3_r, p3_g, p3_b, p3_a, p4_r, p4_g, p4_b, p4_a, p5_r, p5_g, p5_b, p5_a, p6_r, p6_g, p6_b, p6_a, p7_r, p7_g, p7_b, p7_a, p8_r, p8_g, p8_b, p8_a, p9_r, p9_g, p9_b, p9_a, p10_r, p10_g, p10_b, p10_a) 
            values (:fname, :icode, :p1_r, :p1_g, :p1_b, :p1_a, :p2_r, :p2_g, :p2_b, :p2_a, :p3_r, :p3_g, :p3_b, :p3_a, :p4_r, :p4_g, :p4_b, :p4_a, :p5_r, :p5_g, :p5_b, :p5_a, :p6_r, :p6_g, :p6_b, :p6_a, :p7_r, :p7_g, :p7_b, :p7_a, :p8_r, :p8_g, :p8_b, :p8_a, :p9_r, :p9_g, :p9_b, :p9_a, :p10_r, :p10_g, :p10_b, :p10_a)';

    $req = $pdo->prepare($sql);

    $sqlSel = "select id, fname from restore_files";
    $reqSel = $pdo->prepare($sqlSel);

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    foreach ($rows as $row) {
        echo $row->fname."\n";

        $fname = $row->fname;
        $icode = $row->id;

        $img->readImage($fname);

        $req->bindValue(':fname', $fname, PDO::PARAM_STR);
        $req->bindValue(':icode', $icode, PDO::PARAM_INT);

        $cnt = 1;

        foreach ($lpixels as $lpixel) {
            $shnew = $img->getImagePixelColor($lpixel[0],$lpixel[1])->getColor();

            $req->bindValue(':p'.$cnt.'_a', $shnew['a'], PDO::PARAM_INT);
            $req->bindValue(':p'.$cnt.'_r', $shnew['r'], PDO::PARAM_INT);
            $req->bindValue(':p'.$cnt.'_g', $shnew['g'], PDO::PARAM_INT);
            $req->bindValue(':p'.$cnt.'_b', $shnew['b'], PDO::PARAM_INT);

            $cnt++;
        }

        $req->execute();
    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}






