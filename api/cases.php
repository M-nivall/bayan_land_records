<?php

header('Content-Type: application/json');
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use GET.']);
    exit;
}

$result = $conn->query("SELECT lrn, owner_name, location, size_m2, status FROM land_cases ORDER BY id DESC");

$cases = [];
while ($row = $result->fetch_assoc()) {
    $row['size_m2'] = (int) $row['size_m2'];
    $cases[] = $row;
}

echo json_encode($cases, JSON_PRETTY_PRINT);
?>
