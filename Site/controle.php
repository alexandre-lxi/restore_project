<?php

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co_code 
            from restore_file_co_analyse2
            where is_restored <> 5
            and co_code <> 1
            and co_code in (select i_autocode from container where b_isintrash <> 0) 
            group by co_code 
            having count(*)>1
            
            limit 1";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $rowCo = $rows[0];


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
</head>
<body>

<div id="entete">
    En tête
</div>

<div id="main">
    <div id="menu">
        <span> Image à retrouver </span>

        <img src="<?php echo 'pictures/olddir/thumbdir/'.$rowCo->co_code.'.jpg' ?>">
    </div>

    <div id="contenu">
        <div>
            <span >Contenu</span>
        </div>

        <div>
            <ul class="ul">
            <?php
            foreach ($rowsRf as $rowRf) {
                $fname = basename($rowRf->fname);
                $fname = explode('.',$fname);
                $fname = 'pictures/tmpdir/'.$fname[0].'.jpg';

                echo '<li class="li">';
                echo '<img style="margin: 5px" src="'.$fname.'"\>';
                echo '</li>';
            }

            ?>

            </ul>
        </div>
    </div>
</div>

<div id="footer">
    Pied de Page
</div>

</body>
</html>