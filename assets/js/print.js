document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".btn_direct_print").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const otableId = this.getAttribute("data-otableid");

      fetch(`../controller/inhouse_controller.php?header_id=${otableId}`)
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            printRequisitionData(otableId, result.header, result.data);
          } else {
            alert("Failed to load requisition details for printing.");
          }
        })
        .catch((error) => console.error("Error fetching print data:", error));
    });
  });

  function printRequisitionData(otableId, header, details) {
    let iframe = document.getElementById("printIframe");
    if (!iframe) {
      iframe = document.createElement("iframe");
      iframe.id = "printIframe";
      iframe.style.position = "absolute";
      iframe.style.width = "0px";
      iframe.style.height = "0px";
      iframe.style.border = "none";
      document.body.appendChild(iframe);
    }

    let rowsHtml = "";
    let grandTotal = 0;

    details.forEach((item, index) => {
      const qty = parseInt(item.oreq_qty || 1);
      const price = parseFloat(item.ounitprice || 0);
      const total = qty * price;
      grandTotal += total;

      rowsHtml += `
                <tr>
                    <td style="text-align: center;">${index + 1}</td>
                    <td style="padding: 6px;">${item.oitemcode || ""}</td>
                    <td style="padding: 6px;">${item.oitemdesc || ""}</td>
                    <td style="text-align: center; padding: 6px;">${item.ounit || ""}</td>
                    <td style="text-align: right; padding: 6px;">${qty.toLocaleString()}</td>
                    <td style="text-align: right; padding: 6px;">${price.toFixed(2)}</td>
                    <td style="text-align: right; padding: 6px;">${total.toFixed(2)}</td>
                </tr>
            `;
    });

    if (details.length === 0) {
      rowsHtml = `<tr><td colspan="7" style="text-align: center; padding: 10px;">No items found.</td></tr>`;
    }

    const formattedId = String(otableId).padStart(3, "0");

    const hDept = header?.odepartment || "N/A";
    const hSection = header?.osection || "N/A";
    const hLocation = header?.olocation || "N/A";
    const hPurpose = header?.opurpose || "N/A";
    const hRequestedBy = header?.opostby || "N/A";
    const hPrintedBy = window.currentUsername || header?.oprintedby || header?.opostby || "ADMIN";

    // Generate current date and time format (e.g., Nov 5, 2026, 10:18 AM)
    const now = new Date();
    const currentDateTime = now.toLocaleDateString("en-US", { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    }) + ' ' + now.toLocaleTimeString("en-US", { 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
    
    const hPrintDateTime = header?.oprintdate || currentDateTime;

    const htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title></title>
                <style>
                    @page {
                        size: auto;
                        margin: 0mm;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        color: #000;
                        padding: 15mm;
                        background: #fff;
                        margin: 0;
                    }
                    h2 {
                        text-align: center;
                        margin-bottom: 5px;
                        font-size: 15px;
                    }
                    
                    /* Header tracking info grid */
                    .info-grid {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 8px 20px;
                        margin-bottom: 20px;
                        font-size: 13px;
                        border: 1px solid #ccc;
                        padding: 10px;
                        background: #f9f9f9;
                    }
                    .info-item label {
                        font-weight: bold;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 15px;
                        font-size: 13px;
                    }
                    th, td {
                        border: none;
                        padding: 8px 6px;
                        text-align: left;
                    }
                    th {
                        border-bottom: 1.5px solid #000;
                        font-weight: bold;
                    }
                    .footer-container {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-top: 15px;
                        font-size: 13px;
                    }
                    .printed-by-info {
                        color: #333;
                    }
                    .grand-total-container {
                        font-size: 15px;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <h2>IN-HOUSE & SUPPLIES REQUISITION</h2>

                <!-- Header Traceability Info Block -->
                <div class="info-grid">
                    <div class="info-item"><label>Control No:</label> ${formattedId}</div>
                    <div class="info-item"><label>Department:</label> ${hDept}</div>
                    <div class="info-item"><label>Purpose:</label> ${hPurpose}</div>
                    <div class="info-item"><label>Section:</label> ${hSection}</div>
                    <div class="info-item"><label>Requested By:</label> ${hRequestedBy}</div>
                    <div class="info-item"><label>Location:</label> ${hLocation}</div>
                </div>

                <table style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 40px;">#</th>
                            <th>Item Code</th>
                            <th>Item Description</th>
                            <th style="text-align: center;">UOM</th>
                            <th style="text-align: right;">Req Qty</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>

                <div style="border-bottom: 1.5px solid #000; margin-top: 5px; width: 100%;"></div>
                
                <div class="footer-container">
                    <div class="printed-by-info">
                        Printed by: <strong>${hPrintedBy}</strong>  |  <strong>${hPrintDateTime}</strong>
                    </div>
                    <div class="grand-total-container">
                        Total:    ₱ ${grandTotal.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </div>
                </div>
            </body>
            </html>
        `;

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(htmlContent);
    doc.close();

    setTimeout(() => {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
    }, 500);
  }
});