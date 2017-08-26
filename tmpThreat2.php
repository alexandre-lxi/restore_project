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
      WHERE s_format in ('pdf')
      ";

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $nb = 0;


    foreach ($rows as $row) {
        $vid = $row->fname;

        if (file_exists($vid)) {
            $output = shell_exec("pdfinfo ".$vid);

            preg_match('/Pages:\s+([0-9]+)/', $output, $pagecountmatches);
            $pagecount = $pagecountmatches[1];

            preg_match('/Page size:\s+([0-9]{0,5}\.?[0-9]{0,3}) x ([0-9]{0,5}\.?[0-9]{0,3})/', $output, $pagesizematches);
            $width = round($pagesizematches[1]/2.83);
            $height = round($pagesizematches[2]/2.83);

            echo "pagecount = ".$pagecount." width = ".$width." height = ".$height."\n";
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

    $regex_sizes = "/Video: ([^,]*), ([^,]*), ([0-9]{1,4})x([0-9]{1,4})/"; // or : $regex_sizes = "/Video: ([^\r\n]*), ([^,]*), ([0-9]{1,4})x([0-9]{1,4})/"; (code from @1owk3y)
    if (preg_match($regex_sizes, $output, $regs)) {
        $codec = $regs [1] ? $regs [1] : null;
        $width = $regs [3] ? $regs [3] : null;
        $height = $regs [4] ? $regs [4] : null;
    }else{
        $codec = '';
        $width = 0;
        $height = 0;
    }

    $regex_duration = "/Duration: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}).([0-9]{1,2})/";
    if (preg_match($regex_duration, $output, $regs)) {
        $hours = $regs [1] ? $regs [1] : 0;
        $mins = $regs [2] ? $regs [2] : 0;
        $secs = $regs [3] ? $regs [3] : 0;
        $ms = $regs [4] ? $regs [4] : 0;
    }else{
        $hours = 0;
        $mins = 0;
        $secs = 0;
        $ms = 0;
    }

    return array('codec' => $codec,
        'width' => $width,
        'height' => $height,
        'hours' => $hours,
        'mins' => $mins,
        'secs' => $secs,
        'ms' => $ms
    );

}

function _human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
