<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

$VALEUR_hote='prod.kwk.eu.com';
$VALEUR_port='3306';
$VALEUR_nom_bd='total-refontedam';
$VALEUR_user='BdTotal$4';
$VALEUR_mot_de_passe='RvaBD$-R5';

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);
}catch( PDOException $Exception ) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage( ).' : '.$Exception->getCode( ) );
}
