<?php
session_start();

if ($_SESSION['Role'] !== 'Super Admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['customer_number'])) {
    $_SESSION['customer_number'] = 'CTMR-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

include("php/config.php");

// Add to cart
if (isset($_POST['add_to_cart'])) {

    $name      = $_POST['product_name'];
    $price     = floatval($_POST['product_price']);
    $discount  = floatval($_POST['product_Discount']);
    $qty       = intval($_POST['product_quantity']);
    $size      = $_POST['product_size'];
    $sugar     = $_POST['product_sugar'];
    $category  = $_POST['product_category'];
    $ordertype  = $_POST['order_type'];

    // Save nickname
    if (!empty($_POST['customer_nickname'])) {
        $_SESSION['customer_nickname'] = $_POST['customer_nickname'];
    }

    // Save order type
    if (!empty($_POST['order_type'])) {
        $_SESSION['order_type'] = $_POST['order_type'];
    }

    $nickname       = $_SESSION['customer_nickname'] ?? "Guest";
    $customerNumber = $_SESSION['customer_number'];

    // Calculate discounted unit price
    $discountAmount = ($discount / 100) * $price;
    $finalPricePerItem = max($price - $discountAmount, 0); // prevent negative

    // Calculate total price for quantity (discount applied)
    $finalTotal = $finalPricePerItem * $qty;

    $stmt = $conn->prepare("
        INSERT INTO tbl_cart
        (CustomerNumber, ProductName, ProductCategory, UnitPrice, ProductPrice, Discount, ProductQuantity, ProductSize, ProductSugarLevel, CustomerNickname,OrderType, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "sssddisssss",
        $customerNumber,
        $name,
        $category,
        $price,              // UnitPrice (original price)
        $finalTotal,         // ProductPrice (discounted total price)
        $discount,
        $qty,
        $size,
        $sugar,
        $nickname,
        $ordertype
    );

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error adding to cart: " . $conn->error;
    }
}

// Delete item
if (isset($_POST['delete'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM tbl_cart WHERE ProductNumber = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting item: " . $conn->error;
    }
}

// Proceed order (with AJAX JSON response)
if (isset($_POST['proceed_order'])) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['customer_number'])) {
        echo json_encode(['status' => 'error', 'message' => 'No customer session found']);
        exit();
    }

    $nickname       = $_SESSION['customer_nickname'] ?? "Guest";
    $customerNumber = $_SESSION['customer_number'];

    $sql = "
        INSERT INTO tbl_pendingorders
        (CustomerNumber, ProductName, ProductCategory,UnitPrice, ProductPrice, Discount,
         ProductQuantity, ProductSize, ProductSugarLevel, CustomerNickname,OrderType, created_at)
        SELECT
            CustomerNumber,
            ProductName,
            ProductCategory,
            UnitPrice,
            ProductPrice,
            Discount,
            ProductQuantity,
            ProductSize,
            ProductSugarLevel,
            CustomerNickname,
            OrderType,
            NOW()
        FROM tbl_cart
        WHERE CustomerNumber = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database prepare failed']);
        exit();
    }

    $stmt->bind_param("s", $customerNumber);

    if ($stmt->execute()) {
        $clear = $conn->prepare("DELETE FROM tbl_cart WHERE CustomerNumber = ?");
        if ($clear) {
            $clear->bind_param("s", $customerNumber);
            $clear->execute();
            $clear->close();
        }

        unset($_SESSION['customer_nickname']);
        unset($_SESSION['customer_number']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Order processed successfully',
            'customer_number' => $customerNumber,
            'nickname' => $nickname,
            'session_reset' => true
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }

    if ($stmt) {
        $stmt->close();
    }
    exit();
}

// Clear cart + reset nickname + reset customer number
if (isset($_POST['clear_cart'])) {
    $customerNumber = $_SESSION['customer_number'];

    $clear = $conn->prepare("DELETE FROM tbl_cart WHERE CustomerNumber = ?");
    $clear->bind_param("s", $customerNumber);
    $clear->execute();

    unset($_SESSION['customer_nickname']);
    unset($_SESSION['customer_number']);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Subtotal (discount included)
$subtotal = 0;
$customerNumber = $_SESSION['customer_number'] ?? null;

if ($customerNumber) {
    $stmt = $conn->prepare("SELECT SUM(ProductPrice) AS subtotal FROM tbl_cart WHERE CustomerNumber = ?");
    $stmt->bind_param("s", $customerNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $subtotal = $row['subtotal'] ?? 0;
    }
    $stmt->close();
}
?>









<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="admin.css">
  <link rel="shortcut icon" href="favcon/favcon.ico.png" type="image/x-icon">
  <title>Admin Dashboard</title>
</head>

  <style>
/* Product cards */
.cards {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    cursor: pointer;
    height: 100%;
}

.cards:hover {
    transform: translateY(-3px);
}

.product-card img {
    height: 250px;       /* adjust height as needed */
    object-fit: contain;  /* maintain proportions */
    border-radius: 10px;
}

.cards-body {
    padding: 10px;
}

.cards-title {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 1px;
    color: #333;
    height: 36px;
    overflow: hidden;
}

.price {
    color: #bd371c;
    font-size: 16px;
    font-weight: bold;
}

.rating {
    font-size: 12px;
    color: #999;
}

.product-card {
    margin-top: 30px;
    margin-bottom: 30px;
}

/* Bottom-sheet modal */
.modal.bottom-sheet .modal-dialog {
    position: fixed;
    bottom: 0;
    margin: 0;
    width: 100%;
    max-width: 100%;
    transition: all 0.3s ease-in-out;
}

.modal.bottom-sheet .modal-content {
    border-radius: 15px 15px 0 0;
    padding: 15px;
    min-height: 80vh;
}

.modal-handle {
    width: 50px;
    height: 5px;
    background: #ccc;
    border-radius: 5px;
    margin: 0 auto 10px;
}
.container-cart {
    overflow: auto;  
    scrollbar-width: 1px;       
    -ms-overflow-style: none;    
}
/* Cart accordion items */
.accordion-item {
    margin-bottom: 15px;
    margin-top: 15px;
    border: none;
    border-radius: 0 !important; /* flat style */
    overflow: hidden;
    background: #f8f9fa;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}


/* Accordion header style */
.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #fff;
}

/* Item title */
.cart-title {
    font-size: 16px;
    font-weight: 600;
}

/* Sub label */
.cart-sub {
    font-size: 13px;
    color: #6c757d;
    margin-top: -4px;
}

/* Price */
.cart-price {
    font-size: 16px;
    font-weight: 600;
}

/* Old price */
.cart-old-price {
    font-size: 13px;
    text-decoration: line-through;
    color: #999;
    text-align: right;
}

/* Remove button (gray circle) */
.remove-circle {
    border: none;
    background: #e9ecef;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 18px;
    line-height: 26px;
    text-align: center;
    cursor: pointer;
    color: #6c757d;
}

.remove-circle:hover {
    background: #d6d6d6;
}
  :root {
  --primary-color: #6366f1;
  --secondary-color: #8b5cf6;
  --dark-color: #1f2937;
  --light-color: #f9fafb;
  --sidebar-width: 250px;
}

* {
  font-family: 'Poppins', sans-serif;
}

body {
  background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
  min-height: 100vh;
}

.content {
  margin-left: var(--sidebar-width);
  transition: all 0.3s ease;
  background: transparent;
}

.content.collapsed {
  margin-left: 70px;
}

/* Header Styling */
.navbar {
  background: white !important;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem 0;
}


</style>
<body>
<?php include("superadminsidebar.php"); ?>

  <div class="content" id="content">
   <?php include("superadminnavbar.php") ?>


    <div class="container-fluid mt-4">
  <div class="row align-items-center">
    <div class="col-md-6 ">
      <h1 class="fw-bold text-dark mb-1">Cashier</h2>
    </div>
    
    <div class="col-md-6 d-flex justify-content-lg-center mt-1 mt-lg-0">
      <div id="currentDateUTC" class="form-control w-auto rounded-pill shadow-sm text-center" style="padding: 0.575rem 1rem;"></div>
      <script src="sidebar-and-date-utc.js"></script>

    </div>

    <div class="container my-2">
  <div class="row">
    
    <div class="col-md-9" >
  <div class="position-relative">

    <button id="scrollLeftBtn" class="arrow-btn position-absolute top-50 start-0 translate-middle-y">
      <i class="bi bi-chevron-left"></i>
    </button>

    <div id="scrollContainer" class="category-scroll d-flex py-3 px-2">
<?php
$catResult = $conn->query("
    SELECT CategoryName 
    FROM tbl_categories 
    ORDER BY CategoryName ASC
");

?>
  <!-- All -->
  <div class="category-item active">All</div>

  <!-- Categories from DB -->
  <?php while ($cat = $catResult->fetch_assoc()): ?>
    <div class="category-item">
      <?= htmlspecialchars($cat['CategoryName']) ?>
    </div>
  <?php endwhile; ?>

</div>


    <button id="scrollRightBtn" class="arrow-btn position-absolute top-50 end-0 translate-middle-y">
      <i class="bi bi-chevron-right"></i>
    </button>
  </div>
  </div>
<script src="category-scroll.js"></script>


<div class="col-md-3 d-flex align-items-center ">
  <form class="flex-shrink-0 me-3" id="searchForm">
     <div class="input-group flex-nowrap">
  <span class="input-group-text" id="addon-wrapping">  <i class="fa-solid fa-magnifying-glass"></i></span>
     <input type="search" id="categorySearch" class="form-control form-control-md" aria-describedby="addon-wrapping" placeholder="Search Menu">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="search.js"></script>
    </div>
     </form>
    </div>
  </div>
</div>
</div>

<div class="container-fluid">
  <div class="row">
   
    <div class="col-md-8 product-container" style="height: 520px;overflow: auto;background-color: rgb(231, 231, 229);">
     <div class="row g-3" id="productContainer"> 
<?php

$res = mysqli_query($conn, "SELECT * FROM tbl_products where availability = 'Available' ");
while($row = mysqli_fetch_assoc($res)){
  $ProductName   = $row['ProductName'];
  $Discount   = $row['Discount'];
 $ProductPriceSmall  = $row['ProductPriceSmall'];
$ProductPriceMedium = $row['ProductPriceMedium'];
$ProductPriceLarge  = $row['ProductPriceLarge'];
  $ProductImage = $row['ProductImage'];
  $Category = $row['ProductCategory'];
?>
  <div class="col-6 col-md-3 product-card" data-category="<?php echo htmlspecialchars($Category); ?>">
    <div class="cards h-100">
      <button class="btn p-0 w-100 text-start border-0 bg-white"
        data-bs-toggle="modal" 
        data-bs-target="#cartModal"
        data-name="<?php echo $ProductName; ?>" 
        data-category="<?php echo $Category; ?>"
        data-small="₱<?php echo $ProductPriceSmall; ?>"
        data-medium="₱<?php echo $ProductPriceMedium; ?>"
        data-large="₱<?php echo $ProductPriceLarge; ?>"
        data-image="images/<?php echo htmlspecialchars($ProductImage); ?>"
         data-discount="<?php echo $Discount; ?>">
        <div class="imgcenter" style="justify-content: center;align-items: center;display: flex;">
        <img src="images/<?php echo htmlspecialchars($ProductImage); ?>" width="70%" height="" alt="Product"></div>
        <div class="cards-body">
          <div class="cards-title"><?php echo $ProductName; ?></div>
          <div class="cards-title"><h6><b><?php echo $Category; ?></b></h6></div>
          <div class="price">₱<?php echo $ProductPriceSmall; ?> ~ ₱<?php echo $ProductPriceLarge; ?> <br> (Small - Large)</div>
        </div>
      </button>
    </div>
  </div>
<?php } ?>

</div>

    </div>
<div class=" col-md-4 container">
      <div class="mb-3">
  <label for="customerNickname" class="fw-bold d-block mb-2"></label>
  <input type="text" id="customerNickname" name="customer_nickname" class="form-control" placeholder="Enter preferred name or nickname"
         value="<?php echo htmlspecialchars($_SESSION['customer_nickname'] ?? ''); ?>">
</div>

<div class="mb-3">
  <label class="fw-bold d-block mb-2">Order Type</label>
  <div class="btn-group w-100" role="group">
    <input type="radio" class="btn-check" name="order_type" id="dineIn" value="Dine In"
      <?php echo (($_SESSION['order_type'] ?? '') == 'Dine In') ? 'checked' : ''; ?>>
    <label class="btn btn-outline-success" for="dineIn">Dine In</label>

    <input type="radio" class="btn-check" name="order_type" id="takeOut" value="Take Out"
      <?php echo (($_SESSION['order_type'] ?? '') == 'Take Out') ? 'checked' : ''; ?>>
    <label class="btn btn-outline-primary" for="takeOut">Take Out</label>
  </div>
</div>

 <div class="container-cart mt-1">
  <div class="accordion" id="cartAccordion">
    <?php include("cart_items.php"); ?>
  </div>
</div>
 

     <div class="container" style="margin-top: 10px; background-color: #ebebeb; border-radius: 5px; height: 150px; display: flex; flex-direction: column; justify-content: space-between; padding: 10px;">

  <div class="row">
    <div class="totalprice col-6" style="text-align: left;">
      <h6><b>Subtotal</b></h6>
    </div>
   <div class="totalprice col-6" style="text-align: right;">
  <h6><b>₱<?php echo number_format($subtotal, 2); ?></b></h6>
</div>

  </div>

  <div class="d-flex">
     <form class="" method="POST" onsubmit="return confirm('Are you sure you want to clear the items?');">
     <button type="submit" name="clear_cart" class="btn btn flex-fill btn-lg"
        style="width: 110px;font-size: small;background-color: rgb(57, 77, 18);color: white;">
        Clear
    </button>
</form>
   <form class="ms-auto" method="POST" id="proceedOrderForm">
  <button type="submit" name="proceed_order" class="btn btn flex-fill btn-lg" 
          style="width: 110px;font-size: small;background-color: rgb(104, 148, 16);color: white;">
          Proceed
  </button>
</form>

  </div>

</div>

 
    </div>
  </div>
</div>





</div>
    </div>
<div class="modal fade bottom-sheet" id="cartModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content shadow-lg">
      <div class="modal-handle"></div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>

      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <img id="modalImage" src="" class="rounded me-3" alt="Product" width="90">
          <div><h5 id="modalName" class="mb-1"></h5>
            <p id="modalPrice" class="text-success mb-0"></p>
            
   
          </div>
         
        </div>



    <div class="d-flex justify-content-left align-items-center gap-3">
    <span>Quantity</span>
    <div class="input-group" style="width: 120px;">
      <button class="btn btn-outline-secondary btn-sm" id="decrease">-</button>
      <input type="text" class="form-control text-center" id="quantity" value="1">
      <button class="btn btn-outline-secondary btn-sm" id="increase">+</button>
    </div>
  </div>
   <div class="mb-3 col-md-6">
    <label class="fw-bold d-block mb-2">Size</label>
    <div class="btn-group w-100" role="group" aria-label="Size selector">
      <input type="radio" class="btn-check" name="size" id="sizeSmall" autocomplete="off" checked>
      <label class="btn btn-outline-dark" for="sizeSmall">Small</label>

     <!-- <input type="radio" class="btn-check" name="size" id="sizeMedium" autocomplete="off">
      <label class="btn btn-outline-dark" for="sizeMedium">Medium</label> 
      -->


      <input type="radio" class="btn-check" name="size" id="sizeLarge" autocomplete="off">
      <label class="btn btn-outline-dark" for="sizeLarge">Large</label>
    </div>
  </div>

  <div class="mb-3 col-md-6">
    <label class="fw-bold d-block mb-2">Sugar Level</label>
    <div class="btn-group w-100" role="group" aria-label="Sugar selector">
      <input type="radio" class="btn-check" name="sugar" id="sugarNone" autocomplete="off">
      <label class="btn btn-outline-secondary" for="sugarNone">None</label>

      <input type="radio" class="btn-check" name="sugar" id="sugar25" autocomplete="off">
      <label class="btn btn-outline-secondary" for="sugar25">25%</label>

      <input type="radio" class="btn-check" name="sugar" id="sugar50" autocomplete="off" checked>
      <label class="btn btn-outline-secondary" for="sugar50">50%</label>

      <input type="radio" class="btn-check" name="sugar" id="sugar75" autocomplete="off">
      <label class="btn btn-outline-secondary" for="sugar75">75%</label>

      <input type="radio" class="btn-check" name="sugar" id="sugar100" autocomplete="off">
      <label class="btn btn-outline-secondary" for="sugar100">100%</label>
    </div>
  </div>
 <form id="addToCartForm" method="POST">
  <input type="hidden" name="add_to_cart" value="1">
  <input type="hidden" name="product_name" id="formName">
  <input type="hidden" name="product_category" id="formCategory">
  <input type="hidden" name="product_price" id="formPrice">
  <input type="hidden" name="product_Discount" id="formDiscount">
  <input type="hidden" name="product_quantity" id="formQty">
  <input type="hidden" name="product_size" id="formSize">
  <input type="hidden" name="product_sugar" id="formSugar">
  <input type="hidden" name="customer_nickname" id="formNickname">
  <input type="hidden" name="order_type" id="formOrderType" value="<?php echo htmlspecialchars($_SESSION['order_type'] ?? 'Dine In'); ?>">
</form>

      </div>

      <div class="modal-footer d-flex gap-3">
<button type="button" class="btn btn-danger flex-fill" id="addCartBtn">Add Order</button>
      </div>
    </div>
  </div>
</div>
<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="receiptModalLabel">Order Receipt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Receipt content will be loaded here via AJAX -->
        <div id="receiptContent" class="text-center">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading receipt...</span>
          </div>
          <p class="mt-2">Generating receipt...</p>
        </div>
      </div>
<div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="cancelCartBtn">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" id="confirmOrderBtn">
          Confirm Order
        </button>
       <button type="button" class="btn btn-success" id="printReceiptBtn">
  <i class="bi bi-file-earmark-pdf me-1"></i> Save as PDF
</button>
        
      </div>
    </div>
  </div>
</div>



<script src="modal-cart.js"></script>
<script src="category-filter.js"></script>
<script src="receipt-handler.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  loadCartItems();
});

</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

</body>
</html>
