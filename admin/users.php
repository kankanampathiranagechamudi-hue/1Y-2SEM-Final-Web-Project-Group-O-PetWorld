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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_role') {
        $user_id = clean_input($_POST['user_id']);
        $role = clean_input($_POST['role']);
        
        
        if ($role !== 'user' && $role !== 'admin') {
            $error = 'Invalid role';
        } else {
            $query = "UPDATE users SET role = :role WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                $success = 'User role updated successfully';
            } else {
                $error = 'Failed to update user role';
            }
        }
    }
    
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $user_id = clean_input($_POST['user_id']);
        
        
        if ($user_id == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account';
        } else {
            
            $db->beginTransaction();
            
            try {
                
                $query = "DELETE FROM cart_items WHERE user_id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                
                $query = "DELETE FROM users WHERE user_id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                
                $db->commit();
                
                $success = 'User deleted successfully';
            } catch (Exception $e) {
                
                $db->rollBack();
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }
    }
}


$page = isset($_GET['page']) ? (int)clean_input($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;


$query = "SELECT COUNT(*) as total FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $limit);


$query = "SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Pet World Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
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
        width: 50px;
        height: 50px;
        object-fit: cover;
    }

    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem;
    }

    .status-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .status-processing {
        background-color: #17a2b8;
        color: white;
    }

    .status-shipped {
        background-color: #007bff;
        color: white;
    }

    .status-delivered {
        background-color: #28a745;
        color: white;
    }

    .status-cancelled {
        background-color: #dc3545;
        color: white;
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
                    <h1 class="h2">User Management</h1>
                </div>

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo $user['full_name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-toggle="modal"
                                                data-target="#editRoleModal<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-edit"></i> Edit Role
                                            </button>
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-toggle="modal"
                                                data-target="#deleteModal<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>


                                    <div class="modal fade" id="editRoleModal<?php echo $user['user_id']; ?>"
                                        tabindex="-1"
                                        aria-labelledby="editRoleModalLabel<?php echo $user['user_id']; ?>"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="editRoleModalLabel<?php echo $user['user_id']; ?>">Edit User
                                                        Role</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_role">
                                                        <input type="hidden" name="user_id"
                                                            value="<?php echo $user['user_id']; ?>">
                                                        <div class="form-group">
                                                            <label
                                                                for="role<?php echo $user['user_id']; ?>">Role</label>
                                                            <select class="form-control"
                                                                id="role<?php echo $user['user_id']; ?>" name="role">
                                                                <option value="user"
                                                                    <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>
                                                                    User</option>
                                                                <option value="admin"
                                                                    <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                                                    Admin</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save
                                                            Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="modal fade" id="deleteModal<?php echo $user['user_id']; ?>"
                                        tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['user_id']; ?>"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="deleteModalLabel<?php echo $user['user_id']; ?>">Confirm
                                                        Delete</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete user
                                                    <strong><?php echo $user['full_name']; ?></strong>? This action
                                                    cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancel</button>
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="user_id"
                                                            value="<?php echo $user['user_id']; ?>">
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-4">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>