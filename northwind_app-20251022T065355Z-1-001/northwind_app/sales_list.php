<?php
require 'connDB.php';
// Summary: orders with totals
$sql = "SELECT o.i_OrderID, o.c_OrderDate, o.i_EmployeeID, o.i_CustomerID,
 SUM(od.i_Quantity) AS total_qty,
 SUM(od.i_Quantity * p.i_Price) AS total_price
FROM tb_orders o
JOIN tb_orderdetails od ON o.i_OrderID = od.i_OrderID
JOIN tb_products p ON od.i_ProductID = p.i_ProductID
GROUP BY o.i_OrderID
ORDER BY o.i_OrderID DESC";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// helper lookups
$empStmt = $pdo->query('SELECT i_EmployeeID, c_FirstName, c_LastName FROM tb_employees')->fetchAll(PDO::FETCH_ASSOC);
$emp = [];
foreach($empStmt as $e) $emp[$e['i_EmployeeID']] = $e['c_FirstName'].' '.$e['c_LastName'];
$cusStmt = $pdo->query('SELECT i_customerid, c_customername FROM tb_customers')->fetchAll(PDO::FETCH_ASSOC);
$cus = [];
foreach($cusStmt as $c) $cus[$c['i_customerid']] = $c['c_customername'];
?>
<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><title>ตรวจสอบยอดขาย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="bw.css" rel="stylesheet"></head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid"><a class="navbar-brand" href="#">Northwind Sales</a>
  <div class="collapse navbar-collapse"><ul class="navbar-nav me-auto"><li class="nav-item"><a class="nav-link" href="index.php">ขายสินค้า</a></li><li class="nav-item"><a class="nav-link active" href="sales_list.php">ตรวจสอบยอดขาย</a></li></ul></div></div>
</nav>
<div class="container">
  <h3>ตารางสรุปรายการขาย</h3>
  <?php if(isset($_GET['msg'])): ?>
    <?php if($_GET['msg']==='created'): ?>
      <div class="alert alert-success">บันทึกสำเร็จ</div>
    <?php elseif($_GET['msg']==='updated'): ?>
      <div class="alert alert-success">อัปเดตเรียบร้อย</div>
    <?php elseif($_GET['msg']==='deleted'): ?>
      <div class="alert alert-success">ลบเรียบร้อย</div>
    <?php endif; ?>
  <?php endif; ?>
  <table class="table table-bordered bg-white">
    <thead><tr><th>เลขที่การขาย</th><th>วันที่</th><th>พนักงาน</th><th>ลูกค้า</th><th>จำนวนสินค้าทั้งหมด</th><th>ราคารวม</th><th>รายละเอียด</th></tr></thead>
    <tbody>
      <?php
      $sum_qty=0; $sum_price=0;
      foreach($rows as $r): 
        $sum_qty += $r['total_qty'];
        $sum_price += $r['total_price'];
      ?>
      <tr>
        <td><?php echo $r['i_OrderID']; ?></td>
        <td><?php echo $r['c_OrderDate']; ?></td>
        <td><?php echo $emp[$r['i_EmployeeID']] ?? '-'; ?></td>
        <td><?php echo $cus[$r['i_CustomerID']] ?? '-'; ?></td>
        <td><?php echo $r['total_qty']; ?></td>
        <td><?php echo number_format($r['total_price'],2); ?></td>
        <td>
          <a class="btn btn-sm btn-info" href="sales_detail.php?order=<?php echo $r['i_OrderID']; ?>">รายละเอียด</a>
          <a class="btn btn-sm btn-secondary" href="edit_order.php?order=<?php echo $r['i_OrderID']; ?>">แก้ไข</a>
          <form method="post" action="delete_order.php" class="d-inline delete-form" style="display:inline">
            <input type="hidden" name="i_OrderID" value="<?php echo $r['i_OrderID']; ?>">
            <button type="submit" class="btn btn-sm btn-danger">ลบ</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="table-secondary">
        <th colspan="4">รวมทั้งหมด</th>
        <th><?php echo $sum_qty; ?></th>
        <th><?php echo number_format($sum_price,2); ?></th>
        <th></th>
      </tr>
    </tfoot>
  </table>
</div>
<script>
  // Confirm delete
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.delete-form').forEach(function(f){
      f.addEventListener('submit', function(e){
        var id = f.querySelector('input[name="i_OrderID"]').value;
        if (!confirm('ยืนยันลบรายการขาย #' + id + ' ?')) e.preventDefault();
      });
    });
  });
</script>
</body>
</html>
