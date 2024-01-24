<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Language" content="pl" />
	<meta name="Author" content="Jakub Balińskii" />
    <title>Moje Hobby</title>
    <link rel="stylesheet" href="css/style.css">
	<script src="js/timedate.js" type="text/javascript"></script>
	<script src="js/kolorujtd.js"></script>
	<script src="js/timedate.js"></script>
</head>
<body onload="startlock()">
<div id="menu">
	<a href="index.php?idp=">Strona Główna</a>
    <a href="index.php?idp=muzyka">Muzyka</a>
    <a href="index.php?idp=sztukiwalki">Sztuki Walki</a>
    <a href="index.php?idp=gotowanie">Gotowanie</a>
    <a href="index.php?idp=gry">Gry</a>
    <a href="index.php?idp=fantasyscifi">Fantasy i SciFi</a>
    <a href="index.php?idp=kontakt">Kontakt</a>
</div>

<?php
include 'cfg.php';
$conn = OpenCon();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
include 'showpage.php';
if($_GET['idp'] == '') $id = 1;
if($_GET['idp'] == 'muzyka') $id = 2;
if($_GET['idp'] == 'sztukiwalki') $id = 3;
if($_GET['idp'] == 'gotowanie') $id = 4;
if($_GET['idp'] == 'gry') $id = 5;
if($_GET['idp'] == 'fantasyscifi') $id = 6;
if($_GET['idp'] == 'kontakt') $id = 7;
echo PokazPdostrone($id, $conn);
?>
</body>

<div id=zegarek></div>
<div id=data></div>
<footer>
<?php
$nr_indeksu = '164334';
$nrGrupy = '1';
$wersja = 'v1.6';
echo 'Autor: Jakub B. '.$nr_indeksu.' grupa '.$nrGrupy.' wersja '.$wersja.' <br/><br/>';
?>
</footer>
</html>