<?php

class InhouseModel
{
    private mysqli $db;

    public function __construct(mysqli $conn)
    {
        $this->db = $conn;
    }

    public function getInhouseHeader(): array
    {
        $query = "SELECT * FROM tbl_ihsup_header";
        $result = mysqli_query($this->db, $query);

        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
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
        $query = "INSERT INTO tbl_ihsup_header (odepartment, 
                                                odate_need, 
                                                oapprover, 
                                                oaprover_id, 
                                                osection, 
                                                oremarks, 
                                                otype, 
                                                olocation, 
                                                opurpose, 
                                                omode, 
                                                ostatus, 
                                                opostdate, 
                                                opostby, 
                                                opostby_id, 
                                                oapprovedate, 
                                                oapproveby, 
                                                ocompany, 
                                                ocancelby, 
                                                ocanceldate, 
                                                ostockroomid, 
                                                orelease_by, 
                                                orelease_date, 
                                                oprio_lvl, 
                                                osub_purpose, 
                                                oplate_no, 
                                                odometer) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $department   = $data['department'] ?? null;
            $dateNeed     = $data['dateNeed'] ?? null;
            $approver     = $data['approver'] ?? null;
            $approverId   = $data['approverId'] ?? null;
            $section      = $data['section'] ?? null;
            $remarks      = $data['remarks'] ?? null;
            $type         = $data['type'] ?? null;
            $location     = $data['location'] ?? null;
            $purpose      = $data['purpose'] ?? null;
            $mode         = $data['mode'] ?? null;
            $status       = $data['status'] ?? null;
            $postDate     = $data['postDate'] ?? null;
            $postBy       = $data['postBy'] ?? null;
            $postById     = $data['postById'] ?? null;
            $approveDate  = $data['approveDate'] ?? null;
            $approveBy    = $data['approveBy'] ?? null;
            $company      = $data['company'] ?? null;
            $cancelBy     = $data['cancelBy'] ?? null;
            $cancelDate   = $data['cancelDate'] ?? null;
            $stockRoomId  = $data['stockRoomId'] ?? null;
            $releaseBy    = $data['releaseBy'] ?? null;
            $releaseDate  = $data['releaseDate'] ?? null;
            $prioLvl      = $data['prioLvl'] ?? null;
            $subPurpose   = $data['subPurpose'] ?? null;
            $plateNo      = $data['plateNo'] ?? null;
            $odometer     = $data['odometer'] ?? null;

            $stmt->bind_param(
                "sssissssssississssisssissi",
                $department,
                $dateNeed,
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
                $odometer
            );

            if ($stmt->execute()) {
                $insertId = $this->db->insert_id;
                $stmt->close();
                return $insertId;
            }

            error_log("Failed to add Header: " . $stmt->error);
            $stmt->close();
        }
        return false;
    }

    public function addInhouseRequestDetails(array $data): bool
    {
        $query = "INSERT INTO tbl_ihsup_details (oparentid, 
                                                oitemcode, 
                                                obarcodestat, 
                                                oitemdesc, 
                                                oitemno, 
                                                ounit, 
                                                oreq_qty, 
                                                oapprove_qty, 
                                                orelease_qty, 
                                                orelease_remarks, 
                                                ounitprice, 
                                                oreq_amount, 
                                                oapproved_amount, 
                                                orelease_amount, 
                                                ocon_id, 
                                                osup_cust_rem, 
                                                oappr_rem, 
                                                otype) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->db->prepare($query)) {
            $parentId       = $data['parentId'] ?? null;
            $itemCode       = $data['itemCode'] ?? null;
            $barCode        = $data['barCode'] ?? null;
            $itemDesc       = $data['itemDesc'] ?? null;
            $itemNo         = $data['itemNo'] ?? null;
            $ounit          = $data['ounit'] ?? null;
            $reqQty         = $data['reqQty'] ?? null;
            $approvedQty    = $data['approvedQty'] ?? null;
            $releasedQty    = $data['releasedQty'] ?? null;
            $releaseRemarks = $data['releaseRemarks'] ?? null;
            $unitPrice      = $data['unitPrice'] ?? null;
            $reqAmount      = $data['reqAmount'] ?? null;
            $approvedAmount = $data['approvedAmount'] ?? null;
            $releasedAmount = $data['releasedAmount'] ?? null;
            $oconId         = $data['oconId'] ?? null;
            $sup_cust_rem   = $data['sup_cust_rem'] ?? null;
            $app_rem        = $data['app_rem'] ?? null;
            $detail_type    = $data['detail_type'] ?? null;

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
                $detail_type
            );

            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } else {
            error_log("Failed to add Request Details: " . $this->db->error);
            return false;
        }
    }
}
