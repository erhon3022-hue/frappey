<?php
session_start();
include("php/config.php");

if ($_SESSION['Role'] !== 'Super Admin') {
    header("Location: login.php");
    exit();
}
// Fetch categories from database
$catStmt = $conn->prepare("SELECT CategoryName FROM tbl_categories ORDER BY CategoryName ASC");
$catStmt->execute();
$categories = $catStmt->get_result();

// Generate product number if not set
if (!isset($_SESSION['product_number'])) {
    $_SESSION['product_number'] = 'PROD-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

if (isset($_POST['reset_product_number'])) {
    $_SESSION['product_number'] = 'PROD-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    header("Location: superadminaddnewproduct.php");
    exit();
}

if (isset($_POST['add_product'])) {

    $productNumber = $_SESSION['product_number'];
    $productName = $_POST['productName'];
    $productCategory = $_POST['productCategory'];
    $productDescription = $_POST['productDescription'];
    $priceSmall = $_POST['priceSmall'];
    $priceLarge = $_POST['priceLarge'];
    $discount = $_POST['discount'];
    $availability = $_POST['availability'];

    // Image Upload
    $imageName = $_FILES['productImage']['name'];
    $imageTmp = $_FILES['productImage']['tmp_name'];
    $uploadDir = "images/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowed = ['png', 'jpg', 'jpeg', 'webp'];
    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $error = "Only PNG, JPG, JPEG & WEBP files are allowed.";
    } else {

        $newFileName = uniqid('prod_') . '.' . $ext;
        $imagePath = $newFileName;

        if (move_uploaded_file($imageTmp, $uploadDir . $newFileName)) {

            $stmt = $conn->prepare("INSERT INTO tbl_products (ProductNumber, ProductName, ProductCategory, ProductDescription, ProductPriceSmall,ProductPriceLarge, Discount,Availability, ProductImage)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $productNumber, $productName, $productCategory, $productDescription, $priceSmall, $priceLarge, $discount,$availability, $imagePath);

            if ($stmt->execute()) {
                $success = "Product Added Successfully!";

                $_SESSION['product_number'] = 'PROD-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            } else {
                $error = "Failed to add product.";
            }

        } else {
            $error = "Image upload failed.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="shortcut icon" href="favcon/favcon.ico.png" type="image/x-icon">
  <link rel="stylesheet" href="admin.css">
  <title>Add Product</title>
</head>
<style>
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
<!-- RESET FORM (hidden) -->
<form id="resetForm" method="POST" style="display:none;">
  <input type="hidden" name="reset_product_number" value="1">
</form>

<div class="content" id="content">
    <?php include("superadminnavbar.php") ?>


   <div class="container mt-4">
       <h1 class="fw-bold text-dark mb-1">Add Product</h2>
      <p class="text-muted mb-0">Fill in the details below to create a new product.</p>

       <?php if(isset($success)) { ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
       <?php } ?>

       <?php if(isset($error)) { ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
       <?php } ?>

    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to add this product?');">
  <div class="card shadow-sm">
    

    <div class="card-body">
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Product Number</label>
         <input type="text" name="productNumber" class="form-control" value="<?php echo $_SESSION['product_number']; ?>" readonly>

        </div>

        <div class="col-md-6">
          <label class="form-label">Product Name</label>
          <input type="text" name="productName" class="form-control" placeholder="e.g., Milk Tea" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Category</label>
        <select name="productCategory" class="form-select" required>
    <option value="" selected disabled>Select Category</option>

    <?php while ($cat = $categories->fetch_assoc()) { ?>
        <option value="<?php echo $cat['CategoryName']; ?>">
            <?php echo $cat['CategoryName']; ?>
        </option>
    <?php } ?>
</select>

        </div>

        <div class="col-md-6">
          <label class="form-label">Description</label>
          <input type="text" name="productDescription" class="form-control" placeholder="e.g., Brown Sugar Milk Tea" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Price Small</label>
          <input type="number" step="0.01" name="priceSmall" class="form-control" placeholder="₱0.00" required>
        </div>

      

        <div class="col-md-4">
          <label class="form-label">Price Large</label>
          <input type="number" step="0.01" name="priceLarge" class="form-control" placeholder="₱0.00" required>
        </div>

        <div class="col-md-4">
        <label class="form-label">Discount (%)</label>
        <input type="number" step="0.01" name="discount" class="form-control" placeholder="0.00" value="0">
        </div>

        <div class="col-md-4">
          <label class="form-label">Availability</label>
        <select name="availability" class="form-select" required>
             <option value="" selected disabled>Select Availability</option>
             <option value="Available">Available</option>
             <option value="Not Available">Not Available</option>
  
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Product Image</label>
          <input type="file" name="productImage" class="form-control" required>
        </div>
      </div>
    </div>

  <div class="card-footer bg-white d-flex justify-content-end">
  <button type="button" class="btn btn-secondary me-2" onclick="resetProductNumber()">
    <i class="fa-solid fa-rotate"></i> Reset Number
  </button>

  <button type="submit" name="add_product" class="btn btn-primary px-4">
    <i class="fa-solid fa-plus me-2"></i> Add Product
  </button>
</div>

  </div>
</form>

   </div>

<?php include("footer.php") ?>
</div>
          <script src="sidebar-and-date-utc.js"></script>
<script>
  function resetProductNumber() {
    document.getElementById('resetForm').submit();
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
