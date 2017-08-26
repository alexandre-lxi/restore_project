<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 24/08/17
 * Time: 00:34
 */

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$ffmpeg_path = 'ffmpeg'; //or: /usr/bin/ffmpeg , or /usr/local/bin/ffmpeg - depends on your installation (type which ffmpeg into a console to find the install path)
$vid = 'PATH/TO/VIDEO'; //Replace here!

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT *
      FROM restore_files
      WHERE s_format in ('mov','mp4','mpg','mpeg','m2v','wmv','flv')
      ";

//    'avi':
//		case 'mpg':
//		case 'mpeg':
//		case 'm2v':
//		case 'wmv':
//		case 'mov':
//		case 'flv':
//		case 'mp4':

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $nb = 0;


    foreach ($rows as $row) {
        $vid = $row->fname;

        if (file_exists($vid)) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $vid); // check mime type
            finfo_close($finfo);

            if (preg_match('/video\/*/', $mime_type)) {
                $video_attributes = _get_video_attributes($vid, $ffmpeg_path);
                $length = ((($video_attributes['hours'] * 3600) + ($video_attributes['mins'] * 60) + $video_attributes['secs'])*100) + $video_attributes['ms'];

                $sql = 'update restore_files set length = :length/100 where id=:id';
                $req = $pdo->prepare($sql);
                $req->bindValue(':length', $length, PDO::PARAM_INT);
                $req->bindValue(':id', $row->id, PDO::PARAM_INT);
                $req->execute();

		echo $row->id.' '.$length."\n";

            }
        }
    }
} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

function _get_video_attributes($video, $ffmpeg) {

    $command = $ffmpeg . ' -i ' . $video . ' -vstats 2>&1';
    $output = shell_exec($command);

    $regex_duration = "/Duration: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}).([0-9]{1,2})/";
    if (preg_match($regex_duration, $output, $regs)) {
        $hours = $regs [1] ? $regs [1] : null;
        $mins = $regs [2] ? $regs [2] : null;
        $secs = $regs [3] ? $regs [3] : null;
        $ms = $regs [4] ? $regs [4] : null;

        return array(
            'hours' => $hours,
            'mins' => $mins,
            'secs' => $secs,
            'ms' => $ms
        );
    }
    else
        return false;

}

function _human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
