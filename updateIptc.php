<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21/08/17
 * Time: 09:01
 */

include "iptc.php";

$VALEUR_hote = 'prod.kwk.eu.com';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

$cds = array("005" => "ip_name",
    "010" => "ip_urgency",
    "015" => "ip_category",
    "020" => "ip_supcategories",
    "025" => "ip_keywords",
    "040" => "ip_instruction",
    "055" => "ip_created",
    "080" => "ip_byline",
    "085" => "ip_bylinetitle",
    "090" => "ip_city",
    "095" => "ip_state",
    "100" => "ip_country_code",
    "101" => "ip_country",
    "103" => "ip_reference",
    "105" => "ip_headline",
    "110" => "ip_credits",
    "115" => "ip_source",
    "116" => "ip_copyright",
    "120" => "ip_caption",
    "121" => "ip_captionwriter2",
    "122" => "ip_captionwriter");

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = 'SELECT *
        FROM restore_files
        WHERE s_format = "jpg"
        and id = 86552
        and id not in (select id from restore_file_iptc )
            LIMIT 100';

//ztrace($sql);

    $req = $pdo->prepare($sql);
    $req->execute();

    $rows = $req->fetchAll(PDO::FETCH_OBJ);
    $nb = 0;

    $iptc = new iptc();

    foreach ($rows as $row){
	echo $row->fname;

        $iptc->setImg($row->fname);

        $liptc = $iptc->readIPTC();

        if ($liptc == false)
            continue;

        $sql = "insert into restore_file_iptc (rfid, ip_name, ip_urgency, ip_category, ip_supcategories, ip_instruction, ip_created, ip_byline, ip_bylinetitle, ip_city, ip_state, ip_country_code, ip_country, ip_reference, ip_headline, ip_credits, ip_source, ip_copyright, ip_caption, ip_captionwriter2, ip_captionwriter) 
          values (:rfid, :ip_name, :ip_urgency, :ip_category, :ip_supcategories, :ip_instruction, :ip_created, :ip_byline, :ip_bylinetitle, :ip_city, :ip_state, :ip_country_code, :ip_country, :ip_reference, :ip_headline, :ip_credits, :ip_source, :ip_copyright, :ip_caption, :ip_captionwriter2, :ip_captionwriter)";

        $req = $pdo->prepare($sql);
        $req->bindValue(':rfid', $row->id, PDO::PARAM_INT);

        foreach ($cds as $cd) {
	    if ($cd != 'ip_keywords'){
                 $req->bindValue(':'.$cd, $liptc[$cd], PDO::PARAM_STR);
	    }
        }

        $req->execute();

        $sql = 'SELECT max(id) id FROM restore_file_iptc';
        $req = $pdo->prepare($sql);
        $req->execute();
        $idiptc = $req->fetchAll(PDO::FETCH_OBJ);
        $idiptc = $idiptc[0]->id;

        $sql = 'insert into restore_file_iptc_kwords (ipid, kword) values (:ipid, :kword)';
        $req = $pdo->prepare($sql);
        $req->bindValue(':ipid', $idiptc, PDO::PARAM_INT);

        foreach ($liptc['ip_keywords'] as $kword) {
            $req->bindValue(':kword', $kword, PDO::PARAM_STR);
            $req->execute();
        }
    }

} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}
