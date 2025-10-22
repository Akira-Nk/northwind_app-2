<?php
require 'connDB.php';

// If POST -> perform update
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        $i_OrderID = intval($_POST['i_OrderID']);
        $i_CustomerID = intval($_POST['i_CustomerID']);
        $i_EmployeeID = intval($_POST['i_EmployeeID']);
    // c_OrderDate must not be null. Use posted value when present, otherwise fallback to today.
    $c_OrderDate = isset($_POST['c_OrderDate']) ? trim($_POST['c_OrderDate']) : '';
    if ($c_OrderDate === '') {
      $c_OrderDate = date('Y-m-d');
    }
        $i_ShipperID = intval($_POST['i_ShipperID']);

        // update order
        $upd = $pdo->prepare('UPDATE tb_orders SET i_CustomerID = ?, i_EmployeeID = ?, c_OrderDate = ?, i_ShipperID = ? WHERE i_OrderID = ?');
        $upd->execute([$i_CustomerID, $i_EmployeeID, $c_OrderDate, $i_ShipperID, $i_OrderID]);

        // remove existing details
        $del = $pdo->prepare('DELETE FROM tb_orderdetails WHERE i_OrderID = ?');
        $del->execute([$i_OrderID]);

        // insert new details
        $prod_ids = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        $row = $pdo->query('SELECT MAX(i_OrderDetailID) AS mx FROM tb_orderdetails')->fetch(PDO::FETCH_ASSOC);
        $nextDetail = $row && $row['mx'] ? intval($row['mx']) + 1 : 1;
        $ins = $pdo->prepare('INSERT INTO tb_orderdetails (i_OrderDetailID, i_OrderID, i_ProductID, i_Quantity) VALUES (?, ?, ?, ?)');
        for ($i=0;$i<count($prod_ids);$i++){
            $pid = intval($prod_ids[$i]);
            $qty = intval($quantities[$i]);
            if ($pid>0 && $qty>0) {
                $ins->execute([$nextDetail, $i_OrderID, $pid, $qty]);
                $nextDetail++;
            }
        }

    $pdo->commit();
  // After saving, return to the sales summary list
  header('Location: sales_list.php?msg=updated'); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Update failed: ' . htmlspecialchars($e->getMessage());
        exit;
    }
}

// GET -> show form
if (!isset($_GET['order'])) { header('Location: sales_list.php'); exit; }
$order = intval($_GET['order']);

$employees = $pdo->query('SELECT i_EmployeeID, c_FirstName, c_LastName FROM tb_employees')->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query('SELECT i_customerid, c_customername FROM tb_customers')->fetchAll(PDO::FETCH_ASSOC);
$shippers = $pdo->query('SELECT i_SupplierID, c_SupplierName FROM tb_suppliers')->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query('SELECT i_ProductID, c_ProductName, i_Price FROM tb_products')->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT * FROM tb_orders WHERE i_OrderID = ?');
$stmt->execute([$order]);
$orderRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$orderRow) { header('Location: sales_list.php'); exit; }

$dstmt = $pdo->prepare('SELECT i_ProductID, i_Quantity FROM tb_orderdetails WHERE i_OrderID = ? ORDER BY i_OrderDetailID');
$dstmt->execute([$order]);
$details = $dstmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>แก้ไขคำสั่งขาย #<?php echo $order; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="bw.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid"><a class="navbar-brand" href="#">Northwind Sales</a></div>
</nav>

<div class="container">
  <div class="card p-4 mb-4">
    <h4>แก้ไขคำสั่งขาย #<?php echo $order; ?></h4>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
      <div class="alert alert-success mt-3">อัปเดตเรียบร้อย</div>
    <?php endif; ?>
    <form method="post" action="edit_order.php">
      <input type="hidden" name="i_OrderID" value="<?php echo $order; ?>">
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">เลขที่การขาย (Auto)</label>
          <input class="form-control" type="text" value="<?php echo $order; ?>" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">วันที่ขาย</label>
          <input type="date" name="c_OrderDate" class="form-control" value="<?php echo htmlspecialchars(isset($orderRow['c_OrderDate']) && $orderRow['c_OrderDate'] ? date('Y-m-d', strtotime($orderRow['c_OrderDate'])) : date('Y-m-d')); ?>">
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">พนักงานขาย</label>
          <select name="i_EmployeeID" class="form-select" required>
            <option value="">--เลือก--</option>
            <?php foreach($employees as $e): $id = $e['i_EmployeeID']; ?>
              <option value="<?php echo $id; ?>" <?php if($id == $orderRow['i_EmployeeID']) echo 'selected'; ?>><?php echo htmlspecialchars($e['c_FirstName'].' '.$e['c_LastName']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">ลูกค้า</label>
          <select name="i_CustomerID" class="form-select" required>
            <option value="">--เลือก--</option>
            <?php foreach($customers as $c): $id = $c['i_customerid']; ?>
              <option value="<?php echo $id; ?>" <?php if($id == $orderRow['i_CustomerID']) echo 'selected'; ?>><?php echo htmlspecialchars($c['c_customername']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">บริษัทขนส่ง</label>
          <select name="i_ShipperID" class="form-select" required>
            <option value="">--เลือก--</option>
            <?php foreach($shippers as $s): $id = $s['i_SupplierID']; ?>
              <option value="<?php echo $id; ?>" <?php if($id == $orderRow['i_ShipperID']) echo 'selected'; ?>><?php echo htmlspecialchars($s['c_SupplierName']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <h5>รายการสินค้า</h5>
      <div id="items">
        <?php foreach($details as $d): ?>
          <div class="row mb-2 item-row align-items-center">
            <div class="col-md-9">
              <select name="product_id[]" class="form-select">
                <option value="">--เลือกรายการ--</option>
                <?php foreach($products as $p): ?>
                  <option value="<?php echo $p['i_ProductID']; ?>" <?php if($p['i_ProductID']==$d['i_ProductID']) echo 'selected'; ?>><?php echo htmlspecialchars($p['c_ProductName']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <input type="number" min="0" name="quantity[]" class="form-control" value="<?php echo htmlspecialchars($d['i_Quantity']); ?>">
            </div>
            <div class="col-md-1 text-end">
              <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mb-3">
        <button type="button" id="addItem" class="btn btn-outline-primary btn-sm">+ เพิ่มรายการ</button>
      </div>

      <!-- template -->
      <div id="template" class="d-none">
        <div class="row mb-2 item-row align-items-center">
          <div class="col-md-9">
            <select name="product_id[]" class="form-select">
              <option value="">--เลือกรายการ--</option>
              <?php foreach($products as $p): ?>
                <option value="<?php echo $p['i_ProductID']; ?>"><?php echo htmlspecialchars($p['c_ProductName']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <input type="number" min="0" name="quantity[]" class="form-control" value="0">
          </div>
          <div class="col-md-1 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <a href="sales_list.php" class="btn btn-secondary me-2">ยกเลิก</a>
        <button class="btn btn-primary">บันทึกการแก้ไข</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    const addBtn = document.getElementById('addItem');
    const container = document.getElementById('items');
    const template = document.getElementById('template').firstElementChild.cloneNode(true);

    function bindRemove(btn){
      btn.addEventListener('click', function(){
        const row = btn.closest('.item-row');
        if (row) row.remove();
      });
    }

    // bind existing remove buttons
    document.querySelectorAll('.remove-row').forEach(bindRemove);

    addBtn.addEventListener('click', function(){
      const node = template.cloneNode(true);
      // ensure inputs are clean
      node.querySelectorAll('select').forEach(s=>s.selectedIndex=0);
      node.querySelectorAll('input').forEach(i=>i.value='0');
      node.querySelectorAll('.remove-row').forEach(bindRemove);
      container.appendChild(node);
      // scroll into view
      node.scrollIntoView({behavior:'smooth', block:'center'});
    });

    // ensure at least one row exists
    if (!container.querySelector('.item-row')) addBtn.click();
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
