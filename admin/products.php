<?php
session_start();
require_once '../config/database.php';
require_once '../utils/functions.php';


if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}


$database = new Database();
$db = $database->getConnection();


$error = '';
$success = '';
$name = '';
$description = '';
$price = '';
$category_id = '';
$stock = '';
$featured = 0;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        
        $name = clean_input($_POST['name']);
        $description = clean_input($_POST['description']);
        $price = clean_input($_POST['price']);
        $category_id = clean_input($_POST['category_id']);
        $stock = clean_input($_POST['stock']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $product_id = isset($_POST['product_id']) ? clean_input($_POST['product_id']) : null;
        
        
        if (empty($name) || empty($description) || empty($price) || empty($category_id) || empty($stock)) {
            $error = 'Please fill in all fields';
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = 'Price must be a positive number';
        } elseif (!is_numeric($stock) || $stock < 0) {
            $error = 'Stock quantity must be a non-negative number';
        } else {
            
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {                                   //Handle image upload
                $upload_dir = '../image/';
                $temp_name = $_FILES['image']['tmp_name'];
                $image_name = basename($_FILES['image']['name']);
                $unique_image = generate_random_string(10) . '_' . $image_name;
                $upload_path = $upload_dir . $unique_image;
                
                
                $check = getimagesize($temp_name);
                if ($check === false) {
                    $error = 'File is not an image';
                } else {
                    
                    if (move_uploaded_file($temp_name, $upload_path)) {
                        $image = $unique_image;
                    } else {
                        $error = 'Failed to upload image';
                    }
                }
            }
            
            if (empty($error)) {
                if (isset($_POST['update_product']) && !empty($product_id)) {
                    
                    $query = "UPDATE products SET 
                              name = :name, 
                              description = :description, 
                              price = :price, 
                              category_id = :category_id, 
                              stock = :stock,
                              featured = :featured";
                    
                    
                    if (!empty($image)) {
                        $query .= ", image = :image";
                    }
                    
                    $query .= " WHERE product_id = :product_id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->bindParam(':stock', $stock);
                    $stmt->bindParam(':featured', $featured);
                    $stmt->bindParam(':product_id', $product_id);
                    
                    if (!empty($image)) {
                        $stmt->bindParam(':image', $image);
                    }
                    
                    if ($stmt->execute()) {
                        $success = 'Product updated successfully';
                        
                        $name = $description = $price = $category_id = $stock = '';
                    } else {
                        $error = 'Failed to update product';
                    }
                } else {
                    
                    $query = "INSERT INTO products (name, description, price, image, category_id, stock, featured) 
                              VALUES (:name, :description, :price, :image, :category_id, :stock, :featured)";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->bindParam(':stock', $stock);
                    
                    
                    if (empty($image)) {
                        $image = 'default-product.jpg';
                    }
                    $stmt->bindParam(':image', $image);
                    
                    
                    $featured = isset($_POST['featured']) ? 1 : 0;
                    $stmt->bindParam(':featured', $featured);
                    
                    if ($stmt->execute()) {
                        $success = 'Product added successfully';
                       
                        $name = $description = $price = $category_id = $stock = '';
                    } else {
                        $error = 'Failed to add product';
                    }
                }
            }
        }
    }
    
    
    if (isset($_POST['delete_product'])) {
        $product_id = clean_input($_POST['product_id']);
        
        
        $query = "DELETE FROM products WHERE product_id = :product_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        
        if ($stmt->execute()) {
            $success = 'Product deleted successfully';
        } else {
            $error = 'Failed to delete product';
        }
    }
}


$edit_product = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $product_id = clean_input($_GET['edit']);
    
    $query = "SELECT * FROM products WHERE product_id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = $edit_product['name'];
        $description = $edit_product['description'];
        $price = $edit_product['price'];
        $category_id = $edit_product['category_id'];
        $stock = $edit_product['stock'];
        $featured = $edit_product['featured'];
    }
}


$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          ORDER BY p.product_id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Pet World Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
    .sidebar {
        min-height: 100vh;
        background-color: #343a40;
        color: white;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.75);
    }

    .sidebar .nav-link:hover {
        color: rgba(255, 255, 255, 1);
    }

    .sidebar .nav-link.active {
        color: #fff;
        background-color: #007bff;
    }

    .product-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">

            <?php include 'sidebar.php'; ?>


            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Products</h1>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>


                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No products found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['product_id']; ?></td>
                                        <td>
                                            <img src="../image/<?php echo $product['image']; ?>"
                                                alt="<?php echo $product['name']; ?>" class="product-img">
                                        </td>
                                        <td><?php echo $product['name']; ?></td>
                                        <td><?php echo $product['category_name']; ?></td>
                                        <td><?php echo format_price($product['price']); ?></td>
                                        <td><?php echo $product['stock']; ?></td>
                                        <td>
                                            <a href="products.php?edit=<?php echo $product['product_id']; ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                                data-target="#deleteModal<?php echo $product['product_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>


                                            <div class="modal fade"
                                                id="deleteModal<?php echo $product['product_id']; ?>" tabindex="-1"
                                                role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete the product
                                                            "<?php echo $product['name']; ?>"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Cancel</button>
                                                            <form method="POST">
                                                                <input type="hidden" name="product_id"
                                                                    value="<?php echo $product['product_id']; ?>">
                                                                <button type="submit" name="delete_product"
                                                                    class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <div class="modal fade" id="<?php echo isset($edit_product) ? 'editProductModal' : 'addProductModal'; ?>"
        tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">
                        <?php echo isset($edit_product) ? 'Edit Product' : 'Add New Product'; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if (isset($edit_product)): ?>
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                required><?php echo $description; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="price">Price ($)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01"
                                    value="<?php echo $price; ?>" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="stock">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock"
                                    value="<?php echo $stock; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <?php if (isset($edit_product) && !empty($edit_product['image'])): ?>
                            <div class="mb-2">
                                <img src="../image/<?php echo $edit_product['image']; ?>" alt="Current Image"
                                    style="max-width: 200px;">
                                <p class="text-muted">Current image. Upload a new one to replace it.</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*"
                                <?php echo isset($edit_product) ? '' : 'required'; ?>>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit"
                                name="<?php echo isset($edit_product) ? 'update_product' : 'add_product'; ?>"
                                class="btn btn-primary">
                                <?php echo isset($edit_product) ? 'Update Product' : 'Add Product'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php if (isset($edit_product)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        editModal.show();
    });
    </script>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>