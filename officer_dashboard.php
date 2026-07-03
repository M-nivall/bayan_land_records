<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'officer') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Handle "mark as surveyed" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['case_id'])) {
    $caseId = (int) $_POST['case_id'];
    $stmt = $conn->prepare("UPDATE land_cases SET status = 'surveyed' WHERE id = ? AND created_by = ?");
    $stmt->bind_param('ii', $caseId, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: officer_dashboard.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM land_cases WHERE created_by = ? ORDER BY id DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$cases = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Officer Dashboard - Bayan Land Records</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 24px; }
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    h1 { color: #1e3d59; margin: 0; font-size: 22px; }
    .logout-link { color: #b71c1c; text-decoration: none; font-size: 14px; }
    .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
    th { background: #1e3d59; color: #fff; }
    .status { padding: 4px 10px; border-radius: 12px; font-size: 12px; color: #fff; }
    .status-pending { background: #f39c12; }
    .status-surveyed { background: #2980b9; }
    .status-approved { background: #27ae60; }
    button.survey-btn { padding: 6px 12px; background: #2980b9; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
    button.survey-btn:hover { background: #206694; }
    .empty { color: #888; font-size: 14px; padding: 16px 0; }
</style>
</head>
<body>
    <div class="topbar">
        <h1>Officer Dashboard — Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
        <a class="logout-link" href="logout.php">Logout</a>
    </div>

    <div class="card">
        <h2 style="margin-top:0; font-size:16px; color:#1e3d59;">My Land Cases</h2>
        <?php if ($cases->num_rows === 0): ?>
            <div class="empty">You haven't created any land cases yet.</div>
        <?php else: ?>
        <table>
            <tr>
                <th>LRN</th><th>Owner</th><th>Location</th><th>Size (m²)</th><th>Status</th><th>Action</th>
            </tr>
            <?php while ($case = $cases->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($case['lrn']) ?></td>
                <td><?= htmlspecialchars($case['owner_name']) ?></td>
                <td><?= htmlspecialchars($case['location']) ?></td>
                <td><?= htmlspecialchars($case['size_m2']) ?></td>
                <td><span class="status status-<?= htmlspecialchars($case['status']) ?>"><?= htmlspecialchars($case['status']) ?></span></td>
                <td>
                    <?php if ($case['status'] === 'pending'): ?>
                    <form method="POST" action="officer_dashboard.php" style="margin:0;">
                        <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
                        <button type="submit" class="survey-btn">Mark as Surveyed</button>
                    </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>
