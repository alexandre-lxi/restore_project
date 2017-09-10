<?php

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$name = (isset($_GET['name'])?$_GET['name']:'');

function findBySizes($width, $height)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $ret= array();

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT * from restore_files 
                where is_restored <> 1 
                and to_restore=0
                and width = :width 
                and height=:height
                and id not in (select rf_code  from restore_file_co3)
                and id not in (select rf_code  from restore_file_co2)
                and id not in (select rf_code  from restore_file_co where restore_files.is_restored=1)                 
                and s_format in ('jpg', 'png') 
                order by fsize desc";

        $req = $pdo->prepare($sql);
        $req->bindValue(':width',$width,PDO::PARAM_INT);
        $req->bindValue(':height',$height,PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 1;

        foreach ($rows as $row) {
            $ret[] = array(
                'width' => $row->width,
                'rfcode' => $row->id,
                'height' => $row->height,
                'fname' => $row->fname);

        }
        return $ret;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return false;
    }
}


function findByPixels($cocode)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';


    $taux = 0.15;
    $vals[1] = array(
        'r'=> array('min'=>0,'max'=>0),
        'g'=> array('min'=>0,'max'=>0),
        'b'=> array('min'=>0,'max'=>0));
    $tCols[1] = array('p1_r','p1_g','p1_b');
    $ret = array();

    for ($i =1; $i <= 10; $i++){
        $tCols[$i] = array('p'.$i.'_r','p'.$i.'_g','p'.$i.'_b');
    }

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT icode, p1_r, p1_g, p1_b, p1_a, p2_r, p2_g, p2_b, p2_a, p3_r, p3_g, p3_b, p3_a, p4_r, p4_g, p4_b, p4_a, p5_r, p5_g, p5_b, p5_a, p6_r, p6_g, p6_b, p6_a, p7_r, p7_g, p7_b, p7_a, p8_r, p8_g, p8_b, p8_a, p9_r, p9_g, p9_b, p9_a, p10_r, p10_g, p10_b, p10_a
        FROM restore_ofile_colors where icode = :cocode";
        $sql .= " and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" + ":"").$tCols[$i][0]." + ".$tCols[$i][1]." + ".$tCols[$i][2];
        }
        $sql .= ") <> 7650 and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" + ":"").$tCols[$i][0]." + ".$tCols[$i][1]." + ".$tCols[$i][2];
        }
        $sql .= ") <> 0 ";

        $req = $pdo->prepare($sql);
        $req->bindValue(':cocode',$cocode,PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 1;

        foreach ($rows as $row) {
            $nb++;

            for ($i =1; $i <= 10; $i++){
                $rcol = $tCols[$i][0];
                $rval = $row->$rcol;

                $gcol = $tCols[$i][1];
                $gval = $row->$gcol;

                $bcol = $tCols[$i][2];
                $bval = $row->$bcol;

//                $vals[$i]['r']['min'] = $rval*(1-$taux);
//                $vals[$i]['r']['max'] = $rval*(1+ $taux);
//                $vals[$i]['g']['min'] = $gval*(1-$taux);
//                $vals[$i]['g']['max'] = $gval*(1+ $taux);
//                $vals[$i]['b']['min'] = $bval*(1-$taux);
//                $vals[$i]['b']['max'] = $bval*(1+ $taux);

                $vals[$i]['r']['min'] = $rval-10;
                $vals[$i]['r']['max'] = $rval+10;
                $vals[$i]['g']['min'] = $gval-10;
                $vals[$i]['g']['max'] = $gval+10;
                $vals[$i]['b']['min'] = $bval-10;
                $vals[$i]['b']['max'] = $bval+10;
            }

            $sql = "SELECT distinct rfcode, rf.height, rf.width, rf.fname
                    FROM restore_nfile_colors, restore_files rf where rfcode = rf.id";

            $sql .= " and (";

            for ($i =1; $i <= 10; $i++){
                $sql .= (($i>1)?" and ":"").$tCols[$i][0]." between ".$vals[$i]['r']['min']." and ".$vals[$i]['r']['max'];
                $sql .= " and ".$tCols[$i][1]." between ".$vals[$i]['g']['min']." and ".$vals[$i]['g']['max'];
                $sql .= " and ".$tCols[$i][2]." between ".$vals[$i]['b']['min']." and ".$vals[$i]['b']['max'];
            }

            $sql .= ")";

            //print_r($sql);

            $reqSel = $pdo->prepare($sql);
            $reqSel->execute();

            $selRows = $reqSel->fetchAll(PDO::FETCH_OBJ);

            foreach ($selRows as $selRow) {
                $ret[] = array(
                        'rfcode' => $selRow->rfcode,
                        'width' => $selRow->width,
                        'height' => $selRow->height,
                        'fname' => $selRow->fname);

            }
        }
        return $ret;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return false;
    }
}

function findByPixelsSize($cocode, $width, $height)
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'total-refontedam';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';


    $taux = 0.15;
    $vals[1] = array(
        'r'=> array('min'=>0,'max'=>0),
        'g'=> array('min'=>0,'max'=>0),
        'b'=> array('min'=>0,'max'=>0));
    $tCols[1] = array('p1_r','p1_g','p1_b');
    $ret = array();

    for ($i =1; $i <= 10; $i++){
        $tCols[$i] = array('p'.$i.'_r','p'.$i.'_g','p'.$i.'_b');
    }

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        $sql = "SELECT icode, p1_r, p1_g, p1_b, p1_a, p2_r, p2_g, p2_b, p2_a, p3_r, p3_g, p3_b, p3_a, p4_r, p4_g, p4_b, p4_a, p5_r, p5_g, p5_b, p5_a, p6_r, p6_g, p6_b, p6_a, p7_r, p7_g, p7_b, p7_a, p8_r, p8_g, p8_b, p8_a, p9_r, p9_g, p9_b, p9_a, p10_r, p10_g, p10_b, p10_a
        FROM restore_ofile_colors where icode = :cocode";
        $sql .= " and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" + ":"").$tCols[$i][0]." + ".$tCols[$i][1]." + ".$tCols[$i][2];
        }
        $sql .= ") <> 7650 and (";

        for ($i =1; $i <= 10; $i++){
            $sql .= (($i>1)?" + ":"").$tCols[$i][0]." + ".$tCols[$i][1]." + ".$tCols[$i][2];
        }
        $sql .= ") <> 0 ";

        $req = $pdo->prepare($sql);
        $req->bindValue(':cocode',$cocode,PDO::PARAM_INT);
        $req->execute();

        $rows = $req->fetchAll(PDO::FETCH_OBJ);

        $nb = 1;

        foreach ($rows as $row) {
            $nb++;

            for ($i =1; $i <= 10; $i++){
                $rcol = $tCols[$i][0];
                $rval = $row->$rcol;

                $gcol = $tCols[$i][1];
                $gval = $row->$gcol;

                $bcol = $tCols[$i][2];
                $bval = $row->$bcol;

                $vals[$i]['r']['min'] = $rval-10;
                $vals[$i]['r']['max'] = $rval+10;
                $vals[$i]['g']['min'] = $gval-10;
                $vals[$i]['g']['max'] = $gval+10;
                $vals[$i]['b']['min'] = $bval-10;
                $vals[$i]['b']['max'] = $bval+10;

//                $vals[$i]['r']['min'] = $rval*(1-$taux);
//                $vals[$i]['r']['max'] = $rval*(1+ $taux);
//                $vals[$i]['g']['min'] = $gval*(1-$taux);
//                $vals[$i]['g']['max'] = $gval*(1+ $taux);
//                $vals[$i]['b']['min'] = $bval*(1-$taux);
//                $vals[$i]['b']['max'] = $bval*(1+ $taux);
            }

            $sql = "SELECT distinct rfcode, rf.height, rf.width, rf.fname
                    FROM restore_nfile_colors, restore_files rf where rfcode = rf.id 
                    and rf.width=:width and rf.height=:height";

            $sql .= " and (";

            for ($i =1; $i <= 10; $i++){
                $sql .= (($i>1)?" and ":"").$tCols[$i][0]." between ".$vals[$i]['r']['min']." and ".$vals[$i]['r']['max'];
                $sql .= " and ".$tCols[$i][1]." between ".$vals[$i]['g']['min']." and ".$vals[$i]['g']['max'];
                $sql .= " and ".$tCols[$i][2]." between ".$vals[$i]['b']['min']." and ".$vals[$i]['b']['max'];
            }

            $sql .= ")";

            //print_r($sql);

            $reqSel = $pdo->prepare($sql);
            $reqSel->bindValue(':width',$width,PDO::PARAM_INT);
            $reqSel->bindValue(':height',$height,PDO::PARAM_INT);
            $reqSel->execute();

            $selRows = $reqSel->fetchAll(PDO::FETCH_OBJ);

            foreach ($selRows as $selRow) {
                $ret[] = array(
                    'rfcode' => $selRow->rfcode,
                    'width' => $selRow->width,
                    'height' => $selRow->height,
                    'fname' => $selRow->fname);

            }
        }
        return $ret;
    } catch (PDOException $Exception) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        echo $Exception->getMessage().' : '.$Exception->getCode();
        return false;
    }
}

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co.i_autocode, imf.i_width, imf.i_height
            from container co, image_file imf
            where co.i_autocode not in (select co_code from restore_file_co where is_restored=1)
            and co.i_autocode not in (SELECT co_code from restore_file_co2)
            and co.i_autocode not in (select co_code from restore_file_co3)
            and b_isintrash =0
            and co.i_autocode < 60000
            and imf.i_foreigncode = co.i_autocode
            and imf.s_fileformat in ('.jpg','.png') 
            order by rand()           
            ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 0;
    foreach ($rows as $rowCo) {
        $cocode = $rowCo->i_autocode;
        if (!file_exists('/home/ubuntu/restore/olddir/thumbdir/'.$cocode.'.jpg'))
            continue;

        $rowsRf = findByPixelsSize($cocode, $rowCo->i_width, $rowCo->i_height);

        //print_r('NB1:'.count($rowsRf).'<br>');

        $rowsRf3 = findByPixels($cocode);
        //print_r('NB2:'.count($rowsRf3).'<br>');

        $rowsRf2 = findBySizes($rowCo->i_width, $rowCo->i_height);
        //print_r('NB2:'.count($rowsRf2).'<br>');

        if ((count($rowsRf) + count($rowsRf2)+ count($rowsRf3))>0)
            break;
    }


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
<?php
if (isset($_GET['error'])){
    echo '<p style="color: red;">'.$_GET['error'].'</p>';
}else{
    ?>
    <form action="action2.php" method="post">
        <input type="hidden" name="cocode" value="<?php echo $cocode; ?>">

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

            <img height="200px" src="<?php echo 'pictures/olddir/thumbdir/'.$rowCo->i_autocode.'.jpg' ?>">
            <p style="font-size: small;">Dimension: <?php echo $rowCo->i_width.'x'.$rowCo->i_height; ?></p>
        </div>


        <div id="main">
            <div>
                <ul class="ul">
                    <?php
                    foreach ($rowsRf as $rowRf) {
                        $fname = basename($rowRf['fname']);
                        $fname = explode('.',$fname);
                        $fname = 'pictures/tmpdir/'.$fname[0].'.jpg';
                        if (!file_exists($fname))
                            continue;

                        echo '<li class="li">';
                        echo '<table>';
                        echo '<tr>';
                        echo '<td>
                                    <img style="margin: 5px" src="'.$fname.'"\>
                                    <p style="font-size: small;">Dimension : '.$rowRf['width'].'x'.$rowRf['height'].'</p>                                    
                              </td>';
                        echo '<td><input type="radio" name="list" value="'.$rowRf['rfcode'].'"></td>';
                        echo '</tr>';
                        echo '</table>';
                        echo '</li>';
                    }

                    ?>

                </ul>
            </div>
            <div>
                <ul class="ul">
                    <?php
                    foreach ($rowsRf3 as $rowRf) {
                        $fname = basename($rowRf['fname']);
                        $fname = explode('.',$fname);
                        $fname = 'pictures/tmpdir/'.$fname[0].'.jpg';
                        if (!file_exists($fname))
                            continue;

                        echo '<li class="li">';
                        echo '<table>';
                        echo '<tr>';
                        echo '<td>
                                    <img style="margin: 5px" src="'.$fname.'"\>
                                    <p style="font-size: small;">Dimension : '.$rowRf['width'].'x'.$rowRf['height'].'</p>                                    
                              </td>';
                        echo '<td><input type="radio" name="list" value="'.$rowRf['rfcode'].'"></td>';
                        echo '</tr>';
                        echo '</table>';
                        echo '</li>';
                    }

                    ?>

                </ul>
            </div>
            <div>
                <ul class="ul">
                    <?php
                    foreach ($rowsRf2 as $rowRf) {
                        $fname = basename($rowRf['fname']);
                        $fname = explode('.',$fname);
                        $fname = 'pictures/tmpdir/'.$fname[0].'.jpg';
                        if (!file_exists($fname))
                            continue;

                        echo '<li class="li">';
                        echo '<table>';
                        echo '<tr>';
                        echo '<td>
                                    <img style="margin: 5px" src="'.$fname.'"\>
                                    <p style="font-size: small;">Dimension : '.$rowRf['width'].'x'.$rowRf['height'].'</p>                                    
                              </td>';
                        echo '<td><input type="radio" name="list" value="'.$rowRf['rfcode'].'"></td>';
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