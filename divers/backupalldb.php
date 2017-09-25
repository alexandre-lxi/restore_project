<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 23/09/17
 * Time: 21:47
 */

$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$dest = '/var/www/projects/total-1410-refontedam/back/account/pictures/restoredir';

try {


    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    $pdo->rollBack();
    echo $Exception->getMessage().' : '.$Exception->getCode();
}