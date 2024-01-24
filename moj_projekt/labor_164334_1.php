<?php
// zadanie 1
$nr_indeksu = '164344';
$nrGrupy = '1';
echo 'Łukasz ' . $nr_indeksu . ' grupa ' . $nrGrupy . ' <br /><br />';
// zadanie 2
echo 'Zadanie 2 <br />';
echo 'Zastosowanie metody include() <br />';
$color = '';
$fruit = '';
include 'vars.php';

echo $color . ' ' . $fruit . '<br />';
echo 'Zastosowanie metody require_once() <br />';

echo require_once "temp.php";
echo "<br />";
echo 'Zastosowanie warunków if, else, elsif, switch <br />';
$num1 = 5;
$num2 = 10;
echo 'if, elseif, else';
if ($num1 > $num2) {
    echo "a is bigger than b <br />";
} elseif ($num1 == $num2) {
    echo "a is equal to b <br />";
} else {
    echo "a is smaller than b <br />";
}
$i = 2;
echo 'switch<br />';
switch ($i) {
    case 0:
        echo "i equals 0<br />";
        break;
    case 1:
        echo "i equals 1<br />";
        break;
    case 2:
        echo "i equals 2<br />";
        break;
}
echo 'Zastosowanie pętli while(), for() <br />';
$i = 1;
echo 'while <br />';
while ($i <= 10) {
    echo $i++ . '<br />';
}
echo 'for <br />';
for ($i = 100; $i <= 150; $i++) {
    echo $i . "<br />";
}
echo 'Zastosowanie typów zmiennych $_GET, $_POST, $_SESSION <br />';
echo 'Zastosowanie $_GET <br />';
echo 'Hello ' . htmlspecialchars($_GET["name"]) . '!' . '<br />';
echo 'Zastosowanie $_SESSION <br />';
echo 'Setting up $_SESSION variuables <br />';

$_SESSION["favcolor"] = "green";
$_SESSION["favanimal"] = "cat";

echo "Favorite color is " . $_SESSION["favcolor"] . ". <br />";
echo "Favorite animal is " . $_SESSION["favanimal"] . ". <br />";


echo 'Zastosowanie $_POST <br />';
?>
<html>
<body>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    Name: <input type="text" name="name">
    <input type="submit">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    $name = $_POST['name'];
    if (empty($name)) {
        echo "Name is empty";
    } else {
        echo 'Hello ' . htmlspecialchars($_POST["name"]) . '!';
    }
}
?>

</body>
</html>