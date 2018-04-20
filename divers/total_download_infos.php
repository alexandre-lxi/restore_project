<?php

$VALEUR_hote = '127.0.0.1';
$VALEUR_port = '3306';
$VALEUR_nom_bd = 'total-refontedam';
$VALEUR_user = 'alaidin';
$VALEUR_mot_de_passe = 'alaidin';

try {
    $pdo = new PDO('mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd, $VALEUR_user, $VALEUR_mot_de_passe);

    $sql = "SELECT 'total' reg, year(fo.dt_created) annee, count(*) nb
        FROM _frontorderdetail fd
          INNER JOIN _frontorder fo ON (fo.i_autocode=fd.i_ordercode)
        WHERE fo.i_type&16
        AND DATE(fo.dt_created)>='2017-01-01'
        and fd.i_containercode in (select container_topic4.i_containercode from container_topic4)
          group by year(fo.dt_created)
        union all
        SELECT 'dag' reg, year(fo.dt_created) annee, count(*) nb
        FROM _frontorderdetail fd
          INNER JOIN _frontorder fo ON (fo.i_autocode=fd.i_ordercode)
        WHERE fo.i_type&16
        AND DATE(fo.dt_created)>='2017-01-01'
        and fd.i_containercode in (select container_topic4.i_containercode from container_topic4)
        and fd.i_containercode in (select i_foreigncode
            from image_infofr ifr
            where s_copyright like '%dagobert%'
            )
          group by year(fo.dt_created)
        order by annee;";

    $req = $pdo->prepare($sql);
    $req->execute();
    $globalDownloads = $req->fetchAll(PDO::FETCH_CLASS);

    foreach ($globalDownloads as $item) {
        if ($item->annee == 2017){
            if ($item->reg == 'total'){
                $gdwn['2017']['total'] = $item->nb;
            }else{
                $gdwn['2017']['dag'] = $item->nb;
            }
        }else{
            if ($item->reg == 'total'){
                $gdwn['2018']['total'] = $item->nb;
            }else{
                $gdwn['2018']['dag'] = $item->nb;
            }
        }
    }

    $sql = "select 'Total :' reg, count(*) nb
        from container
        where i_autocode in (select container_topic4.i_containercode from container_topic4)
        and b_isintrash =  0
        and i_popularity = 0
        union all
        
        select 'Dagobert :' reg, count(*) nb
        from container
        where i_autocode in (select container_topic4.i_containercode from container_topic4)
        and i_autocode in (select i_foreigncode
            from image_infofr ifr
            where s_copyright like '%dagobert%')
        and b_isintrash =  0
        and i_popularity = 0";

    $req = $pdo->prepare($sql);
    $req->execute();
    $global = $req->fetchAll(PDO::FETCH_CLASS);


    $sql = "SELECT fd.s_reference as label, count(*) nb, min(fo.dt_created) dmin, max(fo.dt_created) dmax, fd.i_containercode id,
          (select count(*)
            from image_infofr ifr
            where s_copyright like '%dagobert%'
            and ifr.i_foreigncode = fd.i_containercode) isDagobert
        FROM _frontorderdetail fd
          INNER JOIN _frontorder fo ON (fo.i_autocode=fd.i_ordercode)
        WHERE fo.i_type&16
        AND DATE(fo.dt_created)>='2017-01-01'
        and fd.i_containercode in (select container_topic4.i_containercode from container_topic4)
          group by fd.s_reference, fd.i_containercode
          
        order by nb desc limit 100";

    $req = $pdo->prepare($sql);
    $req->execute();
    $list = $req->fetchAll(PDO::FETCH_CLASS);




} catch (PDOException $Exception) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    echo $Exception->getMessage().' : '.$Exception->getCode();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/4-col-portfolio.css" rel="stylesheet">

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            padding-top: 54px;
        }

        @media (min-width: 992px) {
            body {
                padding-top: 56px;
            }
        }

        .portfolio-item {
            margin-bottom: 30px;
        }

        .card-img-top{
            max-width: 200px;
        }

    </style>
</head>
<body>

<div class="container" style="text-align: center">
    <h1>
        <img src="http://mediatecms.total.com/media/data/logo_mediatec.png"></h1>
    <h2>Statistiques de téléchargement Digital Marketing</h2>
</div>

<br><br>

<div class="container" style="font-size: 18px">

    <div class="row">
        <div class="col-lg-12" style="font-weight: bold; text-decoration: underline;">
            <h3>
                Nombre de médias
            </h3>
        </div>
    </div>
    <?php
    foreach ($global as $item) {
        ?>
        <div class="row">
            <div class="col-lg-2">
                <p>
                    <?php
                        echo $item->reg;
                    ?>
                </p>
            </div>
            <div class="col-lg-10">
                <p>
                    <?php
                        echo $item->nb;

                    ?>
                </p>
            </div>
        </div>
    <?php
    }
    ?>
</div>

<br><br>

<div class="container" style="font-size: 18px">

    <div class="row">
        <div class="col-lg-12" style="font-weight: bold; text-decoration: underline;">
            <h3>
                Nombre de téléchargements
            </h3>
        </div>
    </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col" colspan="2">2017</th>
                        <th scope="col" colspan="2">2018</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">Total</th>
                        <th scope="row">Dagobert</th>
                        <th scope="row">Total</th>
                        <th scope="row">Dagobert</th>
                    </tr>
                    <tr>
                        <td><?php echo $gdwn['2017']['total']; ?></td>
                        <td><?php echo $gdwn['2017']['dag']; ?></td>
                        <td><?php echo $gdwn['2018']['total']; ?></td>
                        <td><?php echo $gdwn['2018']['dag']; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
</div>

<br><br>

<div class="container">

    <div class="row">
        <div class="col-lg-12" style="font-weight: bold; text-decoration: underline;">
            <h3>
                Liste des téléchargements (top 100)
            </h3>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table">
                <tbody>
                <?php

                foreach ($list as $item) {
                    echo "<tr>";
                        echo "<td class='col-lg-2'>";

                        echo "<img src='http://mediatecms.total.com/back/account/pictures/thumbdir/".$item->id.".jpg' alt='' style='max-width: 200px'>";
                        echo "</td>";

                        echo "<td class='col-lg-8'>";
                        echo "<p style='font-weight: bold'>";
                        echo $item->label;
                        echo "</p>";

                    echo "<p>";
                    echo 'Nombre de téléchargements: '.$item->nb;
                    echo "</p>";


                    echo "<p>";
                    echo 'Dernier téléchargement: '.$item->dmax;
                    echo "</p>";

                    echo "<p>";
                    echo 'Copyright Dagobert: '.($item->isDagobert)?'Oui':'Non';
                    echo "</p>";

                        echo "</td>";

                    echo "</tr>";
                }

                ?>
                </tbody>
            </table>
        </div>
    </div>


</div>

</body>
</html>