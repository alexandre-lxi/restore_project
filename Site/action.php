<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 07/09/17
 * Time: 21:00
 */



$name = (isset($_POST['name']))?$_POST['name']:'';
$cocode = (isset($_POST['cocode']))?$_POST['cocode']:'';
$rfcode = (isset($_POST['list']))?$_POST['list']:'';


echo "name:". $name.'<br>';
echo 'cocode:'.$cocode.'<br>';
echo 'rfcode:'.$rfcode.'<br>';

//header("Location: http://verif.iris-solutions.fr/controle.php?name=".$name);