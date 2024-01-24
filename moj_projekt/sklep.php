<?php
// Include the necessary classes and the database connection from cfg.php
include('cfg.php');

class Cart
{
    private $con;
    private $products = array();

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->con = OpenCon();

        if ($this->con->connect_error) {
            die("Connection failed: " . $this->con->connect_error);
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        $this->products = &$_SESSION['cart'];
    }

    public function addProductToCart($productID)
    {
        // Fetch product data from the database based on the product ID
        $productData = $this->getProductDataFromDatabase($productID);

        if ($productData) {
            // Check if the product is already in the cart
            if (isset($_SESSION['cart'][$productID])) {
                // Increment the quantity if the product is already in the cart
                $_SESSION['cart'][$productID]['quantity'] += 1;
            } else {
                // Add the product to the cart with a default quantity of 1
                $_SESSION['cart'][$productID] = $productData;
                $_SESSION['cart'][$productID]['quantity'] = 1;
            }
        } else {
            echo "Product with ID {$productID} not found in the database.";
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    public function removeProductFromCart($productID)
    {
        // Check if the product is in the cart
        if (isset($_SESSION['cart'][$productID])) {
            // Decrease the quantity
            $_SESSION['cart'][$productID]['quantity']--;

            // Remove the product if the quantity becomes 0
            if ($_SESSION['cart'][$productID]['quantity'] <= 0) {
                unset($_SESSION['cart'][$productID]);
            }
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }


    public function showCart()
    {
        echo "<h3>Koszyk:</h3>";
        echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Opis</th>
                <th>Cena za sztuke</th>
                <th>Jakość</th>
                <th>Cena całkowita</th>
                <th>Akcja</th>
            </tr>";
        $totalSum = 0;
        foreach ($_SESSION['cart'] as $productID => $productData) {
            // Calculate total price (net price + VAT tax)
            $totalPricePerOne = $productData['net_price'] + $productData['vat'];
            $totalPrice = $totalPricePerOne * $productData['quantity'];
            $totalSum += $totalPrice;

            echo "<tr>
                <td>{$productID}</td>
                <td>{$productData['title']}</td>
                <td>{$productData['description']}</td>
                <td>{$totalPricePerOne}</td>
                <td>
                    <input type='number' name='quantity[{$productID}]' id='quantity_{$productID}' value='{$productData['quantity']}' min='1' onchange='updateQuantity({$productID})'>
                </td>
                <td>{$totalPrice}</td>
                <td>
                    <form method='post' action=''>
                        <input type='hidden' name='remove_from_cart' value='{$productID}'>
                        <input type='submit' value='Remove from Cart'>
                    </form>
                </td>
            </tr>";
        }

        echo "</table>";
        echo "<tr>
            <td colspan='5'><strong>Total Sum:</strong></td>
            <td>{$totalSum}</td>
            <td></td></tr>";

        // JavaScript to update quantity using AJAX
        echo "<script>
                function updateQuantity(productID) {
                    var newQuantity = document.getElementById('quantity_' + productID).value;
                    var formData = new FormData();
                    formData.append('update_quantity', '1');
                    formData.append('product_id', productID);
                    formData.append('new_quantity', newQuantity);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, true);
                    xhr.onload = function () {
                        // Reload the page after updating quantity
                        location.reload();
                    };
                    xhr.send(formData);
                }
            </script>";
    }

    private function getProductDataFromDatabase($productID)
    {
        $query = "SELECT * FROM products WHERE id = $productID";
        $result = $this->con->query($query);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }
}

class ProductManagement
{
    private $conn;

    public function __construct()
    {
        $this->conn = OpenCon();

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function showProducts()
    {
        $sql = "SELECT * FROM products";
        $result = $this->conn->query($sql);

        echo "<h3>Products:</h3>";
        echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Opis</th>
                <th>Data wygaśnięcia</th>
                <th>Nowa cena</th>
                <th>Podatek VAT</th>
                <th>Dostępna ilość</th>
                <th>Kategoria</th>
                <th>Wymiary</th>
                <th>Obrazek</th>
                <th>Dodaj do koszyka</th>
            </tr>";

        while ($row = $result->fetch_assoc()) {
            // Check if the product is available
            if ($row['availability_status'] == 1) {
                // Fetch category name based on category ID
                $categoryName = $this->getCategoryName($row['category_id']);

                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['expiration_date']}</td>
                    <td>{$row['net_price']}</td>
                    <td>{$row['vat']}</td>
                    <td>{$row['available_quantity']}</td>
                    <td>{$categoryName}</td>
                    <td>{$row['dimensions']}</td>
                    <td><img src='{$row['image_url']}' style='max-width: 100px; max-height: 100px;'></td>
                    <td><form method='post' action=''>
                        <input type='hidden' name='add_to_cart_id' value='{$row['id']}'>
                        <input type='submit' name='add_to_cart_button' value='Add to Cart'>
                    </form></td>
                </tr>";
            }
        }

        echo "</table>";
    }

    private function getCategoryName($categoryID)
    {
        // Fetch category name from the database based on category ID
        $query = "SELECT name FROM categories WHERE id = $categoryID";
        $result = $this->conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['name'];
        } else {
            return 'Unknown Category';
        }
    }

}

// Initialize ProductManagement and Cart
$productManagement = new ProductManagement();
$cart = new Cart();

if (isset($_POST['update_quantity'])) {
    $productID = $_POST['product_id'];
    $newQuantity = $_POST['new_quantity'];

    $_SESSION['cart'][$productID]['quantity'] = $newQuantity;

    exit();
}



// Check if a product is added to the cart
if (isset($_POST['add_to_cart_button'])) {
    $productID = $_POST['add_to_cart_id'];
    $cart->addProductToCart($productID);
}

if (isset($_POST['remove_from_cart'])) {
    $productID = $_POST['remove_from_cart'];
    $cart->removeProductFromCart($productID);
}

// Show the cart
$cart->showCart();

// Show products
$productManagement->showProducts();
?>

<head>
	<link rel="stylesheet" href="./css/admin2.css">
</head>

