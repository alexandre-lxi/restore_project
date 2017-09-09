<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09/09/17
 * Time: 13:34
 */

function findBySizes($width, $height)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $ret=array();

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT * from restore_files 
                where is_restored <> 1 
                and width = :width 
                and height=:height 
                and s_format in ('jpg', 'png') 
                order by fsize desc
                ";

        $req = $pdo->prepare($sql);
        $req->bindValue(':width',$width,PDO::PARAM_INT);
        $req->bindValue(':height',$height,PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 1;

        foreach ($rows as $row) {
            print_r($row->fname);

            $ret[] = array(
                'width' => $row->width,
                'rfcode' => $row->id,
                'height' => $row->height,
                'fname' => $row->fname);

        }
        return $ret;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return false;
    }
}

$ret = findBySizes(5000, 3334);
foreach ($ret as $item) {
    print_r($item);
}