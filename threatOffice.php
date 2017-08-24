<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 24/08/17
 * Time: 09:33
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT *
      FROM restore_files
      WHERE s_format in ('docx', 'pptx', 'doc', 'ppt', 'xls','xlsx')
      and is_restored = 0";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    foreach ($rows as $row){
        $sql = "select co.i_autocode, i_filesize, co.s_reference
                from container co, image_file imf
                where co.i_autocode = imf.i_foreigncode
                and s_fileformat = concat('.', :sformat)
                and i_filesize = :fsize
                and co.i_autocode not in (select co_code from restore_file_co)";

        $req = $pdo->prepare($sql);
        $req->bindValue(':fsize', $row->fsize, PDO::PARAM_INT);
        $req->bindValue('sformat', $row->s_format, PDO::PARAM_STR);
        $req->execute();

        $cos = $req->fetchAll(PDO::FETCH_OBJ);

        if (count($cos ) == 1){
            echo 'Single : '.$row->id.' to '.$cos[0]->i_autocode."\n";

            $sql = "insert into restore_file_co (rf_code, co_code, is_restored) values (:rf_code, :co_code, FALSE )";
            $req = $pdo->prepare($sql);
            $req->bindValue(':rf_code',$row->id, PDO::PARAM_INT);
            $req->bindValue(':co_code',$cos[0]->i_autocode, PDO::PARAM_INT);
            $req->execute();

            $sql = "update restore_files set to_restore = 1 where id = :id";
            $req = $pdo->prepare($sql);
            $req->bindValue(':id',$row->id, PDO::PARAM_INT);
            $req->execute();

        }elseif(count($cos) > 1){
            $lref = array();
            $ref = '';

            echo 'Multi : '.$row->id."\n";

            foreach ($cos as $co) {
                if (array_key_exists($co->s_reference, $lref)){
                    $lref[$co->s_reference] = $lref[$co->s_reference] +1;
                    $ref = $co->s_reference;
                }else{
                    $lref[$co->s_reference] = 1;
                }
            }

            if (count($lref) == 1){
                echo "\t".' Ref:'.$ref."\n";

                foreach ($cos as $co) {
                    $sql = "insert into restore_file_co (rf_code, co_code, is_restored) values (:rf_code, :co_code, FALSE )";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':rf_code',$row->id, PDO::PARAM_INT);
                    $req->bindValue(':co_code',$co->i_autocode, PDO::PARAM_INT);
                    $req->execute();

                    $sql = "update restore_files set to_restore = 1 where id = :id";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':id',$row->id, PDO::PARAM_INT);
                    $req->execute();
                }
            }else{
                echo "\t".' ####:'.count($lref)."\n";

                foreach ($cos as $co) {
                    $sql = "insert into restore_file_co_analyse (rf_code, co_code, to_restore, is_restored) values (:rf_code, :co_code, FALSE, FALSE )";
                    $req = $pdo->prepare($sql);
                    $req->bindValue(':rf_code',$row->id, PDO::PARAM_INT);
                    $req->bindValue(':co_code',$co->i_autocode, PDO::PARAM_INT);
                    $req->execute();
                }
            }
        }else{
            echo '';
            //echo 'Empty : '.$row->id.' fsize : '.$row->fsize."\n";
        }
    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}