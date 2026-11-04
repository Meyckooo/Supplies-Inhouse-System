<?php
require_once __DIR__ . '/../entity/inhouse_model.php';

class InhouseController
{
    private mysqli $dbConn;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
    }

    public function getCompanyLocation(): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getCompanyLocation();
    }

    public function addInhouseRequest(array $formData, array $fileData = []): bool
    {
        $inhouseModel = new InhouseModel($this->dbConn);

        // Handle file upload if present
        $attachmentName = null;
        if (!empty($fileData['attachment']['name']) && $fileData['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($fileData['attachment']['name']);
            $uploadFile = $uploadDir . $fileName;
            if (move_uploaded_file($fileData['attachment']['tmp_name'], $uploadFile)) {
                $attachmentName = $fileName;
            }
        }

        $this->dbConn->begin_transaction();

        try {
            //status based on button clicked: 1 = to approved, 2 = save
            $actionType = $formData['action'] ?? 'save';
            $statusValue = ($actionType === 'post') ? 1 : 2;

            // Explicitly set Philippine Timezone
            $phTz = new DateTimeZone('Asia/Manila');
            $nowPh = new DateTime('now', $phTz);

            $rawPostDate = $formData['postDate'] ?? $nowPh->format('Y-m-d');
            if (strlen($rawPostDate) === 10) {
                $postDateValue = $rawPostDate . ' ' . $nowPh->format('H:i:s');
            } else {
                $postDateValue = $rawPostDate;
            }

            $headerData = [
                'department' => $formData['department'] ?? null,
                'approver' => $formData['approver'] ?? null,
                'approverId' => isset($formData['approverId']) ? (int)$formData['approverId'] : null,
                'section' => $formData['section'] ?? null,
                'remarks' => $formData['remarks'] ?? '',
                'type' => $formData['type'] ?? 'INHOUSE',
                'location' => $formData['location'] ?? null,
                'purpose' => $formData['purpose'] ?? null,
                'mode' => $formData['mode'] ?? null,
                'ostatus' => $statusValue,
                'postDate' => $postDateValue,
                'postBy' => $formData['postBy'] ?? ($_SESSION['user_name'] ?? 'Admin'),
                'postById' => isset($formData['postById']) ? (int)$formData['postById'] : (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0),
                'company' => $formData['company'] ?? null,
                'stockRoomId' => 0,
                'prioLvl' => isset($formData['prioLvl']) ? (int)$formData['prioLvl'] : 2,
                'subPurpose' => $formData['sub_purpose'] ?? '',
                'plateNo' => $formData['plate_no'] ?? null,
                'odometer' => isset($formData['odometer']) ? (int)$formData['odometer'] : null,
                'attachment' => $attachmentName,
            ];

            $parentId = $inhouseModel->addInhouseRequestHeader($headerData);
            if (!$parentId) {
                throw new Exception("Failed to insert header.");
            }

            // Add item details loop
            if (!empty($formData['items']) && is_array($formData['items'])) {
                foreach ($formData['items'] as $item) {
                    $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                    $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : 0.00;

                    $detailData = [
                        'parentId' => $parentId,
                        'itemCode' => $item['item_code'] ?? null,
                        'barCode' => $item['bar_code'] ?? null,
                        'itemDesc' => $item['item_desc'] ?? null,
                        'itemNo' => isset($item['item_no']) ? (int)$item['item_no'] : null,
                        'ounit' => $item['oum'] ?? null,
                        'reqQty' => $qty,
                        'unitPrice' => $unitPrice,
                        'reqAmount' => $qty * $unitPrice,
                        'oconId' => isset($item['consign_id']) ? (int)$item['consign_id'] : null,
                        'detail_type' => 1,
                    ];

                    if (!$inhouseModel->addInhouseRequestDetails($detailData)) {
                        throw new Exception("Failed to insert detail.");
                    }
                }
            }

            $this->dbConn->commit();
            return true;
        } catch (Exception $e) {
            $this->dbConn->rollback();
            echo "<h3>Database/Transaction Error:</h3> " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function getInhouseHeader(): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getInhouseHeader();
    }

    public function getInhouseDetails(string $headerId): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getInhouseDetails($headerId);
    }

    public function getCurrentCompany(): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getCurrentCompany();
    }

    public function getDepartments(): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getDepartments();
    }

    public function getApprovers(): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getApprovers();
    }

    public function getPlateNumbers(): array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getPlateNumbers();
    }

    public function getHeaderById(string $otableId): ?array
    {
        $inhouseModel = new InhouseModel($this->dbConn);
        return $inhouseModel->getHeaderById($otableId);
    }

    public function updateRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
            $otableId = $_POST['otableid'] ?? null;

            if (!$otableId) {
                $_SESSION['error_message'] = "Invalid Requisition ID.";
                header("Location: ../views/inhouse_requisition.php");
                exit();
            }

            $headerData = [
                'approverId' => $_POST['approverId'] ?? null,
                'remarks'    => $_POST['remarks'] ?? null
            ];

            $detailsData = $_POST['items'] ?? [];

            try {
                $model = new InhouseModel($this->dbConn);
                $success = $model->updateInhouseRequest($otableId, $headerData, $detailsData);

                if ($success) {
                    $_SESSION['success_message'] = "Requisition updated successfully!";
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
            }

            header("Location: ../views/inhouse_requisition_edit.php?otableid=" . urlencode($otableId));
            exit();
        }
    }
}

// --- AJAX DETAILS ENDPOINT HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['header_id'])) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/db.php';

    $headerId = $_GET['header_id'];
    $dbConn = getDBConnection();
    $controller = new InhouseController($dbConn);

    try {
        $details = $controller->getInhouseDetails($headerId);
        echo json_encode(['success' => true, 'data' => $details]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => []]);
    }
    exit();
}

// --- FORM SUBMISSION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once __DIR__ . '/../config/db.php';

    $dbConn = getDBConnection();
    $controller = new InhouseController($dbConn);

    if ($_POST['action'] === 'update') {
        // Route to update method
        $controller->updateRequest();
    } else {
        // Route to add/save method
        $success = $controller->addInhouseRequest($_POST, $_FILES);

        if ($success) {
            header("Location: ../views/inhouse_requisition.php?status=success");
            exit();
        } else {
            echo "<script>alert('Failed to save in-house requisition. Please check logs.'); window.history.back();</script>";
            exit();
        }
    }
}
