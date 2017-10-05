<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 13:59
 */

$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$dirsource = '/media/alex/MEDIATEC-DISK2-3T/*/';
$dest = '/home/alex/Documents/LXI/Clients/KWK/support/total/tmp/';

function glob_recursive($directory, &$directories = array()) {
    foreach(glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
        $directories[] = $folder;
        glob_recursive("{$folder}/*", $directories);
    }
}

function findFiles($directories, $name) {
    $files = array ();
    foreach($directories as $directory) {
            foreach(glob("{$directory}/".$name) as $file) {
                $files[] = $file;
            }
    }
    return $files;
}

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "select co.i_autocode, imf.i_width, imf.i_height, co.s_reference, imf.s_fileformat 
            from container co, image_file imf
            where co.i_autocode not in (select co_code from restore_file_co where is_restored=1)
            and co.i_autocode not in (SELECT co_code from restore_file_co2)
            and co.i_autocode not in (select co_code from restore_file_co3)
            and co.i_autocode not in (select co_code from restore_nf_file)
            and b_isintrash =0            
            and imf.i_foreigncode = co.i_autocode
            and imf.s_fileformat in ('.psd', '.jpg','.png')
            ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = count($rows);

    glob_recursive($dirsource, $directories);

    foreach ($rows as $row) {

        echo "REF : ".$dirsource.$row->s_reference."\n";
        $files = findFiles($directories, $row->s_reference);

        if (count($files) == 1){
            $file = $files[0];
            copy($file, $dest.$row->i_autocode.$row->s_fileformat);
        }

    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}