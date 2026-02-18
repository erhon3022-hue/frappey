<?php
session_start();
include("php/config.php");

// Get product number from URL
$productNumber = $_GET['productNumber'] ?? null;

if (!$productNumber) {
    die("Product Number is missing.");
}

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM tbl_products WHERE ProductNumber = ?");
$stmt->bind_param("s", $productNumber);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Update product
if (isset($_POST['update_product'])) {

    $productName = $_POST['productName'];
    $productCategory = $_POST['productCategory'];
    $productDescription = $_POST['productDescription'];
    $priceSmall = $_POST['priceSmall'];
    $priceLarge = $_POST['priceLarge'];
    $discount = $_POST['discount'];
    $availability = $_POST['availability'];

    // Image Upload
    $imagePath = $product['ProductImage']; // keep old image

    if (!empty($_FILES['productImage']['name'])) {
        $imageName = $_FILES['productImage']['name'];
        $imageTmp = $_FILES['productImage']['tmp_name'];
        $uploadDir = "images/";

        // Validate file type
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];
        $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Only PNG, JPG, JPEG & WEBP files are allowed.";
        } else {
            $newFileName = uniqid('prod_') . '.' . $ext;
            $imagePath = $newFileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            move_uploaded_file($imageTmp, $uploadDir . $newFileName);
        }
    }

    // Update query
    $stmt = $conn->prepare("UPDATE tbl_products SET ProductName=?, ProductCategory=?, ProductDescription=?, ProductPriceSmall=?, ProductPriceLarge=?, Discount=?,Availability=?, ProductImage=? WHERE ProductNumber=?");
    $stmt->bind_param("sssssssss", $productName, $productCategory, $productDescription, $priceSmall, $priceLarge, $discount,$availability, $imagePath, $productNumber);

    if ($stmt->execute()) {
        $success = "Product Updated Successfully!";
        header("Location: superadminproducts.php"); 
    } else {
        $error = "Failed to update product.";
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
  <link rel="stylesheet" href="admin.css">
  <title>Edit Product</title>
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
 <?php include("superadminnavbar.php"); ?>


   <div class="container mt-4">
       <h1 class="fw-bold text-dark mb-1">Edit Product</h2>
      <p class="text-muted mb-0">Update the details below.</p>

       <?php if(isset($success)) { ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
       <?php } ?>

       <?php if(isset($error)) { ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
       <?php } ?>

    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to update the product?');" >
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Product Number</label>
          <input type="text" name="productNumber" class="form-control" value="<?php echo $product['ProductNumber']; ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Product Name</label>
          <input type="text" name="productName" class="form-control" value="<?php echo $product['ProductName']; ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select name="productCategory" class="form-select" required>
            <option value="" disabled>Select Category</option>
            <option value="Frappe" <?php if($product['ProductCategory']=="Frappe") echo "selected"; ?>>Frappe</option>
            <option value="Coffee-Based" <?php if($product['ProductCategory']=="Coffee-Based") echo "selected"; ?>>Coffee-Based</option>
            <option value="Specialty Frappe" <?php if($product['ProductCategory']=="Specialty Frappe") echo "selected"; ?>>Specialty Frappe</option>
            <option value="Milk Tea" <?php if($product['ProductCategory']=="Milk Tea") echo "selected"; ?>>Milk Tea</option>
            <option value="Fruit Series/Yogurt Series" <?php if($product['ProductCategory']=="Fruit Series/Yogurt Series") echo "selected"; ?>>Fruit Series/Yogurt Series</option>
            <option value="Fruit Smoothies/Yogurt Smoothies" <?php if($product['ProductCategory']=="Fruit Smoothies/Yogurt Smoothies") echo "selected"; ?>>Fruit Smoothies/Yogurt Smoothies</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Description</label>
          <input type="text" name="productDescription" class="form-control" value="<?php echo $product['ProductDescription']; ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Price Small</label>
          <input type="number" step="0.01" name="priceSmall" class="form-control" value="<?php echo $product['ProductPriceSmall']; ?>" required>
        </div>

        

        <div class="col-md-4">
          <label class="form-label">Price Large</label>
          <input type="number" step="0.01" name="priceLarge" class="form-control" value="<?php echo $product['ProductPriceLarge']; ?>" required>
        </div>
        
        <div class="col-md-4">
          <label class="form-label">Discount (%)</label>
          <input type="number" step="0.01" name="discount" class="form-control" value="<?php echo $product['Discount']; ?>" required>
        </div>

         <div class="col-md-4">
          <label class="form-label">Availability</label>
        <select name="availability" class="form-select" required>
             <option value="" selected disabled><?php echo $product['Availability']; ?></option>
             <option value="Available">Available</option>
             <option value="Not Available">Not Available</option>
  
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Product Image</label>
          <input type="file" name="productImage" class="form-control">
          <small class="text-muted">Leave blank to keep current image</small>
        </div>

      </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end">
      <button type="submit" name="update_product" class="btn btn-success px-4">
        <i class="fa-solid fa-pen me-2"></i> Update Product
      </button>
    </div>
  </div>
</form>

   </div>

<?php include("footer.php") ?>
</div>

<script src="sidebar-and-date-utc.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
