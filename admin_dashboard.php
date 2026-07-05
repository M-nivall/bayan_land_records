<?php
session_start();
require 'config.php';
require 'lrn_helper.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_name = trim($_POST['owner_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $size_m2 = trim($_POST['size_m2'] ?? '');

    if ($owner_name === '' || $location === '' || $size_m2 === '') {
        $error = 'All fields are required.';
    } else {
        $lrn = generateLRN($conn);
        $stmt = $conn->prepare("INSERT INTO land_cases (lrn, owner_name, location, size_m2, status, created_by) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param('sssii', $lrn, $owner_name, $location, $size_m2, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "Land case added successfully with LRN: $lrn";
        } else {
            $error = 'Failed to add land case.';
        }
        $stmt->close();
    }
}

$cases = $conn->query("SELECT lc.*, u.name AS officer_name FROM land_cases lc LEFT JOIN users u ON lc.created_by = u.id ORDER BY lc.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Bayan Land Records</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 24px; }
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    h1 { color: #1e3d59; margin: 0; font-size: 22px; }
    .logout-link { color: #b71c1c; text-decoration: none; font-size: 14px; }
    .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
    th { background: #1e3d59; color: #fff; }
    .status { padding: 4px 10px; border-radius: 12px; font-size: 12px; color: #fff; }
    .status-pending { background: #f39c12; }
    .status-surveyed { background: #2980b9; }
    .status-approved { background: #27ae60; }
    form.inline-form { display: grid; grid-template-columns: repeat(3, 1fr) auto; gap: 12px; align-items: end; }
    label { display: block; font-size: 13px; margin-bottom: 4px; color: #333; }
    input[type=text], input[type=number] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { padding: 9px 16px; background: #1e3d59; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #16304a; }
    .success { background: #e8f5e9; color: #1b5e20; padding: 10px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
    .error { background: #fdecea; color: #b71c1c; padding: 10px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
</style>
</head>
<body>
    <div class="topbar">
        <h1>Admin Dashboard — Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
        <a class="logout-link" href="logout.php">Logout</a>
    </div>

    <div class="card">
        <h2 style="margin-top:0; font-size:16px; color:#1e3d59;">Add New Land Case</h2>
        <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form class="inline-form" method="POST" action="admin_dashboard.php">
            <div>
                <label>Owner Name</label>
                <input type="text" name="owner_name" required>
            </div>
            <div>
                <label>Location</label>
                <input type="text" name="location" required>
            </div>
            <div>
                <label>Size (m²)</label>
                <input type="number" name="size_m2" required>
            </div>
            <button type="submit">Add Case</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top:0; font-size:16px; color:#1e3d59;">All Land Cases</h2>
        <table>
            <tr>
                <th>LRN</th><th>Owner</th><th>Location</th><th>Size (m²)</th><th>Status</th><th>Created By</th><th>Created At</th>
            </tr>
            <?php while ($case = $cases->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($case['lrn']) ?></td>
                <td><?= htmlspecialchars($case['owner_name']) ?></td>
                <td><?= htmlspecialchars($case['location']) ?></td>
                <td><?= htmlspecialchars($case['size_m2']) ?></td>
                <td><span class="status status-<?= htmlspecialchars($case['status']) ?>"><?= htmlspecialchars($case['status']) ?></span></td>
                <td><?= htmlspecialchars($case['officer_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($case['created_at']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
