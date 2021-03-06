<?php

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$name = (isset($_GET['name'])?$_GET['name']:'');

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co2.co_code, imf.i_width, imf.i_height, rf.id
            from restore_file_co_analyse2 co2, restore_files rf, image_file imf
            where co2.rf_code = rf.id
            and rf.s_format in ('jpg','png')            
            and rf.is_restored = 0
            and rf.to_restore=0
            and (co2.to_restore = 0 or co2.to_restore is null) 
            and co2.co_code <> 1
            and i_foreigncode = co2.co_code
            and co2.co_code in (select i_autocode from container where b_isintrash <> 0)
            order by RAND()
            limit 1
            
            ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $rowCo = $rows[0];
    $cocode = $rowCo->co_code;


    $sql = "select rf_code, fname, width, height
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
<?php
    if (isset($_GET['error'])){
        echo '<p style="color: red;">'.$_GET['error'].'</p>';
    }else{
?>
    <form action="action.php" method="post">
        <input type="hidden" name="cocode" value="<?php echo $cocode; ?>">
        <input type="hidden" name="ttrfcode" value="<?php echo $rowCo->id; ?>">

        <div id="entete">
            <p>Votre nom :
                <select name="name">
                    <option value="sounia" <?php echo ($name == 'sounia')?'selected="selected"':''; ?>">Sounia</option>
                    <option value="antoine" <?php echo ($name == 'antoine')?'selected="selected"':''; ?>">Antoine</option>
                    <option value="alex" <?php echo ($name == 'alex')?'selected="selected"':''; ?>">Alex</option>
                </select>
            </p>

            <p><input type="submit"></p>
        </div>

        <div id="tofind">
            <div>
                Image recherchée :
            </div>

            <img height="170px" src="<?php echo 'pictures/olddir/thumbdir/'.$rowCo->co_code.'.jpg' ?>">
            <p style="font-size: small;">Dimension: <?php echo $rowCo->i_width.'x'.$rowCo->i_height; ?></p>
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
                        echo '<td>
                                    <img style="margin: 5px" src="'.$fname.'"\>
                                    <p style="font-size: small;">Dimension : '.$rowRf->width.'x'.$rowRf->height.'</p>
                              </td>';
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
<?php } ?>

</body>
</html>