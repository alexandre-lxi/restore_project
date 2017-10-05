<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 11/09/17
 * Time: 00:41
 */

function _readDir($dirsource)
{
    $VALEUR_hote = '127.0.0.1';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'onlyfrance';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $files = scandir($dirsource);

        foreach ($files as $file) {
            if ($file == '.') continue;
            if ($file == '..') continue;

            if (!is_dir($dirsource.$file)) {
                $sql = "select * from onlyfrance.container where s_reference=:sref";
                $req = $pdo->prepare($sql);
                $req->bindValue('sref', $file, PDO::PARAM_STR);
                $req->execute();

                $vals = $req->fetchAll(PDO::FETCH_OBJ);

                if (count($vals)==1){
                    $cocode = $vals[0]->i_autocode;
                    rename($dirsource.$file, '/var/www/prod/onlyfrance/back/account/pictures/tmp/toRestore/'.$cocode.'.jpg');
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

_readDir('/var/www/prod/onlyfrance/back/account/ftpupload/2_dir/ALEX_1er_dossier/');
