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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
        
        $name = clean_input($_POST['name']);
        $description = clean_input($_POST['description']);
        $category_id = isset($_POST['category_id']) ? clean_input($_POST['category_id']) : null;
        
        
        if (empty($name)) {
            $error = 'Category name is required';
        } else {
            if (isset($_POST['update_category']) && !empty($category_id)) {
               
                $query = "UPDATE categories SET name = :name, description = :description WHERE category_id = :category_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':category_id', $category_id);
                
                if ($stmt->execute()) {
                    $success = 'Category updated successfully';
                    
                    $name = $description = '';
                } else {
                    $error = 'Failed to update category';
                }
            } else {
                
                $query = "SELECT * FROM categories WHERE name = :name";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $error = 'Category with this name already exists';
                } else {
                    
                    $query = "INSERT INTO categories (name, description) VALUES (:name, :description)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    
                    if ($stmt->execute()) {
                        $success = 'Category added successfully';
                        
                        $name = $description = '';
                    } else {
                        $error = 'Failed to add category';
                    }
                }
            }
        }
    }
    
    
    if (isset($_POST['delete_category'])) {
        $category_id = clean_input($_POST['category_id']);
        
        
        $query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = :category_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['product_count'] > 0) {
            $error = 'Cannot delete category because it has products associated with it';
        } else {
            
            $query = "DELETE FROM categories WHERE category_id = :category_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':category_id', $category_id);
            
            if ($stmt->execute()) {
                $success = 'Category deleted successfully';
            } else {
                $error = 'Failed to delete category';
            }
        }
    }
}


$edit_category = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $category_id = clean_input($_GET['edit']);
    
    $query = "SELECT * FROM categories WHERE category_id = :category_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':category_id', $category_id);                                         // Get category to edit
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = $edit_category['name'];
        $description = $edit_category['description'];
    }
}


$query = "SELECT c.*, COUNT(p.product_id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.category_id = p.category_id 
          GROUP BY c.category_id 
          ORDER BY c.name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Pet World Admin</title>
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
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">

            <?php include 'sidebar.php'; ?>


            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Categories</h1>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="fas fa-plus"></i> Add New Category
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
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No categories found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['category_id']; ?></td>
                                        <td><?php echo $category['name']; ?></td>
                                        <td><?php echo $category['description']; ?></td>
                                        <td><?php echo $category['product_count']; ?></td>
                                        <td>
                                            <a href="categories.php?edit=<?php echo $category['category_id']; ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                                data-target="#deleteModal<?php echo $category['category_id']; ?>"
                                                <?php echo $category['product_count'] > 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>

                                            <!-- Delete button -->
                                            <div class="modal fade"
                                                id="deleteModal<?php echo $category['category_id']; ?>" tabindex="-1"
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
                                                            Are you sure you want to delete the category
                                                            "<?php echo $category['name']; ?>"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Cancel</button>
                                                            <form method="POST">
                                                                <input type="hidden" name="category_id"
                                                                    value="<?php echo $category['category_id']; ?>">
                                                                <button type="submit" name="delete_category"
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


    <div class="modal fade" id="<?php echo isset($edit_category) ? 'editCategoryModal' : 'addCategoryModal'; ?>"
        tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">
                        <?php echo isset($edit_category) ? 'Edit Category' : 'Add New Category'; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <?php if (isset($edit_category)): ?>
                        <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="3"><?php echo $description; ?></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit"
                                name="<?php echo isset($edit_category) ? 'update_category' : 'add_category'; ?>"
                                class="btn btn-primary">
                                <?php echo isset($edit_category) ? 'Update Category' : 'Add Category'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php if (isset($edit_category)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        editModal.show();
    });
    </script>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>