<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26/08/17
 * Time: 09:36
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';


$taux = 0.1;
$vals[1] = array(
    'r'=> array('min'=>0,'max'=>0),
    'g'=> array('min'=>0,'max'=>0),
    'b'=> array('min'=>0,'max'=>0));
$tCols[1] = array('p1_r','p1_g','p1_b');
$res = array(1=>0);
$icodes = array();

for ($i =1; $i <= 10; $i++){
    $tCols[$i] = array('p'.$i.'_r','p'.$i.'_g','p'.$i.'_b');
}


try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT rfcode , p1_r, p1_g, p1_b, p1_a, p2_r, p2_g, p2_b, p2_a, p3_r, p3_g, p3_b, p3_a, p4_r, p4_g, p4_b, p4_a, p5_r, p5_g, p5_b, p5_a, p6_r, p6_g, p6_b, p6_a, p7_r, p7_g, p7_b, p7_a, p8_r, p8_g, p8_b, p8_a, p9_r, p9_g, p9_b, p9_a, p10_r, p10_g, p10_b, p10_a
        FROM restore_nfile_colors where true";

    $sql .= " and (";

    for ($i =1; $i <= 10; $i++){
        $sql .= (($i>1)?" and ":"").$tCols[$i][0]." <> 255 and ".$tCols[$i][1]." <> 255 and ".$tCols[$i][2]." <> 255 ";
        $sql .= " and ".$tCols[$i][0]." <> 0 and ".$tCols[$i][1]." <> 0 and ".$tCols[$i][2]." <> 0";
    }

    $sql .= ") ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 1;

    foreach ($rows as $row) {
        echo $nb."\n";
        $nb++;

        for ($i =1; $i <= 10; $i++){
            $rcol = $tCols[$i][0];
            $rval = $row->$rcol;

            $gcol = $tCols[$i][1];
            $gval = $row->$gcol;

            $bcol = $tCols[$i][2];
            $bval = $row->$bcol;

            $vals[$i]['r']['min'] = $rval*(1-$taux);
            $vals[$i]['r']['max'] = $rval*(1+ $taux);
            $vals[$i]['g']['min'] = $gval*(1-$taux);
            $vals[$i]['g']['max'] = $gval*(1+ $taux);
            $vals[$i]['b']['min'] = $bval*(1-$taux);
            $vals[$i]['b']['max'] = $bval*(1+ $taux);
        }

        $sql = "SELECT distinct fname, icode
        FROM restore_ofile_colors where true";

        $sql .= " and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" and ":"").$tCols[$i][0]." between ".$vals[$i]['r']['min']." and ".$vals[$i]['r']['max'];
            $sql .= " and ".$tCols[$i][1]." between ".$vals[$i]['g']['min']." and ".$vals[$i]['g']['max'];
            $sql .= " and ".$tCols[$i][2]." between ".$vals[$i]['b']['min']." and ".$vals[$i]['b']['max'];
        }

        $sql .= ")";

        $reqSel = $pdo->prepare($sql);
        $reqSel->execute();

        $rowsSel = $reqSel->fetchAll(PDO::FETCH_OBJ);

        if (count($rowsSel)>0){
            if (isset($res[count($rowsSel)])){
                $res[count($rowsSel)] = $res[count($rowsSel)]+1;
            }else{
                $res[count($rowsSel)] = 0;
            }
        }
    }

    print_r($res);

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

