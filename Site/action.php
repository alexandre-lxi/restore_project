<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 07/09/17
 * Time: 21:00
 */



$name = (isset($_POST['name']))?$_POST['name']:'';


header("Location: http://verif.iris-solutions.fr/controle.php?name=".$name);