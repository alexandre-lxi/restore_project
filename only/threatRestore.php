<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20/08/17
 * Time: 11:47
 */



function testFile()
{
    $VALEUR_hote = 'prod.kwk.eu.com';
    $VALEUR_port = '3306';
    $VALEUR_nom_bd = 'onlyfrance';
    $VALEUR_user = 'alaidin';
    $VALEUR_mot_de_passe = 'alaidin';

    $dThumb = '/home/ubuntu/new_onlyfrance/pictures2/thumbdir/';
    $dWeb = '/home/ubuntu/new_onlyfrance/pictures2/webdir/';
    $dori = '/home/ubuntu/new_onlyfrance/pictures2/oridir/';

    try {
        $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

        /*$sqlSel = "SELECT *
                   FROM onlyfrance.restore_files2 rf, onlyfrance.restore_file_co2 rco
                   WHERE rco.rf_code = rf.id
                   AND rco.is_restored = 0
                   and rf.to_restore = 1
                   and s_format in ('jpg','png','tif')";*/
        $sqlSel = "select DISTINCT fname, co.i_autocode, rf.s_format, co.s_reference
                    from container co, image_file imf, restore_files2 rf
                    where s_reference like '%Nancy%'
                    and imf.i_foreigncode = co.i_autocode
                    and imf.i_width = rf.width
                    and imf.i_height = rf.height
                    and imf.i_filesize = rf.fsize
                    and imf.s_fileformat = concat('.', rf.s_format)
                    and co.i_autocode not in (select co_code from restore_file_co2)";

        $reqSel = $pdo->prepare($sqlSel);
        $reqSel->execute();

        $rows = $reqSel->fetchAll(PDO::FETCH_OBJ);

        foreach ($rows as $row) {
            $file = $row->fname;
            $fname = $row->co_code.'.'.$row->s_format;
            $cocode = $row->co_code;

            $img = new Imagick();

            $img->readImage($file);

            echo $file."\n";

            if ($img->getImageWidth() > $img->getImageHeight()) {
                $img->resizeImage(300, 0, Imagick::FILTER_LANCZOS, 1);
                $img->writeImage($dThumb.$cocode.'.jpg');
                $img->clear();
                $img->readImage($file);
                $img->resizeImage(1024, 0, Imagick::FILTER_LANCZOS, 1);
                $img->writeImage($dWeb.$cocode.'.jpg');
                $img->clear();
            } else {
                $img->resizeImage(0, 300, Imagick::FILTER_LANCZOS, 1);
                $img->writeImage($dThumb.$cocode.'.jpg');
                $img->clear();
                $img->readImage($file);
                $img->resizeImage(0, 1024, Imagick::FILTER_LANCZOS, 1);
                $img->writeImage($dWeb.$cocode.'.jpg');
                $img->clear();
            }

            if (file_exists($dWeb.$cocode.'.jpg') && file_exists($dThumb.$cocode.'.jpg')) {
                shell_exec('mv '.$file.' '.$dori.$fname);

                shell_exec('wput '.$dWeb.$cocode.'.jpg ftp://onlyfrance:azE53fl95ghHtrq34@prod.kwk.eu.com/webdir/'.$cocode.'.jpg');
                shell_exec('wput '.$dThumb.$cocode.'.jpg ftp://onlyfrance:azE53fl95ghHtrq34@prod.kwk.eu.com/thumbdir/'.$cocode.'.jpg');
                shell_exec('wput '.$dori.$fname.' ftp://onlyfrance:azE53fl95ghHtrq34@prod.kwk.eu.com/oridir/'.$fname);


                if (file_exists($dori.$fname)) {
                    $sql = "update onlyfrance.restore_files2 set is_restored = 1 where id = :id";
                    $rqt = $pdo->prepare($sql);
                    $rqt->bindValue(':id', $row->id, PDO::PARAM_INT);
                    $rqt->execute();

                    $sql = "update onlyfrance.restore_file_co2 set is_restored =1 where rf_code = :rfcode and co_code = :cocode ";
                    $rqt = $pdo->prepare($sql);
                    $rqt->bindValue(':rfcode', $row->id, PDO::PARAM_INT);
                    $rqt->bindValue(':cocode', $cocode, PDO::PARAM_INT);
                    $rqt->execute();
                }
            }
        }
    } catch (PDOException $Exception) {
        echo $Exception->getMessage().' : '.$Exception->getCode();
    }
}


testFile();