<?php

class InhouseModel
{
    private mysqli $db;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

    public function getCompanyLocation(): array
    {
        $query = "SELECT * FROM tbl_supplies_location";
        $result = mysqli_query($this->db, $query);

        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }

    public function getInhouseHeader(): array
    {
        $query = "SELECT h.*, a.oapprover_name 
              FROM tbl_ihsup_header h
              LEFT JOIN tbl_ihsup_approver a ON h.oapprover_id = a.oapprover_id 
              ORDER BY h.otableid DESC
              LIMIT 50";

        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getInhouseDetails(string $headerId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_ihsup_details WHERE oparentid = ?");
        $stmt->bind_param("s", $headerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        $stmt->close();
        return $details;
    }

    public function addInhouseRequestHeader(array $data): int|false
    {
        $query = "INSERT INTO tbl_ihsup_header (
                    odepartment, oapprover, oapprover_id, osection, 
                    oremarks, otype, olocation, opurpose, omode, ostatus, 
                    opostdate, opostby, opostby_id, oapprovedate, oapprovedby, 
                    ocompany, ocancelby, ocanceldate, ostockroomid, orelease_by, 
                    orelease_date, oprio_lvl, osub_purpose, oplate_no, odometer, attachment
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $department   = $data['department'] ?? null;
            $approver     = $data['approver'] ?? null;
            $approverId   = isset($data['approverId']) ? (int)$data['approverId'] : null;
            $section      = $data['section'] ?? null;
            $remarks      = $data['remarks'] ?? null;
            $type         = $data['type'] ?? 'Inhouse';
            $location     = $data['location'] ?? null;
            $purpose      = $data['purpose'] ?? null;
            $mode         = $data['mode'] ?? null;
            $status       = isset($data['ostatus']) ? (int)$data['ostatus'] : 2;
            $postDate     = $data['postDate'] ?? null;
            $postBy       = $data['postBy'] ?? null;
            $postById     = isset($data['postById']) ? (int)$data['postById'] : 0;
            $approveDate  = $data['approveDate'] ?? null;
            $approveBy    = $data['approveBy'] ?? null;
            $company      = $data['company'] ?? null;
            $cancelBy     = $data['cancelBy'] ?? null;
            $cancelDate   = $data['cancelDate'] ?? null;
            $stockRoomId  = isset($data['stockRoomId']) ? (int)$data['stockRoomId'] : 0;
            $releaseBy    = $data['releaseBy'] ?? null;
            $releaseDate  = $data['releaseDate'] ?? null;
            $prioLvl      = isset($data['prioLvl']) ? (int)$data['prioLvl'] : 2;
            $subPurpose   = $data['subPurpose'] ?? '';
            $plateNo      = $data['plateNo'] ?? null;
            $odometer     = isset($data['odometer']) ? (int)$data['odometer'] : null;
            $attachment   = $data['attachment'] ?? null;

            $stmt->bind_param(
                "sssissssssississsssisssssis",
                $department,
                $approver,
                $approverId,
                $section,
                $remarks,
                $type,
                $location,
                $purpose,
                $mode,
                $status,
                $postDate,
                $postBy,
                $postById,
                $approveDate,
                $approveBy,
                $company,
                $cancelBy,
                $cancelDate,
                $stockRoomId,
                $releaseBy,
                $releaseDate,
                $prioLvl,
                $subPurpose,
                $plateNo,
                $odometer,
                $attachment
            );

            if ($stmt->execute()) {
                $insertId = $this->db->insert_id;
                $stmt->close();
                return $insertId;
            }

            $errorMsg = $stmt->error;
            $stmt->close();
            throw new Exception("MySQL Insert Error: " . $errorMsg);
        }

        throw new Exception("Prepare failed: " . $this->db->error);
    }

    public function addInhouseRequestDetails(array $data): bool
    {
        $query = "INSERT INTO tbl_ihsup_details (
                    oparentid, oitemcode, obarcode, oitemdesc, oitemno, 
                    ounit, oreq_qty, oapprove_qty, orelease_qty, orelease_remarks, 
                    ounitprice, oreq_amount, oapproved_amount, orelease_amount, 
                    ocon_id, osup_cust_rem, oappr_remarks, otype
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $parentId       = $data['parentId'] ?? null;
            $itemCode       = $data['itemCode'] ?? null;
            $barCode        = $data['barCode'] ?? null;
            $itemDesc       = $data['itemDesc'] ?? null;
            $itemNo         = $data['itemNo'] ?? null;
            $ounit          = $data['ounit'] ?? null;
            $reqQty         = isset($data['reqQty']) ? (int)$data['reqQty'] : 0;
            $approvedQty    = isset($data['approvedQty']) ? (int)$data['approvedQty'] : 0;
            $releasedQty    = isset($data['releasedQty']) ? (int)$data['releasedQty'] : 0;
            $releaseRemarks = $data['releaseRemarks'] ?? null;
            $unitPrice      = isset($data['unitPrice']) ? (float)$data['unitPrice'] : 0.00;
            $reqAmount      = isset($data['reqAmount']) ? (float)$data['reqAmount'] : 0.00;
            $approvedAmount = isset($data['approvedAmount']) ? (float)$data['approvedAmount'] : 0.00;
            $releasedAmount = isset($data['releasedAmount']) ? (float)$data['releasedAmount'] : 0.00;
            $oconId         = isset($data['oconId']) ? (int)$data['oconId'] : null;
            $sup_cust_rem   = $data['sup_cust_rem'] ?? null;
            $app_rem        = $data['app_rem'] ?? null;
            $type           = isset($data['type']) ? (int)$data['type'] : 1;

            $stmt->bind_param(
                "isssisiiisddddissi",
                $parentId,
                $itemCode,
                $barCode,
                $itemDesc,
                $itemNo,
                $ounit,
                $reqQty,
                $approvedQty,
                $releasedQty,
                $releaseRemarks,
                $unitPrice,
                $reqAmount,
                $approvedAmount,
                $releasedAmount,
                $oconId,
                $sup_cust_rem,
                $app_rem,
                $type
            );

            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }

            $errorMsg = $stmt->error;
            $stmt->close();
            throw new Exception("MySQL Detail Insert Error: " . $errorMsg);
        }

        throw new Exception("Prepare failed for details: " . $this->db->error);
    }

    public function getCurrentCompany(): array
    {
        $query = "SELECT * FROM tbl_ihsup_location";
        $result = mysqli_query($this->db, $query);

        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }

    public function getDepartments(): array
    {
        $query = "SELECT * FROM tbl_ihsup_department";
        $result = mysqli_query($this->db, $query);

        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }

    public function getApprovers(): array
    {
        $query = "SELECT * FROM tbl_ihsup_approver";
        $result = mysqli_query($this->db, $query);

        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }

    public function getPlateNumbers(): array
    {
        $query = "SELECT * FROM tbl_ihsup_plateno";
        $result = mysqli_query($this->db, $query);

        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }
}
