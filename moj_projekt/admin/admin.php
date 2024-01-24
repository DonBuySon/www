<?php
session_start();
ob_start();
include('../cfg.php');
global $login, $pass;

// -----------------------------------------
// Function: Get Refreshed URL
// -----------------------------------------
function getRefreshedUrl()
{
    $currentUrl = $_SERVER['REQUEST_URI'];
    $randomParam = time();
    return $currentUrl . '?refresh=' . $randomParam;
}

// -----------------------------------------
// Function: Display Login Form
// -----------------------------------------
function FormularzLogowania()
{
    $wynik = '
        <div class="logowanie">
         <h1 class="heading">Panel CMS:</h1>
          <div class="logowanie">
           <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
            <table class="logowanie">
             <tr><td class="log4_t">[email]</td><td><input type="text" name="login_mail" class="logowanie" /></td></td>
             <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" /></td></td>
             <tr><td>&nbsp;</td><td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td></tr>
            </table>
           </form>
          </div>
         </div>
    ';
    return $wynik;
}

// -----------------------------------------
// Function: Display Page for Editing
// -----------------------------------------
function PokazPodstrone($id)
{
    $id_clear = htmlspecialchars($id);
    $conn = OpenCon();

    if (isset($_POST['submit'])) {
        EdytujPodstrone($id_clear);
    }

    $query = "SELECT * FROM page_list WHERE id ='$id_clear' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_array($result)) {
        if ($_SESSION['zalogowany'] == true) {
            $decodedTitle = htmlspecialchars_decode($row['page_title']);
            $decodedContent = htmlspecialchars_decode($row['page_content']);

            echo '
                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                    <label for="title">Tytuł: </label>
                    <input type="text" name="title" value="' . htmlspecialchars($decodedTitle) . '" /><br />
                    
                    <label for="page_content">Zawartość strony:</label>
                    <textarea name="page_content">' . htmlspecialchars($decodedContent) . '</textarea><br />
                    
                    <label for="status">Aktywna:</label>
                    <input type="checkbox" name="status" ' . ($row['status'] == 1 ? 'checked' : '') . ' /><br />
                    
                    <input type="hidden" name="edit_button_id" value="' . $row['id'] . '">
                    <input type="submit" name="submit" value="Edytuj" />
                </form>
            ';
        }
    }

    CloseCon($conn);
}

// -----------------------------------------
// Function: Edit Page Content
// -----------------------------------------
function EdytujPodstrone($id)
{
    $id_clear = htmlspecialchars($id);
    $conn = OpenCon();

    if ($_SESSION['zalogowany'] == true && isset($_POST['submit'])) {
        $title_clear = htmlspecialchars($_POST['title']);
        $content_clear = $_POST['page_content'];

        $title = mysqli_real_escape_string($conn, $title_clear);
        $content = mysqli_real_escape_string($conn, $content_clear);
        $status = isset($_POST['status']) ? 1 : 0;

        $query = "UPDATE page_list SET page_title='$title', page_content='$content', status=$status WHERE id=$id_clear";
        mysqli_query($conn, $query);
        echo 'Page updated successfully.<br /><br />';
    }
    CloseCon($conn);
}

// -----------------------------------------
// Function: Display List of Pages
// -----------------------------------------
function ListaPodstron()
{
    $conn = OpenCon();
    $query = "SELECT * FROM page_list ORDER BY id ASC LIMIT 100";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_array($result)) {
        echo 'id: ' . $row['id'] . ' nazwa strony: ' . $row['page_title'] . '
             <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                 <input type="hidden" name="edit_button_id" value="' . $row['id'] . '">
                 <button type="submit" name="edit_button">Edytuj</button>
                 <input type="hidden" name="usun_button_id" value="' . $row['id'] . '">
                 <button type="submit" name="usun_button' . $row['id'] . '">Usun</button>
             </form>
             <br />';

        if (isset($_POST['usun_button' . $row['id']])) {
            UsunPodstrone($row['id']);
            header('Location: ' . getRefreshedUrl());
            exit();
        }
    }

    CloseCon($conn);
}

// -----------------------------------------
// Function: Delete Page
// -----------------------------------------
function UsunPodstrone($id)
{
    if ($_SESSION['zalogowany'] == true && isset($_POST['usun_button' . $id])) {
        $id_clear = htmlspecialchars($id);
        $conn = OpenCon();

        $query = "DELETE FROM page_list WHERE id=$id_clear LIMIT 1";
        mysqli_query($conn, $query);

        header('Location: ' . getRefreshedUrl());
        exit();
    }
}

// -----------------------------------------
// Function: Display Form for Adding Page
// -----------------------------------------
function DodajPodstroneForm()
{
    echo '
        <form method="post" action="">
            <label for="new_title">Nazwa nowej strony: </label>
            <input type="text" name="new_title" /><br />
            
            <label for="new_page_content">Zawartość nowej strony:</label>
            <textarea name="new_page_content"></textarea><br />
            
            <label for="new_status">Aktywna:</label>
            <input type="checkbox" name="new_status" /><br />
            
            <input type="submit" name="submit_new_page" value="Dodaj Nową Podstronę" />
        </form>
    ';
}

// -----------------------------------------
// Function: Add New Page
// -----------------------------------------
function DodajNowaPodstrone()
{
    $conn = OpenCon();

    if ($_SESSION['zalogowany'] == true && isset($_POST['submit_new_page'])) {
        $title_clear = htmlspecialchars($_POST['new_title']);
        $content_clear = $_POST['new_page_content'];

        $title = mysqli_real_escape_string($conn, $title_clear);
        $content = mysqli_real_escape_string($conn, $content_clear);
        $status = isset($_POST['new_status']) ? 1 : 0;

        $query = "INSERT INTO page_list (page_title, page_content, status) VALUES ('$title', '$content', $status)";
        mysqli_query($conn, $query);

        header('Location: ' . getRefreshedUrl());
        exit();
    }

    CloseCon($conn);
}

if (!isset($_SESSION['zalogowany'])) {
    $_SESSION['zalogowany'] = false;
}

if ($_SESSION['zalogowany'] !== true) {
    if (isset($_POST['login_mail'])) {
        if ($_POST['login_mail'] == $login && $_POST['login_pass'] == $pass) {
            $_SESSION['zalogowany'] = true;
            echo 'Logowanie powiodło się . <br /><br />';
            ListaPodstron();
            DodajPodstroneForm();
        } else {
            echo 'Błąd logowania. Spróbuj ponownie. <br/>';
            echo FormularzLogowania();
        }
    } else {
        echo FormularzLogowania();
    }
} else {
    ListaPodstron();
    DodajPodstroneForm();
}

if (isset($_POST['edit_button_id'])) {
    $buttonId = $_POST['edit_button_id'];
    PokazPodstrone($buttonId);
}

if (isset($_POST['submit_new_page'])) {
    DodajNowaPodstrone();
}

class CategoryManagement
{
    private $conn;

    public function __construct()
    {
        $this->conn = OpenCon();

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function addCategory($parent_id, $name)
    {
        $parent_id = $this->conn->real_escape_string($parent_id);
        $name = $this->conn->real_escape_string($name);

        $sql = "INSERT INTO categories (parent_id, name) VALUES ('$parent_id', '$name')";
		
        if ($this->conn->query($sql) !== TRUE) {
            echo "Error adding categories: " . $this->conn->error;
        }
    }

    public function deleteCategory($id)
    {
        $id = $this->conn->real_escape_string($id);

        $sql = "DELETE FROM categories WHERE id = '$id'";


        if ($this->conn->query($sql) !== TRUE) {
            echo "Error deleting category: " . $this->conn->error;
        }
    }

    public function editCategory($id, $name)
    {
        $id = $this->conn->real_escape_string($id);
        $name = $this->conn->real_escape_string($name);

        $sql = "UPDATE categories SET name = '$name' WHERE id = '$id'";

        if ($this->conn->query($sql) !== TRUE) {
            echo "Error editing category: " . $this->conn->error;
        }
    }

    public function showCategories()
    {
        $sql = "SELECT * FROM categories ORDER BY parent_id, id";
        $result = $this->conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo "{$row['id']}. {$row['name']} (Mother: {$row['parent_id']})<br>";
        }
    }

    public function generateCategoryTree()
    {
        $sql = "SELECT * FROM categories ORDER BY parent_id, id";
        $result = $this->conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            if ($row['parent_id'] == 0) {
                echo "{$row['id']}. {$row['name']} (Mother: {$row['parent_id']})<br>";
                $this->displayChildCategories($row['id'], 1);
            }
        }
    }

    private function displayChildCategories($parent_id, $depth)
    {
        $sql = "SELECT * FROM categories WHERE parent_id = '$parent_id' ORDER BY id";
        $result = $this->conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $indentation = str_repeat("&nbsp;", $depth * 2);
            echo "{$indentation}{$row['id']}. {$row['name']} (Mother: {$row['parent_id']})<br>";
            $this->displayChildCategories($row['id'], $depth + 1);
        }
    }
}

$categoryManagement = new CategoryManagement();
if ($_SESSION['zalogowany'] == true) {
    if (isset($_POST['submit_new_category'])) {
        $parent_id = isset($_POST['new_category_mother']) ? $_POST['new_category_mother'] : 0;
        $name = $_POST['new_category_name'];
    
        $categoryManagement->addCategory($parent_id, $name);
    
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    }
    
    // Edit category
    if (isset($_POST['edit_category_id'])) {
        $category_id = $_POST['edit_category_id'];
        echo "<h3>Edytuj kategorie:</h3>";
        echo "<form method='post' action=''>";
        echo "<label for='edit_category_id'>ID kategorii: </label>";
        echo "<input type='text' name='edit_category_id' value='{$category_id}' readonly /><br />";
        echo "<label for='new_category_name'>New Category Name: </label>";
        echo "<input type='text' name='new_category_name' /><br />";
        echo "<input type='submit' name='submit_edit_category' value='Edit Category' />";
        echo "</form>";
    }
    
    // Edit category submission
    if (isset($_POST['submit_edit_category'])) {
        $category_id = $_POST['edit_category_id'];
        $new_name = $_POST['new_category_name'];
        $categoryManagement->editCategory($category_id, $new_name);
    }
    
    // Delete category
    if (isset($_POST['delete_category_id'])) {
        $category_id = $_POST['delete_category_id'];
        $categoryManagement->deleteCategory($category_id);
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    }
    
    // Display category tree
    echo "<h3>Drzewo kategori:</h3>";
    $categoryManagement->generateCategoryTree();
    
    // Display form for adding a new category
    echo "<h3>Dodaj nową kategorie:</h3>";
    echo "<form method='post' action=''>";
    echo "<label for='new_category_name'>Nazwa kategori: </label>";
    echo "<input type='text' name='new_category_name' /><br />";
    echo "<label for='new_category_mother'>kategoria Matki (0 dla głównej kategori):</label>";
    echo "<input type='text' name='new_category_mother' /><br />";
    echo "<input type='submit' name='submit_new_category' value='Dodaj nową kategorie' />";
    echo "</form>";
    
    // Display form for deleting a category
    echo "<h3>Usuń kategorie:</h3>";
    echo "<form method='post' action=''>";
    echo "<label for='delete_category_id'>ID kategori: </label>";
    echo "<input type='text' name='delete_category_id' /><br />";
    echo "<input type='submit' name='submit_delete_category' value='Usuń kategorie' />";
    echo "</form>";
    
    // Display form for editing a category
    echo "<h3>Edycja kategori:</h3>";
    echo "<form method='post' action=''>";
    echo "<label for='edit_category_id'>ID kategori: </label>";
    echo "<input type='text' name='edit_category_id' /><br />";
    echo "<label for='new_category_name'>Nowa nazwa kategori: </label>";
    echo "<input type='text' name='new_category_name' /><br />";
    echo "<input type='submit' name='submit_edit_category' value='Edycja kateogri' />";
    echo "</form>";
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

    public function getProductById($id)
    {
        $id = $this->conn->real_escape_string($id);

        $sql = "SELECT * FROM products WHERE id = '$id'";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function addProduct($title, $description, $expiration_date, $net_price, $vat_tax, $available_quantity, $availability_status, $category, $dimensions, $image)
    {
        $title = $this->conn->real_escape_string($title);
        $description = $this->conn->real_escape_string($description);
        $expiration_date = $this->conn->real_escape_string($expiration_date);
        $net_price = $this->conn->real_escape_string($net_price);
        $vat_tax = $this->conn->real_escape_string($vat_tax);
        $available_quantity = $this->conn->real_escape_string($available_quantity);
        $availability_status = $this->conn->real_escape_string($availability_status);
        $category = $this->conn->real_escape_string($category);
        $dimensions = $this->conn->real_escape_string($dimensions);
        $image = $this->conn->real_escape_string($image);

        $sql = "INSERT INTO products (title, description, expiration_date, net_price, vat, available_quantity, availability_status, category_id, dimensions, image_url) 
                VALUES ('$title', '$description', '$expiration_date', '$net_price', '$vat_tax', '$available_quantity', '$availability_status', '$category', '$dimensions', '$image')";

        if ($this->conn->query($sql) !== TRUE) {
            echo "Error adding product: " . $this->conn->error;
        }
    }

    public function deleteProduct($id)
    {
        $id = $this->conn->real_escape_string($id);

        $sql = "DELETE FROM products WHERE id = '$id'";

        if ($this->conn->query($sql) !== TRUE) {
            echo "Error deleting product: " . $this->conn->error;
        }
    }



    public function editProduct($id, $title, $description, $expiration_date, $net_price, $vat_tax, $available_quantity, $availability_status, $category, $dimensions, $image)
    {
        $id = $this->conn->real_escape_string($id);
        $title = $this->conn->real_escape_string($title);
        $description = $this->conn->real_escape_string($description);
        $expiration_date = $this->conn->real_escape_string($expiration_date);
        $net_price = $this->conn->real_escape_string($net_price);
        $vat_tax = $this->conn->real_escape_string($vat_tax);
        $available_quantity = $this->conn->real_escape_string($available_quantity);
        $availability_status = $this->conn->real_escape_string($availability_status);
        $category = $this->conn->real_escape_string($category);
        $dimensions = $this->conn->real_escape_string($dimensions);
        $image = $this->conn->real_escape_string($image);

        $sql = "UPDATE products SET 
            title = '$title', 
            description = '$description', 
            expiration_date = '$expiration_date', 
            net_price = '$net_price', 
            vat = '$vat_tax', 
            available_quantity = '$available_quantity', 
            availability_status = '$availability_status', 
            category_id = '$category', 
            dimensions = '$dimensions', 
            image_url = '$image' 
            WHERE id = '$id'";

        if ($this->conn->query($sql) !== TRUE) {
            echo "Error editing product: " . $this->conn->error;
        }
    }

    public function showProducts()
    {
        $sql = "SELECT * FROM products";
        $result = $this->conn->query($sql);

        echo "<h3>Produkty:</h3>";
        echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Opis</th>
                <th>Data wygaśnięcia</th>
                <th>Cenna netto</th>
                <th>Podatek VAT</th>
                <th>Dostępna ilość</th>
                <th>Status dostępności</th>
                <th>Kategorie</th>
                <th>Wymiary</th>
                <th>Obraz</th>
                <th>Edytuj</th>
                <th>Usuń</th>
            </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['description']}</td>
                <td>{$row['expiration_date']}</td>
                <td>{$row['net_price']}</td>
                <td>{$row['vat']}</td>
                <td>{$row['available_quantity']}</td>
                <td>{$row['availability_status']}</td>
                <td>{$row['category_id']}</td>
                <td>{$row['dimensions']}</td>
                <td><img src='{$row['image_url']}' style='max-width: 100px; max-height: 100px;'></td>
                <td><form method='post' action=''>
                    <input type='hidden' name='edit_product_id' value='{$row['id']}'>
                    <input type='submit' name='edit_product_button' value='Edit'>
                </form></td>
                <td><form method='post' action=''>
                    <input type='hidden' name='delete_product_id' value='{$row['id']}'>
                    <input type='submit' name='delete_product_button' value='Delete'>
                </form></td>
            </tr>";
        }

        echo "</table>";

    }
}

$productManagement = new ProductManagement();
if ($_SESSION['zalogowany'] == true) {
    if (isset($_POST['submit_new_product'])) {
        $title = $_POST['new_product_title'];
        $description = $_POST['new_product_description'];
        $expiration_date = $_POST['new_product_expiration_date'];
        $net_price = $_POST['new_product_net_price'];
        $vat_tax = $_POST['new_product_vat_tax'];
        $available_quantity = $_POST['new_product_available_quantity'];
        $availability_status = $_POST['new_product_availability_status'];
        $category = $_POST['new_product_category'];
        $dimensions = $_POST['new_product_dimensions'];
        $image = $_POST['new_product_image'];
    
        $productManagement->addProduct($title, $description, $expiration_date, $net_price, $vat_tax, $available_quantity, $availability_status, $category, $dimensions, $image);
    
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    }
    
    
    if (isset($_POST['submit_edit_product'])) {
        $product_id = $_POST['edit_product_id'];
        $title = $_POST['edit_product_title'];
        $description = $_POST['edit_product_description'];
        $expiration_date = $_POST['edit_product_expiration_date'];
        $net_price = $_POST['edit_product_net_price'];
        $vat_tax = $_POST['edit_product_vat_tax'];
        $available_quantity = $_POST['edit_product_available_quantity'];
        $availability_status = $_POST['edit_product_availability_status'];
        $category = $_POST['edit_product_category'];
        $dimensions = $_POST['edit_product_dimensions'];
        $image = $_POST['edit_product_image'];
    
        $productManagement->editProduct($product_id, $title, $description, $expiration_date, $net_price, $vat_tax, $available_quantity, $availability_status, $category, $dimensions, $image);
    
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    }
    
    // Delete product
    if (isset($_POST['delete_product_id'])) {
        $product_id = $_POST['delete_product_id'];
        $productManagement->deleteProduct($product_id);
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    }
    
    // Display products
    $productManagement->showProducts();
    
    // Display form for adding a new product
    echo "<h3>Dodaj nowy produkt:</h3>";
    echo "<form method='post' action=''>";
    echo "<table>";
    echo "<tr><td><label for='new_product_title'>Nazwa produktu: </label></td><td><input type='text' name='new_product_title' /></td></tr>";
    echo "<tr><td><label for='new_product_description'>Opis produktu:</label></td><td><textarea name='new_product_description'></textarea></td></tr>";
    echo "<td><label for='new_product_expiration_date'>Data wygaśnięcia:</label></td><td><input type='date' name='new_product_expiration_date' /></td>";
    echo "<tr><td><label for='new_product_net_price'>Cena netto:</label></td><td><input type='text' name='new_product_net_price' /></td></tr>";
    echo "<tr><td><label for='new_product_vat_tax'>Podatek VAT:</label></td><td><input type='text' name='new_product_vat_tax' /></td></tr>";
    echo "<tr><td><label for='new_product_available_quantity'>Dostępna ilość:</label></td><td><input type='text' name='new_product_available_quantity' /></td></tr>";
    echo "<tr><td><label for='new_product_availability_status'>Status dostępności:</label></td><td><input type='text' name='new_product_availability_status' /></td></tr>";
    echo "<tr><td><label for='new_product_category'>Kategorie:</label></td><td><input type='text' name='new_product_category' /></td></tr>";
    echo "<tr><td><label for='new_product_dimensions'>Wymiary:</label></td><td><input type='text' name='new_product_dimensions' /></td></tr>";
    echo "<tr><td><label for='new_product_image'>Obrazek:</label></td><td><input type='text' name='new_product_image' /></td></tr>";
    echo "</table>";
    echo "<input type='submit' name='submit_new_product' value='Dodaj produkt' />";
    echo "</form>";
        
    
    if (isset($_POST['edit_product_id'])) {
        $product_id = $_POST['edit_product_id'];
        $product = $productManagement->getProductById($product_id);
    
        if ($product !== null) {
            echo "<h3>Edit Product:</h3>";
            echo "<form method='post' action=''>";
            echo "<label for='edit_product_id'>Product ID: </label>";
            echo "<input type='text' name='edit_product_id' value='{$product_id}' readonly /><br />";
            
            echo "<label for='edit_product_title'>Product Title: </label>";
            echo "<input type='text' name='edit_product_title' value='{$product['title']}' /><br />";
            
            echo "<label for='edit_product_description'>Product Description:</label>";
            echo "<textarea name='edit_product_description'>{$product['description']}</textarea><br />";
            
            echo "<label for='edit_product_expiration_date'>Expiration Date:</label>";
            echo "<input type='date' name='edit_product_expiration_date' value='{$product['expiration_date']}' /><br />";
            
            echo "<label for='edit_product_net_price'>Net Price:</label>";
            echo "<input type='text' name='edit_product_net_price' value='{$product['net_price']}' /><br />";
            
            echo "<label for='edit_product_vat_tax'>VAT Tax:</label>";
            echo "<input type='text' name='edit_product_vat_tax' value='{$product['vat']}' /><br />";
            
            echo "<label for='edit_product_available_quantity'>Available Quantity:</label>";
            echo "<input type='text' name='edit_product_available_quantity' value='{$product['available_quantity']}' /><br />";
            
            echo "<label for='edit_product_availability_status'>Availability Status:</label>";
            echo "<input type='text' name='edit_product_availability_status' value='{$product['availability_status']}' /><br />";
            
            echo "<label for='edit_product_category'>Category:</label>";
            echo "<input type='text' name='edit_product_category' value='{$product['category_id']}' /><br />";
            
            echo "<label for='edit_product_dimensions'>Dimensions:</label>";
            echo "<input type='text' name='edit_product_dimensions' value='{$product['dimensions']}' /><br />";
            
            echo "<label for='edit_product_image'>Image:</label>";
            echo "<input type='text' name='edit_product_image' value='{$product['image_url']}' /><br />";
            
            echo "<input type='submit' name='submit_edit_product_changes' value='Submit Changes' />";
            echo "<input type='submit' name='cancel_edit_product' value='Cancel' />";
            echo "</form>";
        } else {
            echo "Product not found.";
        }
    }
    
    
    if (isset($_POST['submit_edit_product_changes'])) {
        $product_id = $_POST['edit_product_id'];
        $title = $_POST['edit_product_title'];
        $description = $_POST['edit_product_description'];
        $expiration_date = $_POST['edit_product_expiration_date'];
        $net_price = $_POST['edit_product_net_price'];
        $vat_tax = $_POST['edit_product_vat_tax'];
        $available_quantity = $_POST['edit_product_available_quantity'];
        $availability_status = $_POST['edit_product_availability_status'];
        $category = $_POST['edit_product_category'];
        $dimensions = $_POST['edit_product_dimensions'];
        $image = $_POST['edit_product_image'];
    
        $productManagement->editProduct($product_id, $title, $description, $expiration_date, $net_price, $vat_tax, $available_quantity, $availability_status, $category, $dimensions, $image);
    
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    } elseif (isset($_POST['cancel_edit_product'])) {
        header('Location: ' . $_SERVER["REQUEST_URI"]);
        exit();
    }
}



ob_end_flush();
?>


<head>
	<link rel="stylesheet" href="../css/admin2.css">
</head>
