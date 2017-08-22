<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 13:12
 */
$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$dirsource = '/var/www/projects/total-1410-refontedam/restoreDir/toAnalyse/';

function getFileExtension($file, $withdot=false)
{
    if($withdot)
        return strtolower(substr($file, strrpos($file,".")));
    else
        return strtolower(substr($file, strrpos($file,".")+1));
}

function isImage($file)
{
    if(isset($_SERVER['WINDIR']))
    {
        return getFileExtension($file)=="jpg" || getFileExtension($file)=="tif";
    }
    $info = exec("file -bi '".$file."'");
    return strstr($info, "image")!==false || getFileExtension($file)=="eps" || getFileExtension($file)=="tga";
}

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);


    $sql = "select
      db.i_code, db.oldfile, db.restore, imf.s_filename, co.s_reference, imf.i_width, imf.i_height, imf.i_filesize /1024/1024 fs,
      co.i_autocode, co.s_reference, co.b_isintrash, co.dt_created
    from `total-refontedam`.restore_dbl db, `total-refontedam`.image_file imf, `total-refontedam`.container co
    where co.i_autocode = imf.i_foreigncode
      and co.i_autocode = db.i_code
      and co.i_autocode not in (select i_code from restore_dbl where restore = 1)
      limit 10";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 0;
    $isTreat = array();

    foreach ($rows as $row) {
        if (true){
            $fname = str_replace('/home/ubuntu/restore/toAnalyse/', $dirsource, $row->oldfile);

            if(isImage($fname) || isPdf($fname))
            {
                $inData = getImageInfo($fname);

                echo $row->i_code . " ". $fname;
                print_r($inData);
            }
        }

        if (false) {
            $sql = "SELECT DISTINCT imf.s_filename
            FROM `total-refontedam`.restore_dbl db, `total-refontedam`.image_file imf, `total-refontedam`.container co
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = db.i_code
              AND oldfile= :oldf";

            $req = $pdo->prepare($sql);
            $req->bindValue(':oldf', $row->oldfile, PDO::PARAM_STR);
            $req->execute();

            $filenames = $req->fetchAll(PDO::FETCH_OBJ);

            if (count($filenames) == 1) {
                $fname = $filenames[0]->s_filename;

                echo "Single file=> Code:".$row->i_code." - ".$fname."\n";

                if (array_key_exists($fname, $isTreat)) {
                    $isTreat[$fname] = $isTreat[$fname] + 1;;
                } else {
                    $isTreat[$fname] = 1;
                    $sql = "UPDATE restore_dbl SET restore = 1 WHERE oldfile = :oldf";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':oldf', $row->oldfile, PDO::PARAM_STR);
                    $req->execute();
                }
            }
        }
    }

    echo "\n\n";
    print_r($isTreat);

    echo $nb;
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}