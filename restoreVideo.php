<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */

$VALEUR_hote='prod.kwk.eu.com';
$VALEUR_port='3306';
$VALEUR_nom_bd='total-refontedam';
$VALEUR_user='alaidin';
$VALEUR_mot_de_passe='alaidin';

try{
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);
}catch( PDOException $Exception ) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage( ).' : '.$Exception->getCode( );
}

//$dirsource    = '/home/ubuntu/tri/toRestore/';
//$dest         = '/home/ubuntu/tri/oridir/';

$dirsource = '/home/alex/Documents/IRIS/Clients/kwk/total/tmp/';
$dest = '/home/alex/Documents/IRIS/Clients/kwk/total/tmp/dest/';
$files = scandir($dirsource);

foreach ($files as $file){
    if (!is_dir($file)) {
        $fileSize = filesize($dirsource.$file);
        $fileDet = explode('.', $file);
        $fileExt = $fileDet[1];

        echo $file.':'.$fileExt.':'.$fileSize."\n";

        $sql = "SELECT s_path
            FROM `total-refontedam`.image_file imf, `total-refontedam`.container co, `total-refontedam`.image_infofr info
            WHERE co.i_autocode = imf.i_foreigncode
              AND co.i_autocode = info.i_foreigncode
              AND right(co.s_reference,3) = '".$fileExt."'
              AND i_filesize = ".$fileSize;

        $rows = $pdo->query($sql);

        if ($rows->rowCount() == 1) {
            $row = $rows->fetchAll();
            $bname = basename($row[0]['s_path']);
            $oldFile = $dirsource.$file;
            $newFile = $dest.$bname;
            if (copy( $oldFile, $newFile)){
                echo "Copy OK";
            }else{
                echo "COPY KO";
            }
        } elseif ($rows->rowCount() == 0) {
            echo "0 ligne";
        } else {
            echo $rows->rowCount().' lignes'."\n";
        }

        echo "\n";
    }
}
