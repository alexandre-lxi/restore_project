<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 07/09/17
 * Time: 21:00
 */

$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$name = (isset($_POST['name']))?$_POST['name']:'';
$cocode = (isset($_POST['cocode']))?$_POST['cocode']:'';
$rfcode = (isset($_POST['list']))?$_POST['list']:'';
$ttrfcode=  (isset($_POST['ttrfcode']))?$_POST['ttrfcode']:'';

echo "name:". $name.'<br>';
echo 'cocode:'.$cocode.'<br>';
echo 'rfcode:'.$rfcode.'<br>';


if ($name == ''){
    $error = 'Vous devez vous identifier !';

    header('Location: http://54.194.136.252/controle.php?errors='.$error);
    exit();
}


try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $pdo->beginTransaction();

    $sql = "update restore_activity set click=click+1, fusion=fusion+:nb where name=:idname";
    $reqRA = $pdo->prepare($sql);
    $reqRA->bindValue(':idname', $name, PDO::PARAM_STR);
    $reqRA->bindValue(':nb', ($rfcode=='')?0:1,PDO::PARAM_INT);
    $reqRA->execute();

    if ($rfcode <> '') {
        $sql = "INSERT INTO restore_file_co3 VALUES (:rfcode, :cocode, FALSE, :who)";
        $reqrf3 = $pdo->prepare($sql);
        $reqrf3->bindValue(':rfcode', $rfcode, PDO::PARAM_INT);
        $reqrf3->bindValue(':cocode', $cocode, PDO::PARAM_INT);
        $reqrf3->bindValue(':who', $name, PDO::PARAM_INT);
        $reqrf3->execute();

        $sql = "update restore_files set to_restore=1 where id=:rfcode";
        $reqRF = $pdo->prepare($sql);
        $reqRF->bindValue(':rfcode',$rfcode,PDO::PARAM_INT);
        $reqRF->execute();

        $sql = "update restore_file_co_analyse2 set to_restore=1 where rf_code=:rfcode and co_code=:cocode";
        $reqRF = $pdo->prepare($sql);
        $reqRF->bindValue(':rfcode',$rfcode,PDO::PARAM_INT);
        $reqRF->bindValue(':cocode',$cocode,PDO::PARAM_INT);
        $reqRF->execute();
    }else{
        $sql = "update restore_file_co_analyse2 set to_restore=2 where rf_code=:rfcode  and co_code=:cocode";
        $reqRF = $pdo->prepare($sql);
        $reqRF->bindValue(':rfcode',$ttrfcode,PDO::PARAM_INT);
        $reqRF->bindValue(':cocode',$cocode,PDO::PARAM_INT);
        $reqRF->execute();
    }


    $pdo->commit();
    header('Location: http://54.194.136.252/controle.php?name='.$name);

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    $pdo->rollBack();
    $error = $Exception->getMessage().' : '.$Exception->getCode();
    header('Location: http://54.194.136.252/controle.php?errors='.$error);
    exit();
}