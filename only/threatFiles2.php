<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

//$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

//$dirsource    = '/home/ubuntu/tri/toRestore/';
$dirsource = '/home/ubuntu/new_onlyfrance/toRestore/toRestore/';


function testFile($file)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'onlyfrance';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $dThumb = '/home/ubuntu/new_onlyfrance/pictures/thumbdir/';
    $dWeb = '/home/ubuntu/new_onlyfrance/pictures/webdir/';
    $dori = '/home/ubuntu/new_onlyfrance/pictures/oridir/';

    $fname = basename($file);
    $cocode = explode('.',$fname);
    $cocode = $cocode[0];

    $img = new Imagick();

    $img->readImage($file);

    echo $file."\n";

    if ($img->getImageWidth() > $img->getImageHeight()){
        $img->resizeImage(300,0,Imagick::FILTER_LANCZOS,1);
        $img->writeImage($dThumb.$cocode.'.jpg');
        $img->clear();
        $img->readImage($file);
        $img->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
        $img->writeImage($dWeb.$cocode.'.jpg');
        $img->clear();
    }else{
        $img->resizeImage(0,300,Imagick::FILTER_LANCZOS,1);
        $img->writeImage($dThumb.$cocode.'.jpg');
        $img->clear();
        $img->readImage($file);
        $img->resizeImage(0,1024,Imagick::FILTER_LANCZOS,1);
        $img->writeImage($dWeb.$cocode.'.jpg');
        $img->clear();
    }

    if (file_exists($dWeb.$cocode.'.jpg') && file_exists($dThumb.$cocode.'.jpg')) {
        shell_exec('mv '.$file.' '.$dori.$fname);

        if (file_exists($dori.$fname)){
            try {
                $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

                $sql = "insert into onlyfrance.restore_files (fname, isOldFile) values(:fname, TRUE )";
                $rqt = $pdo->prepare($sql);
                $rqt->bindValue(':fname', $fname, PDO::PARAM_STR);
                $rqt->execute();

                $sql = "select max(id) id from onlyfrance.restore_files";
                $rqt = $pdo->prepare($sql);
                $rqt->execute();
                $id = $rqt->fetchAll(PDO::FETCH_OBJ);
                $id = $id[0]->id;

                $sql = "insert into onlyfrance.restore_file_co (rf_code, co_code) values (:rfcode, :cocode) ";
                $rqt = $pdo->prepare($sql);
                $rqt->bindValue(':rfcode', $id, PDO::PARAM_INT);
                $rqt->bindValue(':cocode', $cocode, PDO::PARAM_INT);
                $rqt->execute();
            } catch (PDOException $Exception) {
                // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
                // String.
                echo $Exception->getMessage().' : '.$Exception->getCode();
            }
            }
    }
}

function _readDir($dirsource)
{
    $files = scandir($dirsource, SCANDIR_SORT_DESCENDING);

    $nb = 0;

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!is_dir($dirsource.$file)) {
            testFile($dirsource.$file);
        } else {
            _readDir($dirsource.$file.'/');
        }

        $nb++;
        if ($nb >= 1000)
            break;
    }
}

_readDir($dirsource);
