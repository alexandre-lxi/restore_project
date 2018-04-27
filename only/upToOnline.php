<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

$dirsource = '/var/www/prod/onlyfrance/back/account/pictures/tmp/toRestore/oridir/';

function testFile($file)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'onlyfrance';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $timestart=microtime(true);
    echo "Start: ".date("H:i:s", $timestart)."\n";

    $fname = basename($file);
    $cocode = explode('.',$fname);
    $cocode = $cocode[0];

    echo $file."\n";

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

//        $sql = "insert into onlyfrance.restore_files (fname, isOldFile) values(:fname, TRUE )";
//        $rqt = $pdo->prepare($sql);
//        $rqt->bindValue(':fname', $fname, PDO::PARAM_STR);
//        $rqt->execute();
//
//        $sql = "select max(id) id from onlyfrance.restore_files";
//        $rqt = $pdo->prepare($sql);
//        $rqt->execute();
//        $id = $rqt->fetchAll(PDO::FETCH_OBJ);
//        $id = $id[0]->id;
//
//        $sql = "insert into onlyfrance.restore_file_co (rf_code, co_code) values (:rfcode, :cocode) ";
//        $rqt = $pdo->prepare($sql);
//        $rqt->bindValue(':rfcode', $id, PDO::PARAM_INT);
//        $rqt->bindValue(':cocode', $cocode, PDO::PARAM_INT);
//        $rqt->execute();
//
//        $sql = "update onlyfrance.container co
//                        inner join onlyfrance.restore_file_co rfc on co.i_autocode = rfc.co_code
//                        inner join onlyfrance.restore_container_sav cos on cos.i_autocode = rfc.co_code
//                            set co.b_isonline = 1
//                        where co.b_isonline =0
//                        and cos.b_isonline =1
//                        and rfc.co_code = :cocode";
//
//        $rqt = $pdo->prepare($sql);
//        $rqt->bindValue(':cocode', $cocode, PDO::PARAM_INT);
//        $rqt->execute();

        echo "      Update: ".date("H:i:s", microtime(true)- $timestart)."\n";

    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        die();
    }
}

function _readDir($dirsource)
{
    $files = scandir($dirsource);

    $nb = 0;

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!file_exists($dirsource.$file))
            continue;

        if (!is_dir($dirsource.$file)) {
            testFile($dirsource.$file);
        } else {
            _readDir($dirsource.$file.'/');
        }
    }
}

_readDir($dirsource);
