<?php

header('Content-Type: application/json; charset=utf-8');

function receivingRespond(int $status, array $response): void
{
    http_response_code($status);
    echo json_encode($response);
    exit;
}

function receivingDecimalToCents(string $value): ?int
{
    if (!preg_match('/\A[0-9]{1,8}(?:\.[0-9]{1,2})?\z/', $value)) {
        return null;
    }

    $parts = explode('.', $value, 2);
    $whole = (int) $parts[0];
    $fraction = isset($parts[1]) ? (int) str_pad($parts[1], 2, '0') : 0;

    return $whole * 100 + $fraction;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to view receiving records.']);
    exit;
}

$base_path = '../';
require_once __DIR__ . '/../includes/auth_check.php';

if (!hasAccess(2, $isSuperAdmin, $allowedModules)) {
    receivingRespond(403, ['success' => false, 'error' => 'You do not have permission to view receiving records.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') !== 'post') {
        receivingRespond(400, ['success' => false, 'error' => 'Invalid receiving action.']);
    }

    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        receivingRespond(403, ['success' => false, 'error' => 'Your session has expired. Refresh the page and try again.']);
    }

    if (!hasActionAccess(2, 'Post', $isSuperAdmin, (string) $userId, $conn)) {
        receivingRespond(403, ['success' => false, 'error' => 'You do not have permission to post receiving quantities.']);
    }

    $recordId = filter_var($_POST['otableid'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 4294967295],
    ]);
    $rawQuantity = $_POST['quantity'] ?? '';
    if ($recordId === false || !is_string($rawQuantity) || !preg_match('/\A[0-9]{1,8}\z/', $rawQuantity)) {
        receivingRespond(422, ['success' => false, 'error' => 'Enter a valid positive whole-number quantity.']);
    }

    $quantityCents = receivingDecimalToCents($rawQuantity . '.00');
    if ($quantityCents === null || $quantityCents <= 0) {
        receivingRespond(422, ['success' => false, 'error' => 'Enter a quantity greater than zero.']);
    }
    $quantityForSql = $rawQuantity . '.00';
    $postedBy = trim((string) $userId);
    if ($postedBy === '' || strlen($postedBy) > 20) {
        receivingRespond(422, ['success' => false, 'error' => 'Unable to identify the user posting this quantity.']);
    }

    $transactionStarted = false;
    $failureStatus = 500;
    $failureMessage = 'Unable to post the received quantity.';

    try {
        if (!$conn->begin_transaction()) {
            throw new RuntimeException('Unable to begin receiving transaction.');
        }
        $transactionStarted = true;

        $receivingStmt = $conn->prepare(
            'SELECT oitemno, oitemcode, oitemdesc, obarcode, ounit, orecv_qty, oput_qty
             FROM tbl_ihsupp_recv
             WHERE otableid = ?
             FOR UPDATE'
        );
        if (!$receivingStmt) {
            throw new RuntimeException('Unable to prepare receiving record lookup: ' . $conn->error);
        }
        $receivingStmt->bind_param('i', $recordId);
        if (!$receivingStmt->execute()) {
            $error = $receivingStmt->error;
            $receivingStmt->close();
            throw new RuntimeException('Unable to lock receiving record: ' . $error);
        }
        $receivingRecord = $receivingStmt->get_result()->fetch_assoc();
        $receivingStmt->close();
        if (!$receivingRecord) {
            $failureStatus = 404;
            $failureMessage = 'The receiving record was not found.';
            throw new RuntimeException($failureMessage);
        }

        $receivedCents = receivingDecimalToCents((string) $receivingRecord['orecv_qty']);
        $placedCents = receivingDecimalToCents((string) $receivingRecord['oput_qty']);
        if ($receivedCents === null || $placedCents === null) {
            throw new RuntimeException('Receiving record contains invalid quantity values.');
        }

        $remainingCents = max(0, $receivedCents - $placedCents);
        if ($quantityCents > $remainingCents) {
            $failureStatus = 422;
            $failureMessage = 'The entered quantity exceeds the remaining received quantity of '
                . intdiv($remainingCents, 100) . '.'
                . str_pad((string) ($remainingCents % 100), 2, '0', STR_PAD_LEFT) . '.';
            throw new RuntimeException($failureMessage);
        }
        $newPlacedCents = $placedCents + $quantityCents;

        $itemNumber = (int) $receivingRecord['oitemno'];
        $itemStmt = $conn->prepare('SELECT oitemno FROM tbl_item WHERE oitemno = ? FOR UPDATE');
        if (!$itemStmt) {
            throw new RuntimeException('Unable to prepare inventory item lookup: ' . $conn->error);
        }
        $itemStmt->bind_param('i', $itemNumber);
        if (!$itemStmt->execute()) {
            $error = $itemStmt->error;
            $itemStmt->close();
            throw new RuntimeException('Unable to lock inventory item: ' . $error);
        }
        $itemExists = $itemStmt->get_result()->num_rows > 0;
        $itemStmt->close();
        if (!$itemExists) {
            $failureStatus = 404;
            $failureMessage = 'The matching inventory item was not found.';
            throw new RuntimeException($failureMessage);
        }

        $updateReceivingStmt = $conn->prepare(
            'UPDATE tbl_ihsupp_recv
             SET oput_qty = oput_qty + ?
             WHERE otableid = ?'
        );
        if (!$updateReceivingStmt) {
            throw new RuntimeException('Unable to prepare receiving quantity update: ' . $conn->error);
        }
        $updateReceivingStmt->bind_param('si', $quantityForSql, $recordId);
        if (!$updateReceivingStmt->execute() || $updateReceivingStmt->affected_rows !== 1) {
            $error = $updateReceivingStmt->error;
            $updateReceivingStmt->close();
            throw new RuntimeException('Unable to update receiving quantity: ' . $error);
        }
        $updateReceivingStmt->close();

        $updateItemStmt = $conn->prepare(
            'UPDATE tbl_item
             SET oihsup_qty = COALESCE(oihsup_qty, 0) + ?
             WHERE oitemno = ?'
        );
        if (!$updateItemStmt) {
            throw new RuntimeException('Unable to prepare inventory quantity update: ' . $conn->error);
        }
        $updateItemStmt->bind_param('si', $quantityForSql, $itemNumber);
        if (!$updateItemStmt->execute() || $updateItemStmt->affected_rows !== 1) {
            $error = $updateItemStmt->error;
            $updateItemStmt->close();
            throw new RuntimeException('Unable to update inventory quantity: ' . $error);
        }
        $updateItemStmt->close();

        $insertType = 1;
        $itemCode = (string) $receivingRecord['oitemcode'];
        $itemDescription = (string) $receivingRecord['oitemdesc'];
        $barcode = (string) $receivingRecord['obarcode'];
        $unit = (string) $receivingRecord['ounit'];
        $stockCardStmt = $conn->prepare(
            'INSERT INTO tbl_ihsup_stockcard
                (oitemno, oitemcode, oitemdesc, obarcode, ounit, oinsert_type, opost_by, opostdate, oqty)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)'
        );
        if (!$stockCardStmt) {
            throw new RuntimeException('Unable to prepare stock card insert: ' . $conn->error);
        }
        $stockCardStmt->bind_param(
            'issssiss',
            $itemNumber,
            $itemCode,
            $itemDescription,
            $barcode,
            $unit,
            $insertType,
            $postedBy,
            $quantityForSql
        );
        if (!$stockCardStmt->execute()) {
            $error = $stockCardStmt->error;
            $stockCardStmt->close();
            throw new RuntimeException('Unable to insert receiving stock card entry: ' . $error);
        }
        $stockCardStmt->close();

        if ($newPlacedCents === $receivedCents) {
            $completeStmt = $conn->prepare(
                'UPDATE tbl_ihsupp_recv
                 SET ostatus = 2
                 WHERE otableid = ?
                   AND orecv_qty = oput_qty'
            );
            if (!$completeStmt) {
                throw new RuntimeException('Unable to prepare receiving completion update: ' . $conn->error);
            }
            $completeStmt->bind_param('i', $recordId);
            if (!$completeStmt->execute()) {
                $error = $completeStmt->error;
                $completeStmt->close();
                throw new RuntimeException('Unable to mark receiving record complete: ' . $error);
            }
            $completeStmt->close();
        }

        if (!$conn->commit()) {
            throw new RuntimeException('Unable to commit receiving transaction.');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Received quantity posted successfully.',
        ]);
        exit;
    } catch (Throwable $error) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        if ($failureStatus === 500) {
            error_log('Receiving quantity transaction failed: ' . $error->getMessage());
        }
        receivingRespond($failureStatus, ['success' => false, 'error' => $failureMessage]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$stmt = $conn->prepare(
    'SELECT otableid, oitemno, oitemdesc, oitemcode, obarcode, ounit, omonitor_by,
            orecv_qty, oput_qty, omode, orefno, ostatus, opost_by, opostdate, ocreatedate
     FROM tbl_ihsupp_recv WHERE ostatus = 0
     ORDER BY otableid ASC'
);

if (!$stmt) {
    error_log('Unable to prepare receiving records query: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to load receiving records.']);
    exit;
}

if (!$stmt->execute()) {
    error_log('Unable to fetch receiving records: ' . $stmt->error);
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to load receiving records.']);
    exit;
}

$result = $stmt->get_result();
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = [
        'otableid' => (int) $row['otableid'],
        'oitemno' => (int) $row['oitemno'],
        'oitemdesc' => $row['oitemdesc'],
        'oitemcode' => $row['oitemcode'],
        'obarcode' => $row['obarcode'],
        'ounit' => $row['ounit'],
        'omonitor_by' => $row['omonitor_by'],
        'orecv_qty' => $row['orecv_qty'],
        'oput_qty' => $row['oput_qty'],
        'omode' => $row['omode'],
        'orefno' => (int) $row['orefno'],
        'ostatus' => (int) $row['ostatus'],
        'opost_by' => $row['opost_by'],
        'opostdate' => $row['opostdate'],
        'ocreatedate' => $row['ocreatedate'],
    ];
}

$stmt->close();

echo json_encode(['success' => true, 'data' => $records]);
