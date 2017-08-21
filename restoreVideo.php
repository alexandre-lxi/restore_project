<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$ret = array();
$ret['single'] = array();
$ret['doublon'] = array();
$ret['total'] = array();
$nb = 0;

function testFile($dirsource, $dest, $file, $pdo, &$ret, &$nb)
{
    $fileSize = filesize($dirsource.$file);
    $fileDet = explode('.', $file);
    $fileExt = $fileDet[1];

    echo $nb."\n";
    $nb = $nb + 1;

    if (($nb % 5000) ==0){
        file_put_contents('/var/www/project/log.txt', json_encode($ret)."\n"."\n", FILE_APPEND);
        //file_put_contents('/home/ubuntu/log.txt', json_encode($ret)."\n"."\n", FILE_APPEND);
//        die();
    }

    if (($fileExt == 'txt') || ($fileExt == 'TX?')) {
        return false;
    } else {
        if (!array_key_exists($fileExt, $ret['single']))
            $ret['single'][$fileExt] = 0;

        if (!array_key_exists($fileExt, $ret['doublon']))
            $ret['doublon'][$fileExt] = 0;

        if (!array_key_exists($fileExt, $ret['total']))
            $ret['total'][$fileExt] = 0;
    }

    //echo $nb."\n";
    //$nb = $nb +1;

    try {
        $sql = "SELECT co.i_autocode, imf.s_fileformat
            FROM `total-refontedam`.image_file imf, `total-refontedam`.container co, `total-refontedam`.image_infofr info
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = info.i_foreigncode
              AND right(co.s_reference,3) = :extension
              AND i_filesize = :size";

        $req = $pdo->prepare($sql);
        $req->bindValue(':extension', strip_tags($fileExt), PDO::PARAM_STR);
        $req->bindValue(':size', $fileSize, PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        if (count($rows) == 1) {
            $fname = $rows[0]->i_autocode.$rows[0]->s_fileformat;
            $dname = $dest.'/oridir/';

            if (!file_exists($dname)){
                if (!mkdir($dname, 0777, true)) {
                    die('Echec lors de la création des répertoires...');
                }
            }

//            echo $fname."\n";
//            echo $bname."\n";
//            echo $dname."\n";

            $oldFile = $dirsource.$file;
            $newFile = $dname.$fname;

//            echo $oldFile."\n";
//            echo $newFile."\n";

            if (!copy( $oldFile, $newFile)){
                $log = "ERROR COPY#".$oldFile."=>".$newFile."\n";
                //file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
                file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
            }

            $ret['single'][$fileExt] = $ret['single'][$fileExt] + 1;
        } elseif (count($rows) > 1) {
            $ret['doublon'][$fileExt] = $ret['doublon'][$fileExt] + 1;
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode()."\n";
        $log = $dirsource.$file.'##'.$fileExt.'##'.$fileSize.'##'.$Exception->getMessage().' : '.$Exception->getCode()."\n";
        //file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
        file_put_contents('/home/ubuntu/log.txt', $log, FILE_APPEND);
    }

    $ret['total'][$fileExt] = $ret['total'][$fileExt] + 1;
}

function _readDir($dirsource, $dest, $pdo, &$ret, &$nb)
{
    $files = scandir($dirsource);

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!is_dir($dirsource.$file)) {
            testFile($dirsource, $dest, $file, $pdo, $ret, $nb);
        } else {
            _readDir($dirsource.$file.'/', $dest, $pdo, $ret, $nb);
        }
    }
}

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

//$dirsource    = '/home/ubuntu/tri/toRestore/';
//$dirsource = '/home/ubuntu/restore/toAnalyse/';
//$dest = '/home/ubuntu/restore/newdir';

$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/tmp/';
$dest = '/home/alex/Documents/IRIS/Clients/kwk/total/restore';

_readDir($dirsource, $dest, $pdo, $ret, $nb);

//file_put_contents('/home/ubuntu/log.txt', json_encode($ret)."\n", FILE_APPEND);
file_put_contents('/var/www/project/log.txt', json_encode($ret)."\n", FILE_APPEND);
