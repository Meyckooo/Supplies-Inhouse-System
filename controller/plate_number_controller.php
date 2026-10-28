<?php

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to manage plate numbers.']);
    exit;
}

$base_path = '../';
require_once __DIR__ . '/../includes/auth_check.php';

if (!hasAccess(2, $isSuperAdmin, $allowedModules)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'You do not have permission to view plate numbers.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Your session has expired. Refresh the page and try again.']);
        exit;
    }

    $rawPlateNumber = $_POST['oplateno'] ?? '';
    if (!is_string($rawPlateNumber)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Enter a valid plate number.']);
        exit;
    }

    $plateNumber = strtoupper(trim($rawPlateNumber));
    if ($plateNumber === '' || strlen($plateNumber) > 10) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Enter a plate number between 1 and 10 characters.']);
        exit;
    }

    $createdBy = trim((string) ($_SESSION['user_id'] ?? ''));
    if ($createdBy === '' || strlen($createdBy) > 15) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Unable to identify the user registering this plate number.']);
        exit;
    }

    $duplicateStmt = $conn->prepare('SELECT 1 FROM tbl_ihsup_plateno WHERE oplateno = ? LIMIT 1');
    if (!$duplicateStmt) {
        error_log('Unable to prepare duplicate plate check: ' . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to register the plate number.']);
        exit;
    }

    $duplicateStmt->bind_param('s', $plateNumber);
    if (!$duplicateStmt->execute()) {
        error_log('Unable to check for duplicate plate number: ' . $duplicateStmt->error);
        $duplicateStmt->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to register the plate number.']);
        exit;
    }

    $isDuplicate = $duplicateStmt->get_result()->num_rows > 0;
    $duplicateStmt->close();

    if ($isDuplicate) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'That plate number is already registered.']);
        exit;
    }

    $insertStmt = $conn->prepare(
        'INSERT INTO tbl_ihsup_plateno (oplateno, ocreatedby, ocreatedate)
         VALUES (?, ?, NOW())'
    );

    if (!$insertStmt) {
        error_log('Unable to prepare plate number insert: ' . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to register the plate number.']);
        exit;
    }

    $insertStmt->bind_param('ss', $plateNumber, $createdBy);
    if (!$insertStmt->execute()) {
        error_log('Unable to insert plate number: ' . $insertStmt->error);
        $insertStmt->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to register the plate number.']);
        exit;
    }

    $plateId = $insertStmt->insert_id;
    $insertStmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Plate number registered successfully.',
        'data' => ['otableid' => $plateId, 'oplateno' => $plateNumber],
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$stmt = $conn->prepare(
    'SELECT otableid, oplateno
     FROM tbl_ihsup_plateno
     ORDER BY otableid ASC'
);

if (!$stmt) {
    error_log('Unable to prepare plate number query: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to load plate numbers.']);
    exit;
}

if (!$stmt->execute()) {
    error_log('Unable to fetch plate numbers: ' . $stmt->error);
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to load plate numbers.']);
    exit;
}

$result = $stmt->get_result();
$plateNumbers = [];

while ($row = $result->fetch_assoc()) {
    $plateNumbers[] = [
        'otableid' => (int) $row['otableid'],
        'oplateno' => $row['oplateno'],
    ];
}

$stmt->close();

echo json_encode(['success' => true, 'data' => $plateNumbers]);
