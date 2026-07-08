<?php

header('Content-Type: application/json');
require '../../config.php';
require '../../lrn_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing JSON body.']);
    exit;
}

$owner_name = trim($input['owner_name'] ?? '');
$location = trim($input['location'] ?? '');
$size_m2 = $input['size_m2'] ?? null;

if ($owner_name === '' || $location === '' || $size_m2 === null) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'owner_name, location, and size_m2 are required.']);
    exit;
}

$lrn = generateLRN($conn);

$stmt = $conn->prepare("INSERT INTO land_cases (lrn, owner_name, location, size_m2, status) VALUES (?, ?, ?, ?, 'pending')");
$size_m2_int = (int) $size_m2;
$stmt->bind_param('sssi', $lrn, $owner_name, $location, $size_m2_int);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'lrn' => $lrn]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create land case.']);
}
$stmt->close();
?>
