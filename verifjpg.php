<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/08/17
 * Time: 18:47
 */

include 'iptc.php';

echo "<html>";

?>
<html>
<head>
    <style>
        table {
            border-collapse: collapse; /* Les bordures du tableau seront collées (plus joli) */
        }

        td {
            border: 1px solid black;
        }
    </style>


</head>
<body>
<?php

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

$fname = 'pictures/'.$_GET['img'];
$size = getimagesize($fname, $info);

$iptc = new iptc();
$iptc->setImg($fname);

$liptc = $iptc->readIPTC();
?>
<table>
    <?php
    foreach ($cds as $cd) {
        ?>
        <tr>
            <td><?php echo $cd; ?></td>
            <td><?php echo $liptc[$cd]; ?></td>
        </tr>

        <?php
    }
    ?>

</table>

<?php



?>

</body>
</html>
