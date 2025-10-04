<?php
session_start();
require_once 'config.php';

// ✅ Block access if not logged in or not admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center; margin-top:50px;'>❌ Access Denied: Admins only</h2>";
    echo "<p style='text-align:center;'><a href='login.php'>Go to Login</a></p>";
    exit();
}

// Fetch all users
try {
    $stmt = $conn->query("SELECT user_id, username, email, role, active, created_at FROM users ORDER BY user_id ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>User Management - AdzDIAG</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
</head>
<body>
<div class="container py-5">
  <h2>User Management (Admin Only)</h2>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($users): ?>
        <?php foreach($users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['user_id']) ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= $u['active'] ? "✅ Active" : "❌ Inactive" ?></td>
            <td><?= htmlspecialchars($u['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No users found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
  <a href="logout.php" class="btn btn-danger float-end">Logout</a>
</div>
</body>
</html>
