<?php

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to manage approvers.']);
    exit;
}

$base_path = '../';
require_once __DIR__ . '/../includes/auth_check.php';

/** @var mysqli $conn */
if (!hasAccess(2, $isSuperAdmin, $allowedModules)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'You do not have permission to manage approvers.']);
    exit;
}

function approverError(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function parseApproverId($value): ?int
{
    if (!is_string($value) && !is_int($value)) {
        return null;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2147483647],
    ]);

    return $id === false ? null : $id;
}

function getApproverOptions(mysqli $conn): array
{
    $users = [];
    $userResult = $conn->query(
        "SELECT oUserid, oFullname
         FROM tbl_user
         WHERE oActive = 1
         ORDER BY oFullname ASC"
    );
    if (!$userResult) {
        error_log('Unable to fetch approver users: ' . $conn->error);
        approverError(500, 'Unable to load available approvers.');
    }

    while ($row = $userResult->fetch_assoc()) {
        $userId = parseApproverId($row['oUserid']);
        if ($userId !== null) {
            $users[] = [
                'oUserid' => $userId,
                'oFullname' => $row['oFullname'],
            ];
        }
    }

    $departments = [];
    $departmentResult = $conn->query(
        "SELECT otableid, odepartment_name
         FROM tbl_ihsup_department
         WHERE odepartment_name IS NOT NULL
           AND odepartment_name <> ''
         ORDER BY odepartment_name ASC"
    );
    if (!$departmentResult) {
        error_log('Unable to fetch approver departments: ' . $conn->error);
        approverError(500, 'Unable to load departments.');
    }

    while ($row = $departmentResult->fetch_assoc()) {
        $departments[] = [
            'otableid' => (int) $row['otableid'],
            'odepartment_name' => $row['odepartment_name'],
        ];
    }

    $locations = [];
    $locationResult = $conn->query(
        'SELECT otableid FROM tbl_supplies_location ORDER BY otableid ASC'
    );
    if (!$locationResult) {
        error_log('Unable to fetch approver locations: ' . $conn->error);
        approverError(500, 'Unable to load locations.');
    }

    while ($row = $locationResult->fetch_assoc()) {
        $locations[] = (int) $row['otableid'];
    }

    return [
        'users' => $users,
        'departments' => $departments,
        'locations' => $locations,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        approverError(403, 'Your session has expired. Refresh the page and try again.');
    }

    $approverId = parseApproverId($_POST['approver_id'] ?? null);
    $departmentId = parseApproverId($_POST['department_id'] ?? null);
    $locationId = parseApproverId($_POST['location_id'] ?? null);

    if ($approverId === null || $departmentId === null || $locationId === null) {
        approverError(422, 'Select a valid approver, department, and location.');
    }

    $userStmt = $conn->prepare(
        'SELECT oFullname FROM tbl_user WHERE oUserid = ? AND oActive = 1 LIMIT 1'
    );
    if (!$userStmt) {
        error_log('Unable to prepare approver user lookup: ' . $conn->error);
        approverError(500, 'Unable to register the approver.');
    }
    $userStmt->bind_param('i', $approverId);
    if (!$userStmt->execute()) {
        error_log('Unable to look up selected approver: ' . $userStmt->error);
        $userStmt->close();
        approverError(500, 'Unable to register the approver.');
    }
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    if (!$user) {
        approverError(422, 'The selected approver is unavailable. Refresh and choose another user.');
    }
    $approverName = is_string($user['oFullname']) ? trim($user['oFullname']) : '';
    if ($approverName === '' || strlen($approverName) > 30) {
        approverError(422, 'The selected approver name must be between 1 and 30 characters.');
    }

    $departmentStmt = $conn->prepare(
        'SELECT 1 FROM tbl_ihsup_department WHERE otableid = ? LIMIT 1'
    );
    if (!$departmentStmt) {
        error_log('Unable to prepare approver department lookup: ' . $conn->error);
        approverError(500, 'Unable to register the approver.');
    }
    $departmentStmt->bind_param('i', $departmentId);
    if (!$departmentStmt->execute()) {
        error_log('Unable to look up selected department: ' . $departmentStmt->error);
        $departmentStmt->close();
        approverError(500, 'Unable to register the approver.');
    }
    $departmentExists = $departmentStmt->get_result()->num_rows > 0;
    $departmentStmt->close();
    if (!$departmentExists) {
        approverError(422, 'The selected department is unavailable. Refresh and choose another department.');
    }

    $locationStmt = $conn->prepare(
        'SELECT 1 FROM tbl_supplies_location WHERE otableid = ? LIMIT 1'
    );
    if (!$locationStmt) {
        error_log('Unable to prepare approver location lookup: ' . $conn->error);
        approverError(500, 'Unable to register the approver.');
    }
    $locationStmt->bind_param('i', $locationId);
    if (!$locationStmt->execute()) {
        error_log('Unable to look up selected location: ' . $locationStmt->error);
        $locationStmt->close();
        approverError(500, 'Unable to register the approver.');
    }
    $locationExists = $locationStmt->get_result()->num_rows > 0;
    $locationStmt->close();
    if (!$locationExists) {
        approverError(422, 'The selected location is unavailable. Refresh and choose another location.');
    }

    $duplicateStmt = $conn->prepare(
        'SELECT 1
         FROM tbl_ihsup_approver
         WHERE oapprover_id = ?
           AND olocation_id = ?
           AND odepartment_id = ?
         LIMIT 1'
    );
    if (!$duplicateStmt) {
        error_log('Unable to prepare approver duplicate check: ' . $conn->error);
        approverError(500, 'Unable to register the approver.');
    }
    $duplicateStmt->bind_param('iii', $approverId, $locationId, $departmentId);
    if (!$duplicateStmt->execute()) {
        error_log('Unable to check for duplicate approver: ' . $duplicateStmt->error);
        $duplicateStmt->close();
        approverError(500, 'Unable to register the approver.');
    }
    $isDuplicate = $duplicateStmt->get_result()->num_rows > 0;
    $duplicateStmt->close();

    if ($isDuplicate) {
        approverError(409, 'That approver is already registered for this department and location.');
    }

    $insertStmt = $conn->prepare(
        'INSERT INTO tbl_ihsup_approver
            (oapprover_name, oapprover_id, olocation_id, odepartment_id)
         VALUES (?, ?, ?, ?)'
    );
    if (!$insertStmt) {
        error_log('Unable to prepare approver insert: ' . $conn->error);
        approverError(500, 'Unable to register the approver.');
    }
    $insertStmt->bind_param('siii', $approverName, $approverId, $locationId, $departmentId);
    if (!$insertStmt->execute()) {
        error_log('Unable to insert approver: ' . $insertStmt->error);
        $insertStmt->close();
        approverError(500, 'Unable to register the approver.');
    }
    $insertStmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Approver registered successfully.',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

if (($_GET['action'] ?? '') === 'options') {
    echo json_encode(['success' => true, 'data' => getApproverOptions($conn)]);
    exit;
}

$stmt = $conn->prepare(
    'SELECT a.otableid, a.oapprover_name, a.oapprover_id, a.olocation_id,
            a.odepartment_id, d.odepartment_name
     FROM tbl_ihsup_approver AS a
     LEFT JOIN tbl_ihsup_department AS d ON d.otableid = a.odepartment_id
     ORDER BY a.otableid ASC'
);
if (!$stmt) {
    error_log('Unable to prepare approver list query: ' . $conn->error);
    approverError(500, 'Unable to load registered approvers.');
}
if (!$stmt->execute()) {
    error_log('Unable to fetch approvers: ' . $stmt->error);
    $stmt->close();
    approverError(500, 'Unable to load registered approvers.');
}

$result = $stmt->get_result();
$approvers = [];
while ($row = $result->fetch_assoc()) {
    $approvers[] = [
        'otableid' => (int) $row['otableid'],
        'oapprover_name' => $row['oapprover_name'],
        'oapprover_id' => (int) $row['oapprover_id'],
        'olocation_id' => (int) $row['olocation_id'],
        'odepartment_name' => $row['odepartment_name'] ?? 'Unassigned',
    ];
}
$stmt->close();

echo json_encode(['success' => true, 'data' => $approvers]);
