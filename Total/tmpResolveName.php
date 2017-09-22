<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 11/09/17
 * Time: 00:41
 */


function getFileExtension($file, $withdot=false)
{
    if($withdot)
        return strtolower(substr($file, strrpos($file,".")));
    else
        return strtolower(substr($file, strrpos($file,".")+1));
}


function _readDir($dirsource)
{
    $VALEUR_hote = '127.0.0.1';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $cars = array("+",")","(","'","&","é","è","à" );

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $files = scandir($dirsource);

        foreach ($files as $file) {
            if ($file == '.') continue;
            if ($file == '..') continue;

            if (!is_dir($dirsource.$file)) {
                $ref = $file;

                foreach ($cars as $car) {
                    if ($car = "è"){
                        $ref = str_replace($car,"a_",$ref);
                    }else{
                        $ref = str_replace($car,"_",$ref);
                    }
                }

                $sql = "select * from container where s_reference=:sref";
                $req = $pdo->prepare($sql);
                $req->bindValue('sref', $ref, PDO::PARAM_STR);
                $req->execute();

                $ext = getFileExtension($file, true);

                $vals = $req->fetchAll(PDO::FETCH_OBJ);

                if (count($vals)==1){
                    echo $file."\n";
                    echo $ref."\n";

                    $cocode = $vals[0]->i_autocode;
                    rename($dirsource.$file, $dirsource.$cocode.$ext);
                }elseif (count($vals)>1){
                    echo $file."\n";
                    echo $ref."\n";

                    echo "   MULTI\n";

                    $fsize = filesize($file);

                    $sql = "select * from container co, image_file imf 
                            where s_reference=:sref 
                            and co.i_autocode = imf.i_foreigncode
                            and imf.i_filesize = :fsize";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':sref', $ref, PDO::PARAM_STR);
                    $req->bindValue(':fsize', $fsize, PDO::PARAM_INT);
                    $req->execute();

                    $vals = $req->fetchAll(PDO::FETCH_OBJ);
                    if (count($vals)==1) {
                        $cocode = $vals[0]->i_autocode;
                        rename($dirsource.$file, $dirsource.$cocode.$ext);
                    }

                }

            } else {
                _readDir($dirsource.$file.'/');
            }
        }
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}

_readDir('/home/ubuntu/restore_total/toRestore/');
