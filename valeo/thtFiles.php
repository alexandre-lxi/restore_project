<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 14/01/18
 * Time: 22:03
 */

$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'valeomediadev';
$VALEUR_user = 'valeo';
$VALEUR_mot_de_passe = 'm8p3FCbt9qQXS3';

$FTP_host = 'bucket-23c46363-4dc8-4f75-bc81-e6c80fcb3279-fsbucket.services.clever-cloud.com';
$FTP_UN = 'user23c463634dc84f75bc81e6c80fcb3279';
$FTP_PWD = 'AsgAGhMR5Inn6pue';


$dori = '/media/sf_partage_ub/oridir/';
$dtmp = '//';


try {

/*    $cftp = ftp_connect($FTP_host) or die("Impossible de se connecter au serveur $FTP_host");
    if (@ftp_login($cftp, $FTP_UN, $FTP_PWD)){
            echo "Connecté en tant que $FTP_UN@$FTP_PWD\n";
    } else {
            echo "Connexion impossible en tant que $FTP_UN\n";
            die();
    }

    if (is_null($cftp)){
        echo 'error ftp';
        die();
    }
 */
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select concat(c.i_autocode, imf.s_fileformat) fname, imf.s_filename nfname
            from image_file imf
              inner join container c ON imf.i_foreigncode = c.i_autocode
            where c.b_isintrash = 0
            ";
    $req = $pdo->prepare($sql);
    $req->execute();
    $vals = $req->fetchAll(PDO::FETCH_CLASS);

    foreach ($vals as $val) {
        rename($dori.$val->fname, $dori.$val->nfname);

   /*     $rfile = $val->nfname;

        if (ftp_put($cftp, $rfile, $dori.$val->nfname, FTP_BINARY)) {
            echo "Le fichier $rfile a été chargé avec succès\n";
            unlink($dtmp.$val->nfname);

        } else {
            echo "Il y a eu un problème lors du chargement du fichier $rfile\n";
            die();
        }*/
        echo $val->nfname."\n";
    }

    //ftp_close($cftp);

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
    die();
}
