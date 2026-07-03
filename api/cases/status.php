<?php

header('Content-Type: application/json');
require '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use PATCH.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing JSON body.']);
    exit;
}

$lrn = trim($input['lrn'] ?? '');
$status = trim($input['status'] ?? '');

$allowedStatuses = ['pending', 'surveyed', 'approved'];

if ($lrn === '' || !in_array($status, $allowedStatuses, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => "lrn and a valid status ('pending', 'surveyed', or 'approved') are required."]);
    exit;
}

$stmt = $conn->prepare("UPDATE land_cases SET status = ? WHERE lrn = ?");
$stmt->bind_param('ss', $status, $lrn);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Status updated']);
} else {
    // Check if the case exists at all vs. just no change (status already the same)
    $check = $conn->prepare("SELECT id FROM land_cases WHERE lrn = ?");
    $check->bind_param('s', $lrn);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Case not found.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Status updated']);
    }
    $check->close();
}
$stmt->close();
?>
