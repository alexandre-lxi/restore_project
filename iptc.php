<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 23/08/17
 * Time: 18:31
 */

class iptc
{

    var $h_codesIptc; /* $h_codesIptc : (tableau associatif) contient les codes des champs IPTC associés à un libellé */
    var $h_cheminImg; /* $h_cheminImg : (chaine) contient le chemin complet du fichier d'image */
    var $h_iptcData;  /* $h_iptcData  : (chaine) contient les données encodées de l'iptc de l'image */

    var $h_codesIptc2; /* $h_codesIptc : (tableau associatif) contient les codes des champs IPTC associés à un libellé */
    var $h_fieldIptc;

    function setImg($cheminImg)
    {
        $this->h_codesIptc2 = array("005" => "ip_name",
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
        $this->h_fieldIptc = array_flip($this->h_codesIptc2);
        $this->h_cheminImg = $cheminImg;

        // On extrait les données encodées de l'iptc
        getimagesize($this->h_cheminImg, $info);
        if (isset($info["APP13"]))
            $this->h_iptcData = $info["APP13"];
        else
            $this->h_iptcData = false;
    }

    function readIPTC()
    {
        $tblIPTC = iptcparse($this->h_iptcData);

        $lesIptc = array();
        $this->_initIptcArray($lesIptc);

        if (!is_array($tblIPTC))
            return false;

        print_r($lesIptc);
        print_r($tblIPTC);

//        while ((is_array($tblIPTC)) && (list($codeIPTC, $valeurIPTC) = each($tblIPTC))) {
//
//
//            $codeIPTC = str_replace("2#", "", $codeIPTC);
//
//            if (($codeIPTC != "000") && ($codeIPTC != "140")) {
//                while (list($index,) = each($valeurIPTC)) {
//                    echo $index."\n";
//                    if ($codeIPTC == "025" || $codeIPTC == "020") {
//                        if (isset($valeurIPTC[$index]))
//                            $lesIptc[$this->_getIptcLabel($codeIPTC)] .= $valeurIPTC[$index].";";
//                    } else {
//                        if (isset($valeurIPTC[$index]))
//                            $lesIptc[$this->_getIptcLabel($codeIPTC)] .= $valeurIPTC[$index];//.$retourLigne;
//                    }
//                }
//            }
//        }
//        $lesIptc["ip_keywords"] = explode(";", $lesIptc["ip_keywords"]);
        //$lesIptc["s_supcategories"] = explode(";",$lesIptc["s_supcategories"]);
        if (is_array($lesIptc)) return $lesIptc; else return false;
    }

    function _initIptcArray(&$tab)
    {
        foreach ($this->h_codesIptc2 as $v) {
            $tab[$v] = '';
        }
    }

    function _getIptcLabel($code)
    {
        if (isset($this->h_codesIptc2[$code]))
            return $this->h_codesIptc2[$code];
        else {
            return "";
        }
    }
}
