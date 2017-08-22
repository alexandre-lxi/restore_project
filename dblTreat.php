<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 13:12
 */
$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);


    $sql = "select
      db.i_code, db.oldfile, db.restore, imf.s_filename, co.s_reference, imf.i_width, imf.i_height, imf.i_filesize /1024/1024 fs,
      co.i_autocode, co.s_reference, co.b_isintrash, co.dt_created
    from `total-refontedam`.restore_dbl db, `total-refontedam`.image_file imf, `total-refontedam`.container co
    where co.i_autocode = imf.i_foreigncode
      and co.i_autocode = db.i_code    
      
      and oldfile not in (select oldfile from restore_dbl where restore = 1)
      and co.i_autocode not in (select i_code from restore_dbl where restore = 1)";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = 0;
    $isTreat = array();

    foreach ($rows as $row) {
        $sql = "select distinct imf.s_filename
            from `total-refontedam`.restore_dbl db, `total-refontedam`.image_file imf, `total-refontedam`.container co
            where co.i_autocode = imf.i_foreigncode
              and co.i_autocode = db.i_code
              and oldfile= :oldf";

        $req = $pdo->prepare($sql);
        $req->bindValue(':oldf', $row->oldfile, PDO::PARAM_STR);
        $req->execute();

        $filenames = $req->fetchAll(PDO::FETCH_OBJ);

        if (count($filenames) ==1){
            $fname = $filenames[0]->s_filename;

            echo "Single file=> Code:".$row->i_code." - ".$fname."\n";

            if (array_key_exists($fname, $isTreat)){
                $isTreat[$fname] = $isTreat[$fname] + 1;;
            }else{
                $isTreat[$fname] = 1;
                $sql = "update restore_dbl set restore = 1 where oldfile = :oldf";
                $req = $pdo->prepare($sql);
                $req->bindValue(':oldf', $row->oldfile, PDO::PARAM_STR);
                $req->execute();
            }

        }
    }

    echo "\n\n";
    print_r($isTreat);

    echo $nb;
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}