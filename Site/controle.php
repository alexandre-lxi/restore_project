<?php

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co2.co_code , count(*)
            from restore_file_co_analyse2 co2, restore_files rf
            where co2.rf_code = rf.id
            and rf.s_format in ('jpg','png')
            and co2.is_restored <> 5
            and co2.co_code <> 1
            and co2.co_code in (select i_autocode from container where b_isintrash <> 0)
            group by co2.co_code
            having count(*)>1
            ORDER BY RAND( )
            limit 1
            ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $rowCo = $rows[0];
    $cocode = $rowCo->co_code;


    $sql = "select rf_code, fname
            from restore_file_co_analyse2, restore_files
            where co_code = :cocode
            and id = rf_code";

    $req = $pdo->prepare($sql);
    $req->bindValue(':cocode', $rowCo->co_code, PDO::PARAM_INT);
    $req->execute();

    $rowsRf = $req->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
    die();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Controle images</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script>
        $(window).scroll(function () {
            $('#entete').css({"position":"fixed", "top":"0px", "left":"0px"});
            if ($(window).scrollTop() == 0)
            {
                $('#entete').css({"position":"fixed", "top":"50px", "left":"0px"});
            }
        });
    </script>

</head>
<body>
<form action="action.php" method="post">
    <input type="hidden" name="cocode" value="<?php echo $cocode; ?>">

    <div id="entete">
        <p>Votre nom : <input type="text" name="name" value="<?php echo (isset($_GET['name'])?$_GET['name']:''); ?>"></p>
        <p><input type="submit"></p>
    </div>

    <div id="tofind">
        <div>
            Image recherchée :
        </div>

        <img height="240px" src="<?php echo 'pictures/olddir/thumbdir/'.$rowCo->co_code.'.jpg' ?>">
    </div>


    <div id="main">
            <div>
                <ul class="ul">
                <?php
                foreach ($rowsRf as $rowRf) {
                    $fname = basename($rowRf->fname);
                    $fname = explode('.',$fname);
                    $fname = 'pictures/tmpdir/'.$fname[0].'.jpg';

                    echo '<li class="li">';
                    echo '<table>';
                    echo '<tr>';
                    echo '<td><img style="margin: 5px" src="'.$fname.'"\></td>';
                    echo '<td><input type="radio" name="list" value="'.$rowRf->rf_code.'"></td>';
                    echo '</tr>';
                    echo '</table>';
                    echo '</li>';
                }

                ?>

                </ul>
            </div>
    </div>
</form>

</body>
</html>