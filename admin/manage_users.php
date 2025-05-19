<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ? AND isAdmin = FALSE");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_users.php");
    exit();
}

// Handle toggle admin status
if (isset($_GET['toggle_admin']) && is_numeric($_GET['toggle_admin'])) {
    $stmt = $conn->prepare("UPDATE users SET isAdmin = NOT isAdmin WHERE userID = ?");
    $stmt->execute([$_GET['toggle_admin']]);
    header("Location: manage_users.php");
    exit();
}

// Get all users
$stmt = $conn->prepare("SELECT * FROM users ORDER BY email");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Users</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">User List</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge <?= $user['isAdmin'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $user['isAdmin'] ? 'Admin' : 'User' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?toggle_admin=<?= $user['userID'] ?>" class="btn btn-sm btn-primary">
                                Toggle Admin
                            </a>
                            <?php if (!$user['isAdmin']): ?>
                                <a href="?delete=<?= $user['userID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>