<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>

<?php 
echo "pictures/".$_GET['vid'];
?>

<br>

<video width="600" controls>
    <?php 	
$src = "pictures/".$_GET['vid']	;
echo '<source src="'.$src.'" >'; ?>
	
</video>
</body>
</html>
