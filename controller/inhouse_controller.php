<?php
require_once __DIR__ . '/../entity/inhouse_model.php';

class InhouseController
{
    private mysqli $dbConn;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
    }

    public function addInhouseRequest(array $formData): bool
    {
        $inhouseModel = new InhouseModel($this->dbConn);

        $this->dbConn->begin_transaction();

        try {
            // Add header data mapping
            $headerData = [
                'department' => $formData['department'] ?? null,
                'dateNeed' => $formData['dateNeed'] ?? null,
                'approver' => $formData['approver'] ?? null,
                'approverId' => isset($formData['approverId']) ? (int)$formData['approverId'] : null,
                'section' => $formData['section'] ?? null,
                'remarks' => $formData['remarks'] ?? null,
                'type' => $formData['type'] ?? null,
                'location' => $formData['location'] ?? null,
                'purpose' => $formData['purpose'] ?? null,
                'mode' => $formData['mode'] ?? null,
                'status' => isset($formData['status']) ? (int)$formData['status'] : null,
                'postDate' => $formData['postDate'] ?? null,
                'postBy' => $formData['postBy'] ?? null,
                'postById' => isset($formData['postById']) ? (int)$formData['postById'] : null,
                'company' => $formData['company'] ?? null,
                'prioLvl' => isset($formData['prioLvl']) ? (int)$formData['prioLvl'] : null,
            ];

            $parentId = $inhouseModel->addInhouseRequestHeader($headerData);
            if (!$parentId) {
                throw new Exception("Failed to insert header.");
            }

            // Add details
            if (!empty($formData['details']) && is_array($formData['details'])) {
                foreach ($formData['details'] as $detail) {
                    $detailData = [
                        'parentId' => $parentId,
                        'itemCode' => $detail['item_code'] ?? null,
                        'barCode' => $detail['bar_code'] ?? null,
                        'itemDesc' => $detail['item_desc'] ?? null,
                        'itemNo' => isset($detail['item_no']) ? (int)$detail['item_no'] : null,
                        'ounit' => $detail['oum'] ?? null,
                        'reqQty' => isset($detail['qty']) ? (int)$detail['qty'] : 0,
                        'unitPrice' => isset($detail['unit_price']) ? (float)$detail['unit_price'] : 0.00,
                        'reqAmount' => isset($detail['req_amount']) ? (float)$detail['req_amount'] : 0.00,
                    ];

                    if (!$inhouseModel->addInhouseRequestDetails($detailData)) {
                        throw new Exception("Failed to insert request detail.");
                    }
                }
            }

            $this->dbConn->commit();
            return true;

        } catch (Exception $e) {
            $this->dbConn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            return false;
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
}
?>