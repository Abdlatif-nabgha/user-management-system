<?php
// Database connection
$pdo = new PDO(
    "mysql:host=localhost;dbname=enset-2025",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['idd'])) {
    $id = $_GET['idd'];
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    header("Location: index.php");
    exit();
}

// Add action
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $email = $_POST['email'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users VALUES(NULL, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $pass, $role]);
    header("Location: index.php");
    exit();
}

// Update action
if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $id = $_POST['idd'];
    $email = $_POST['email'];
    $pass = !empty($_POST['pass']) ? password_hash($_POST['pass'], PASSWORD_DEFAULT) : $_POST['old_pass'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET email = ?, password = ?, role = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $pass, $role, $id]);
    header("Location: index.php");
    exit();
}

// Edit action
$userToEdit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['idd'])) {
    $id = $_GET['idd'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $userToEdit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userToEdit) {
        header("Location: index.php");
        exit();
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link rel="stylesheet" href="https://bootswatch.com/5/litera/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            /* Light gray background */
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.9em;
            min-width: 400px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .users-table thead tr {
            background-color: #3a7ca5;
            color: #ffffff;
            text-align: left;
            font-weight: bold;
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
        }

        .users-table tbody tr {
            border-bottom: 1px solid #dddddd;
            transition: all 0.3s;
        }

        .users-table tbody tr:nth-of-type(even) {
            background-color: #ffffff;
        }

        .users-table tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }

        .users-table tbody tr:last-of-type {
            border-bottom: 2px solid #3a7ca5;
        }

        .users-table tbody tr:hover {
            background-color: #e9ecef;
        }

        .action-btn {
            margin: 0 5px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <?php if ($userToEdit): ?>
            <!-- Edit Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Edit User</h2>
                </div>
                <div class="card-body">
                    <form action="index.php" method="post">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="idd" value="<?= htmlspecialchars($userToEdit['id']) ?>">
                        <input type="hidden" name="old_pass" value="<?= htmlspecialchars($userToEdit['password']) ?>">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($userToEdit['email']) ?>"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" name="pass" placeholder="New password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="guest" <?= $userToEdit['role'] == 'guest' ? 'selected' : '' ?>>Guest</option>
                                <option value="author" <?= $userToEdit['role'] == 'author' ? 'selected' : '' ?>>Author</option>
                                <option value="admin" <?= $userToEdit['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <button class="btn btn-primary">Save</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Main Page with List and Add Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h1 class="mb-0">Users Management</h1>
                </div>
                <div class="card-body">
                    <form action="index.php" method="post">
                        <input type="hidden" name="action" value="add">

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="pass" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="guest">Guest</option>
                                    <option value="author">Author</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button class="btn btn-success">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Users List</h2>
                </div>
                <div class="card-body p-0">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>EMAIL</th>
                                <th>ROLE</th>
                                <th class="text-center">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                                    <td class="text-center">
                                        <a href="index.php?action=edit&idd=<?= $user['id'] ?>"
                                            class="btn btn-primary action-btn" title="Edit">
                                            E
                                        </a>
                                        <a onclick="confirmDelete(event)"
                                            href="index.php?action=delete&idd=<?= $user['id'] ?>"
                                            class="btn btn-danger action-btn" title="Delete">
                                            X
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this user?')) {
                location.href = e.target.closest('a').href;
            }
        }
    </script>
</body>

</html>