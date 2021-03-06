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

$olddname = '/home/ubuntu/restore/olddir/thumbdir/';

$img = new Imagick();



$lpixels = array(
    '1'=>array(30,30),
    '2'=>array(130,160),
    '3'=>array(230,30),
    '4'=>array(30,60),
    '5'=>array(160,130),
    '6'=>array(230,60),
    '7'=>array(130,130),
    '8'=>array(140,140),
    '9'=>array(150,150),
    '10'=>array(160,160),
);


//$files = scandir($olddname);
$files[] = '46617.jpg';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = 'insert into restore_ofile_colors (fname, icode, p1_r, p1_g, p1_b, p1_a, p2_r, p2_g, p2_b, p2_a, p3_r, p3_g, p3_b, p3_a, p4_r, p4_g, p4_b, p4_a, p5_r, p5_g, p5_b, p5_a, p6_r, p6_g, p6_b, p6_a, p7_r, p7_g, p7_b, p7_a, p8_r, p8_g, p8_b, p8_a, p9_r, p9_g, p9_b, p9_a, p10_r, p10_g, p10_b, p10_a) 
            values (:fname, :icode, :p1_r, :p1_g, :p1_b, :p1_a, :p2_r, :p2_g, :p2_b, :p2_a, :p3_r, :p3_g, :p3_b, :p3_a, :p4_r, :p4_g, :p4_b, :p4_a, :p5_r, :p5_g, :p5_b, :p5_a, :p6_r, :p6_g, :p6_b, :p6_a, :p7_r, :p7_g, :p7_b, :p7_a, :p8_r, :p8_g, :p8_b, :p8_a, :p9_r, :p9_g, :p9_b, :p9_a, :p10_r, :p10_g, :p10_b, :p10_a)';

    $req = $pdo->prepare($sql);

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (is_dir($olddname.$file))
            continue;

        echo $file."\n";

        $fname = $olddname.$file;
        $icode = explode('.', $file);
        $icode = $icode[0];

        try {
            $img->readImage($fname);

            $req->bindValue(':fname', $file, PDO::PARAM_STR);
            $req->bindValue(':icode', $icode, PDO::PARAM_INT);

            $cnt = 1;

            foreach ($lpixels as $lpixel) {
                $shnew = $img->getImagePixelColor($lpixel[0], $lpixel[1])->getColor();
                $req->bindValue(':p'.$cnt.'_a', $shnew['a'], PDO::PARAM_INT);
                $req->bindValue(':p'.$cnt.'_r', $shnew['r'], PDO::PARAM_INT);
                $req->bindValue(':p'.$cnt.'_g', $shnew['g'], PDO::PARAM_INT);
                $req->bindValue(':p'.$cnt.'_b', $shnew['b'], PDO::PARAM_INT);

                $cnt++;
            }

            $img->clear();

            $req->execute();
        }catch(Exception $e){
            continue;
        }

    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}






