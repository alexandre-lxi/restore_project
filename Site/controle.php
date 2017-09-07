<?php

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co_code 
            from restore_file_co_analyse2
            where is_restored <> 5
            and co_code <> 1
            and co_code in (select i_autocode from container where b_isintrash <> 0) 
            group by co_code 
            having count(*)>1
            order by count(*) desc
            limit 1";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $row = $rows[0];

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
        <img src="<?php echo 'pictures/olddir/thumbdir/'.$row->co_code.'jpg' ?>">
    </div>

    <div id="contenu">
        Contenu
    </div>
</div>

<div id="footer">
    Pied de Page
</div>

</body>
</html>