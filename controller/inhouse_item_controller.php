<?php

require_once __DIR__ . '/../entity/inhouse_item_model.php';
require_once __DIR__ . '/../config/db.php'; 

class InhouseItemController
{
    private ?mysqli $dbConn;

    public function __construct(?mysqli $conn = null)
    {
        $this->dbConn = $conn ?? getDBConnection();
    }

    public function getLookUpItems(string $searchTerm, string $searchType = 'all'): array
    {
        $itemModel = new InhouseItemModel($this->dbConn);
        return $itemModel->getLookUpItems($searchTerm, $searchType);
    }
}

// --- AJAX ENDPOINT HANDLER ---
if (isset($_GET['search'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json');

    $searchTerm = $_GET['search'] ?? '';
    $searchType = $_GET['type'] ?? 'all';
    $db = getDBConnection();
    
    $controller = new InhouseItemController($db);
    $items = $controller->getLookUpItems($searchTerm, $searchType);

    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
    exit();
}
?>