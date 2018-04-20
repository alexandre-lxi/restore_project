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

$dirsource = '/media/sf_partage_ub/SUPPORTCOM/*/';
$dest = '/media/sf_partage_ub/total/';

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
            where b_isintrash =0            
            and imf.i_foreigncode = co.i_autocode
            and co.i_autocode in (
  select i_containercode
    from container_topic2 c2
    inner JOIN topic2 t ON c2.i_foreigncode = t.i_autocode    
    where i_leftidx>=8629 and i_rightidx <=8700
)
order by 1 desc
            ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);

    $nb = count($rows);

    glob_recursive($dirsource, $directories);

    foreach ($rows as $row) {
        $ff = "";
        $fm = 0;

        echo "REF : ".$dirsource.$row->s_reference."\n";
        $files = findFiles($directories, $row->s_reference);

        if (count($files) == 1){
            echo "COPY"."\n";
            $file = $files[0];
            rename($file, $dest.$row->i_autocode.$row->s_fileformat);
        }elseif (count($files) > 1){
            foreach ($files as $file) {
                if (filemtime($file) > $fm){
                    $fm = filemtime($file) ;
                    $ff = $file;
                }
            }

            echo "COPY NN"."\n";
            rename($file, $dest.$row->i_autocode.$row->s_fileformat);
        }else{
            echo "NOT FOUND"."\n";
        }

    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}