<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

//$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

//$dirsource    = '/home/ubuntu/tri/toRestore/';
//$dirsource = '/home/ubuntu/new_onlyfrance/toRestore/toRestore/';
//$dirsource = '/home/ubuntu/new_onlyfrance/test/';
$dirsource    = '/home/ubuntu/new_onlyfrance/toAnalyse/';

function getFileExtension($file, $withdot=false)
{
    if($withdot)
        return strtolower(substr($file, strrpos($file,".")));
    else
        return strtolower(substr($file, strrpos($file,".")+1));
}

function isImage($file)
{
    $info = exec("file -bi '".$file."'");
    return strstr($info, "image")!==false || getFileExtension($file)=="eps" || getFileExtension($file)=="tga";
}

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

    $timestart=microtime(true);

    if (!isImage($file))
        return false;

    $fname = basename($file);
    $cocode = explode('.',$fname);
    $cocode = $cocode[0];

    $img = new Imagick();

    $img->readImage($file);

    echo $file."\n";
    echo "Start: ".date("H:i:s", $timestart)."\n";

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

    echo "      Resize: ".date("H:i:s", microtime(true)- $timestart)."\n";
    $timestart=microtime(true);

    if (file_exists($dWeb.$cocode.'.jpg') && file_exists($dThumb.$cocode.'.jpg')) {
        shell_exec('mv '.$file.' '.$dori.$fname);

        echo "      Move: ".date("H:i:s", microtime(true)- $timestart)."\n";
        $timestart=microtime(true);

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
                die();
            }

            echo "      Insert tables: ".date("H:i:s", microtime(true)- $timestart)."\n";
            $timestart=microtime(true);


            shell_exec('wput '.$dWeb.$cocode.'.jpg ftp://onlyfrance:33Dskoi2e@prod.kwk.eu.com/webdir/'.$cocode.'.jpg');
            shell_exec('wput '.$dThumb.$cocode.'.jpg ftp://onlyfrance:33Dskoi2e@prod.kwk.eu.com/thumbdir/'.$cocode.'.jpg');
            shell_exec('wput '.$dori.$fname.' ftp://onlyfrance:33Dskoi2e@prod.kwk.eu.com/oridir/'.$fname);

            echo "      WPUT: ".date("H:i:s", microtime(true)- $timestart)."\n";
            $timestart=microtime(true);

            try {
                $sql = "update onlyfrance.container co
                        inner join onlyfrance.restore_file_co rfc on co.i_autocode = rfc.co_code
                        inner join onlyfrance.restore_container_sav cos on cos.i_autocode = rfc.co_code
                            set co.b_isonline = 1
                        where co.b_isonline =0
                        and cos.b_isonline =1
                        and rfc.co_code = :cocode";

                $rqt = $pdo->prepare($sql);
                $rqt->bindValue(':cocode', $cocode, PDO::PARAM_INT);
                $rqt->execute();

                echo "      Update: ".date("H:i:s", microtime(true)- $timestart);

            } catch (PDOException $Exception) {
                // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
                // String.
                echo $Exception->getMessage().' : '.$Exception->getCode();
                die();
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
