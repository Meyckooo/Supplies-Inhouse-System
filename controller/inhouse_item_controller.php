<?php

require_once __DIR__ . '/../entity/inhouse_item_model.php';

class InhouseItemController
{
    private mysqli $dbConn;

    public function __construct(mysqli $conn)
    {
        $this->dbConn = $conn;
    }

    public function getLookUpItems(string $searchTerm): array
    {
        $itemModel = new InhouseItemModel($this->dbConn);
        return $itemModel->getLookUpItems($searchTerm);
    }
}
?>