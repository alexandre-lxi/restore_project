<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 18:47
 */

include 'iptc.php';

echo "<html>";

?>
<html>
<head>
    <style>
        table {
            border-collapse: collapse; /* Les bordures du tableau seront collées (plus joli) */
        }

        td {
            border: 1px solid black;
        }
    </style>


</head>
<body>
<?php


$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT rf_code, co_code, rf.fname
            FROM restore_file_co2 co2, restore_files rf 
            WHERE co2.is_restored = 0
            and co2.rf_code = rf.id
            and rf.s_format in ('jpg','png')
            and co_code in (SELECT co_code from restore_file_co2 group by co_code having count(*)>1)
            order by co_code
            limit 100";
    $req = $pdo->prepare($sql);
    $req->execute();
    $rfvals = $req->fetchAll(PDO::FETCH_OBJ);


} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
    return -1;
}

$dname = 'pictures/tmpdir/';
$dname2 = 'pictures/olddir/thumbdir/';



echo '<table>';

foreach ($rfvals as $rfval) {
    $fname = $rfval->fname;
    $fname = basename($fname);
    $fname = explode('.',$fname);
    $fname = $fname[0];
    $fname = $dname.$fname.'.jpg';

    $fname2 = $dname2.$rfval->co_code.'.jpg';

    echo '<tr>';
        echo '<td><img src="'.$fname.'" width="500"></td>';
        echo '<td><img src="'.$fname2.'" width="500"></td>';
    echo '</tr>';
}



echo '</table>';
?>
</body>
</html>
