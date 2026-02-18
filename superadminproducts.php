<?php

session_start();
include("php/config.php");

if ($_SESSION['Role'] !== 'Super Admin') {
    header("Location: login.php");
    exit();
}
// Delete Product
if (isset($_GET['delete'])) {
    $productNumber = $_GET['delete'];

    // Get image file name
    $stmt = $conn->prepare("SELECT ProductImage FROM tbl_products WHERE ProductNumber = ?");
    $stmt->bind_param("s", $productNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $imagePath = "images/" . $product['ProductImage'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete product
    $stmt = $conn->prepare("DELETE FROM tbl_products WHERE ProductNumber = ?");
    $stmt->bind_param("s", $productNumber);
    $stmt->execute();

    header("Location: superadminproducts.php");
    exit();
}

// Category Filter
$categoryFilter = $_GET['category'] ?? '';

// Fetch Categories (for dropdown)
$catStmt = $conn->prepare("SELECT CategoryName FROM tbl_categories ORDER BY CategoryName ASC");
$catStmt->execute();
$categories = $catStmt->get_result();

if ($categoryFilter != '') {
    $stmt = $conn->prepare("SELECT * FROM tbl_products WHERE ProductCategory = ? ORDER BY ProductNumber DESC");
    $stmt->bind_param("s", $categoryFilter);
} else {
    $stmt = $conn->prepare("SELECT * FROM tbl_products ORDER BY ProductNumber DESC");
}

$stmt->execute();
$products = $stmt->get_result();

// Count products for stats
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM tbl_products")->fetch_assoc()['count'];
$categoriesCount = $conn->query("SELECT COUNT(*) as count FROM tbl_categories")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="favcon/favcon.ico.png" type="image/x-icon">
  <link rel="stylesheet" href="admin.css">
  <title>Products Management</title>
  <style>
    :root {
      --primary-color: #6366f1;
      --secondary-color: #8b5cf6;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --dark-color: #1f2937;
      --light-color: #f9fafb;
    }

    * {
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
      min-height: 100vh;
    }

    .content {
      margin-left: 250px;
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

    /* Stats Cards */
    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: 1px solid #e5e7eb;
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 1rem;
    }

    .stat-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 0.25rem;
    }

    .stat-label {
      font-size: 0.875rem;
      color: #6b7280;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Main Card */
    .main-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: 1px solid #e5e7eb;
    }

    .card-header-custom {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
    }

    /* Search and Filter */
    .search-container {
      position: relative;
    }

    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .search-input {
      padding-left: 45px;
      border-radius: 10px;
      border: 1px solid #e5e7eb;
      height: 45px;
    }

    .filter-select {
      border-radius: 10px;
      border: 1px solid #e5e7eb;
      height: 45px;
      background: white;
    }

    .btn-action {
      padding: 0.5rem 1.25rem;
      border-radius: 10px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-add {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border: none;
    }

    .btn-add:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
    }

    /* Table Styling */
    .product-table {
      margin: 0;
    }

    .product-table thead th {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      padding: 1rem 1.25rem;
    }

    .product-table tbody td {
      padding: 1.25rem;
      vertical-align: middle;
      border-bottom: 1px solid #f3f4f6;
    }

    .product-table tbody tr:hover {
      background-color: #f8fafc;
    }

    /* Product Image */
    .product-img {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #f3f4f6;
      transition: transform 0.3s ease;
    }

    .product-img:hover {
      transform: scale(1.1);
    }

    /* Price Badges */
    .price-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      background: #f0f9ff;
      color: #0369a1;
      border: 1px solid #bae6fd;
    }

    .discount-badge {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    /* Action Buttons */
    .btn-edit, .btn-delete {
      padding: 0.375rem 0.75rem;
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 500;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-edit {
      background: #fef3c7;
      color: #92400e;
    }

    .btn-edit:hover {
      background: #fde68a;
      transform: translateY(-2px);
    }

    .btn-delete {
      background: #fee2e2;
      color: #991b1b;
    }

    .btn-delete:hover {
      background: #fecaca;
      transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
    }

    .empty-state-icon {
      font-size: 4rem;
      color: #d1d5db;
      margin-bottom: 1rem;
    }

  

   
  </style>
</head>
<body>

<?php include("superadminsidebar.php"); ?>

<div class="content" id="content">
      <?php include("superadminnavbar.php") ?>


   <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h1 class="fw-bold text-dark mb-1">Products Management</h1>
              <p class="text-muted mb-0">Manage your inventory, pricing, and product details.</p>
            </div>
          
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row g-4 mb-4">
        <div class="col-6">
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary-color);">
              <i class="fa-solid fa-box"></i>
            </div>
            <div class="stat-value"><?= $totalProducts ?></div>
            <div class="stat-label">Total Products</div>
          </div>
        </div>

        <div class="col-6">
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--secondary-color);">
              <i class="fa-solid fa-tags"></i>
            </div>
            <div class="stat-value"><?= $categoriesCount ?></div>
            <div class="stat-label">Categories</div>
          </div>
        </div>

       

       
      </div>

      <!-- Main Content Card -->
      <div class="main-card">
        <!-- Card Header -->
        <div class="card-header-custom">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h5 class="fw-bold mb-0">Product List</h5>
            </div>
            <div class="col-md-6">
              <div class="row g-2">
                <div class="col-md-7">
                  <div class="search-container">
                    <i class="bi bi-search search-icon"></i>
                    <input id="live_search" type="text" class="form-control search-input" placeholder="Search products...">

                  </div>
                </div>
                <div class="col-md-5">
                  <select class="form-select filter-select" id="categoryFilter">
                    <option value="" <?= ($categoryFilter == '') ? 'selected' : '' ?>>All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()) { ?>
                      <option value="<?= $cat['CategoryName'] ?>" <?= ($categoryFilter == $cat['CategoryName']) ? 'selected' : '' ?>>
                        <?= $cat['CategoryName'] ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-4">
          <!-- Action Buttons -->
          <div class="row mb-4">
            <div class="col-md-8">
              <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-action" onclick="applyFilter()">
                  <i class="bi bi-funnel me-2"></i>Apply Filter
                </button>
                <button class="btn btn-outline-secondary btn-action" onclick="resetFilter()">
                  <i class="bi bi-arrow-clockwise me-2"></i>Reset Filter
                </button>
              </div>
            </div>
         
          </div>

          <!-- Products Table -->
          <div class="table-responsive">
            <table class="table product-table">
              <thead>
                <tr>
                  <th width="50">#</th>
                  <th width="80">Image</th>
                  <th>Product Info</th>
                  <th>Category</th>
                  <th>Pricing</th>
                  <th>Discount</th>
                  <th>Availability</th>
                  <th width="180">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($products->num_rows > 0): ?>
                  <?php $i = 1; ?>
                  <?php while ($row = $products->fetch_assoc()): ?>
                    <tr>
                      <td class="fw-semibold"><?= $i++ ?></td>
                      <td>
                        <img src="images/<?= $row['ProductImage'] ?>" 
                             alt="Product" 
                             class="product-img"
                             onerror="this.src='https://via.placeholder.com/60'">
                      </td>
                      <td>
                        <div class="fw-semibold text-dark"><?= $row['ProductName'] ?></div>
                        <small class="text-muted">ID: <?= $row['ProductNumber'] ?></small>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark"><?= $row['ProductCategory'] ?></span>
                      </td>
                      <td>
                        <div class="d-flex flex-column gap-1">
                          <?php if($row['ProductPriceSmall'] > 0): ?>
                            <span class="price-badge">Small: ₱<?= number_format($row['ProductPriceSmall'], 2) ?></span>
                          <?php endif; ?>
                          <?php if($row['ProductPriceLarge'] > 0): ?>
                            <span class="price-badge">Large:₱<?= number_format($row['ProductPriceLarge'], 2) ?></span>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td>
                        <?php if($row['Discount'] > 0): ?>
                          <span class="badge discount-badge">-<?= $row['Discount'] ?>%</span>
                        <?php else: ?>
                          <span class="text-muted">No discount</span>
                        <?php endif; ?>
                      </td>
                       <td>
                        <?php if($row['Availability'] == "Available"): ?>
                          <span class="badge discount-badge"><?= $row['Availability'] ?></span>
                        <?php else: ?>
                          <span class="badge discount-badge">Not Available</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex gap-2">
                          <a href="editproduct.php?productNumber=<?= $row['ProductNumber'] ?>" 
                             class="btn btn-edit">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                          </a>
                          <a href="superadminproducts.php?delete=<?= $row['ProductNumber'] ?>" 
                             class="btn btn-delete"
                             onclick="return confirm('Are you sure you want to delete this product?');">
                            <i class="bi bi-trash me-1"></i>Delete
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7">
                      <div class="empty-state">
                        <div class="empty-state-icon">
                          <i class="bi bi-box-seam"></i>
                        </div>
                        <h4 class="text-muted">No products found</h4>
                        <p class="text-muted mb-4">Get started by adding your first product</p>
                        <a href="superadminaddnewproduct.php" class="btn btn-add">
                          <i class="bi bi-plus-lg me-2"></i>Add Product
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

         
        </div>
      </div>
   </div>

   <?php include("footer.php") ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){
    $("#live_search").keyup(function(){
        var input = $(this).val();
        $.ajax({
            type: "POST",
            url: "search_products.php",
            data: { query: input },
            success: function(response){
                $("tbody").html(response);
            }
        });
    });
});
</script>


<script src="sidebar-and-date-utc.js"></script>
<script src="filter_products.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>