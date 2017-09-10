<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 10/09/17
 * Time: 22:37
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "insert into restore_container_online values (:cocode, :value)";
    $ins = $pdo->prepare($sql);

    $lines = file('rollback.sql', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        echo $line."\n";
        $pos = strpos($line, ' where');
        if ($pos === false)
            continue;

        $value = substr($line, $pos-1, 1);
        $pos2 = strrpos( $line, '= ');
        if ($pos2 === false)
            continue;
        $cocode = substr($line, $pos2+2, strlen($line)-$pos2-3);

        $ins->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $ins->bindValue(':value', $value, PDO::PARAM_INT);
        $ins->execute();

    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}


