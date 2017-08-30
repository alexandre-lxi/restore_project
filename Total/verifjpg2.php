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


$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $rfcode = $_GET['imgtmp'];

    $sql = "SELECT fname FROM restore_files WHERE id = :rfcode";
    $req = $pdo->prepare($sql);
    $req->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
    $req->execute();
    $rfvals = $req->fetchAll(PDO::FETCH_OBJ);

    $fname = $rfvals[0]->fname;
    $fname = basename($fname);
    $fname = explode('.', $fname);
    $fname = $fname[0];


} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
    return -1;
}

$fname = 'pictures/tmpdir/'.$fname.'.jpg';

$cocode = $_GET['imgold'];

$fname2 = 'pictures/olddir/thumbdir/'.$_GET['imgold'].'.jpg';




?>
<table>
        <tr>
            <td><img src="<?php echo $fname ?>" width="500"></td>
            <td><img src="<?php echo $fname2 ?>" width="500"></td>
        </tr>


</table>

<br>

<?php
function controlPixels($rfcode, $cocode)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $tCols[1] = array('p1_r', 'p1_g', 'p1_b');
    for ($i = 1; $i <= 10; $i++) {
        $tCols[$i] = array('p'.$i.'_r', 'p'.$i.'_g', 'p'.$i.'_b');
    }

    $taux = 0.1;

    try {
        echo "controlPixels rf: ".$rfcode." co: ".$cocode."\n";

        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT * FROM restore_nfile_colors WHERE rfcode = :rfcode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
        $req->execute();
        $rfvals = $req->fetchAll(PDO::FETCH_OBJ);

        $sql = "SELECT * FROM restore_ofile_colors WHERE icode= :cocode";
        $req = $pdo->prepare($sql);
        $req->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $req->execute();
        $covals = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 0;

        if ((count($rfvals) == 0) || (count($covals) == 0))
            return -2;

        $rfvals = $rfvals[0];
        $covals = $covals[0];

        for ($i = 1; $i <= 10; $i++) {
            $rcol = $tCols[$i][0];
            $r_rfval = $rfvals->$rcol;
            $r_coval = $covals->$rcol;

            $gcol = $tCols[$i][1];
            $g_rfval = $rfvals->$gcol;
            $g_coval = $covals->$gcol;

            $bcol = $tCols[$i][2];
            $b_rfval = $rfvals->$bcol;
            $b_coval = $covals->$bcol;

            echo "<br>".$r_rfval."<=>".$r_coval."<br>";
            echo $g_rfval."<=>".$g_coval."<br>";
            echo $b_rfval."<=>".$b_coval."<br>";

            if (
                ((($r_coval * (1 + $taux)) >= $r_rfval) && (($r_coval * (1 - $taux)) <= $r_rfval)) &&
                ((($g_coval * (1 + $taux)) >= $g_rfval) && (($g_coval * (1 - $taux)) <= $g_rfval)) &&
                ((($b_coval * (1 + $taux)) >= $b_rfval) && (($b_coval * (1 - $taux)) <= $b_rfval)))
                $nb++;
        }

        //echo $nb;

        if ($nb >= 9)
            return 1;
        else
            return 0;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return -1;
    }
}



?>

<div>
    <span>CP : <?php echo controlPixels($rfcode, $cocode) ?></span>
</div>
</body>
</html>
