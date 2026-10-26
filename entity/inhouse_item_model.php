<?php
class InhouseItemModel {
    private mysqli $db;

    public ?int $itemNo;
    public ?string $itemCode;
    public ?string $itemBarcode;
    public ?string $itemDescription;
    public ?string $unit;
    public ?float $price;
    public ?float $wholeSalePrice;
    public ?float $cost;
    public ?string $itemType;
    public ?string $itemClass;
    public ?int $itemClassNo;
    public ?string $brand;
    public ?int $brandNo;
    public ?string $department;
    public ?int $departmentNo;
    public ?int $consignId;
    public ?float $store;
    public ?float $storePndg;
    public ?float $display;
    public ?float $displayPndg;
    public ?float $snrc;
    public ?float $snrcPndg;
    public ?string $monitor_by;
    public ?string $asaButang;
    public ?string $secondLoc;
    public ?string $whLocator;
    public ?string $priority2;
    public ?string $itemStatus;
    public ?int $active;
    public ?int $notDebit;
    public ?int $notCredit;
    public ?int $cashOnly;
    public ?int $priceVary;
    public ?int $mark;
    public ?int $promo;
    public ?string $rack;
    public ?int $yearEnd_diy_actual;
    public ?int $yearEnd_bodega_actual;
    public ?int $yearEnd_diy_damage;
    public ?int $yearEnd_bodega_damage;
    public ?int $yearEnd_total_actual;
    public ?int $yearEnd_total_damage;
    public ?string $mglLocator;
    public ?string $itemGroup;
    public ?string $lastRecon;
    public ?int $lastRecon_readFlag;
    public ?string $purchType;
    public ?string $incoming;
    public ?int $oicc_psMark;
    public ?float $owmsTotal;
    public ?int $displayCritical;
    public ?int $criticalPercentage;
    public ?float $diyBalance; 

    public function __construct(mysqli $conn) {
        $this->db = $conn;
    }

    public function getLookUpItems(string $searchTerm): array {
        $query = "SELECT * FROM tbl_item 
                  WHERE oitemcode LIKE ? 
                     OR obarcode LIKE ? 
                     OR oitemdesc LIKE ?
                     OR itemno LIKE ?
                  LIMIT 25";

        if ($stmt = $this->db->prepare($query)) {
            $searchItem = "%" . $searchTerm . "%";
            $stmt->bind_param("ssss", $searchItem, $searchItem, $searchItem, $searchItem);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            $stmt->close();
            return $items;
        }
        
        return [];
    }
}
?>