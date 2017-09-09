<?php

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$name = (isset($_GET['name'])?$_GET['name']:'');

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co.i_autocode
                from container co, image_file imf
                where co.i_autocode not in (select co_code from restore_file_co where is_restored=1)
                and co.i_autocode not in (SELECT co_code from restore_file_co2 where is_restored =1)
                and imf.i_foreigncode = co.i_autocode
                and imf.s_fileformat in ('.jpg','.png');
            ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $rowCo = $rows[0];
    $cocode = $rowCo->co_code;


    $sql = "select rf_code, fname, imf.i_width, imf.i_height
            from restore_file_co_analyse2, restore_files, image_file imf
            where co_code = :cocode
            and i_foreigncode = co_code
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

        <div id="entete">
            <p>Votre nom :
                <select name="name">
                    <option value="sounia" selected="<?php echo ($name == 'sounia')?'selected':""; ?>">Sounia</option>
                    <option value="antoine" selected="<?php echo ($name == 'antoine')?'selected':""; ?>">Antoine</option>
                    <option value="alex" selected="<?php echo ($name == 'alex')?'selected':""; ?>">Alex</option>
                </select>
            </p>

            <p><input type="submit"></p>
        </div>

        <div id="tofind">
            <div>
                Image recherchée :
            </div>

            <img height="240px" src="<?php echo 'pictures/olddir/thumbdir/'.$rowCo->co_code.'.jpg' ?>">
            <p>Width: <?php echo $rowCo->width; ?></p>
            <p>Height: <?php echo $rowCo->height; ?></p>
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
                                    <p>Width: '.$rowRf->i_width.'</p>
                                    <p>Height: '.$rowRf->i_height.'</p>
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