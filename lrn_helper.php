<?php

function generateLRN($conn) {
    $year = date('Y');
    $prefix = "LRN-$year-";

    $sql = "SELECT lrn FROM land_cases WHERE lrn LIKE ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $likeParam = $prefix . '%';
    $stmt->bind_param('s', $likeParam);
    $stmt->execute();
    $result = $stmt->get_result();

    $nextNumber = 1;
    if ($row = $result->fetch_assoc()) {
        $lastLrn = $row['lrn']; 
        $parts = explode('-', $lastLrn);
        $lastNumber = (int) end($parts);
        $nextNumber = $lastNumber + 1;
    }
    $stmt->close();

    return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}
?>
