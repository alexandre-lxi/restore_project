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


$fname = 'pictures/tmpdir/'.$_GET['imgtmp'];
$fname2 = 'pictures/olddir/thumbdir/'.$_GET['imgold'];


?>
<table>
        <tr>
            <td><img src="<?php echo $fname ?>" width="500"></td>
            <td><img src="<?php echo $fname2 ?>" width="500"></td>
        </tr>


</table>


<?php



?>

</body>
</html>
