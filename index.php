<?php
require 'connDB.php'; // uses $pdo from connDB.php
// fetch employees, customers, suppliers, products
$employees = $pdo->query('SELECT i_EmployeeID, c_FirstName, c_LastName FROM tb_employees')->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query('SELECT i_customerid, c_customername FROM tb_customers')->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query('SELECT i_SupplierID, c_SupplierName FROM tb_suppliers')->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query('SELECT i_ProductID, c_ProductName, i_Price FROM tb_products')->fetchAll(PDO::FETCH_ASSOC);

// compute next OrderID (max + 1)
$stmt = $pdo->query('SELECT MAX(i_OrderID) AS mx FROM tb_orders');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$nextOrder = $row && $row['mx'] ? intval($row['mx']) + 1 : 10248;
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>หน้าจอการขายสินค้า</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="bw.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Northwind Sales</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">ขายสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="sales_list.php">ตรวจสอบยอดขาย</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h3>หน้าจอการขายสินค้า</h3>
  <form id="orderForm" method="post" action="process_order.php">
    <div class="row mb-2">
      <div class="col-md-3">
        <label class="form-label">เลขที่การขาย (Auto)</label>
        <input type="text" readonly class="form-control" name="i_OrderID" value="<?php echo $nextOrder; ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">วันที่ขาย</label>
        <input type="text" readonly class="form-control" name="c_OrderDate" value="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">พนักงานขาย</label>
        <select name="i_EmployeeID" class="form-select" required>
          <option value="">--เลือก--</option>
          <?php foreach($employees as $e): ?>
            <option value="<?php echo $e['i_EmployeeID']; ?>"><?php echo htmlspecialchars($e['c_FirstName'].' '.$e['c_LastName']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">ลูกค้า</label>
        <select name="i_CustomerID" class="form-select" required>
          <option value="">--เลือก--</option>
          <?php foreach($customers as $c): ?>
            <option value="<?php echo $c['i_customerid']; ?>"><?php echo htmlspecialchars($c['c_customername']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">บริษัทส่งสินค้า</label>
        <select name="i_ShipperID" class="form-select" required>
          <option value="">--เลือก--</option>
          <?php foreach($suppliers as $s): ?>
            <option value="<?php echo $s['i_SupplierID']; ?>"><?php echo htmlspecialchars($s['c_SupplierName']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <h5>รายการสินค้า (ไม่เกิน 10 รายการ)</h5>
    <table class="table table-sm table-bordered bg-white">
      <thead><tr><th>#</th><th>สินค้า</th><th>จำนวน</th></tr></thead>
      <tbody>
        <?php for($i=0;$i<10;$i++): ?>
          <tr>
            <td><?php echo $i+1; ?></td>
            <td>
              <select name="product_id[]" class="form-select">
                <option value="">--เลือกรายการ--</option>
                <?php foreach($products as $p): ?>
                  <option value="<?php echo $p['i_ProductID']; ?>"><?php echo htmlspecialchars($p['c_ProductName']); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="quantity[]" min="0" class="form-control" value="0"></td>
          </tr>
        <?php endfor; ?>
      </tbody>
    </table>

    <!-- Confirm Modal trigger -->
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">ยืนยันการสั่งซื้อ</button>
    <a href="sales_list.php" class="btn btn-secondary">ไปหน้าตรวจสอบยอดขาย</a>

    <!-- Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">ยืนยันการสั่งซื้อ</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            กรุณาตรวจสอบข้อมูลก่อนกดยืนยัน
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-primary">ยืนยัน</button>
          </div>
        </div>
      </div>
    </div>

  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
