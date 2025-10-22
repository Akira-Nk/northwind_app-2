<?php
require 'connDB.php';
if (!isset($_GET['order'])) { header('Location: sales_list.php'); exit; }
$order = intval($_GET['order']);
$sql = "SELECT od.i_ProductID, od.i_Quantity, p.c_ProductName, p.i_Price, (od.i_Quantity * p.i_Price) AS total
FROM tb_orderdetails od
JOIN tb_products p ON od.i_ProductID = p.i_ProductID
WHERE od.i_OrderID = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sum_qty = 0; $sum_price = 0;
foreach($rows as $r){ $sum_qty += $r['i_Quantity']; $sum_price += $r['total']; }
?>
<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><title>รายละเอียดการขาย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="bw.css" rel="stylesheet"></head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid"><a class="navbar-brand" href="#">Northwind Sales</a></div>
</nav>
<div class="container">
  <h3>รายละเอียดการขาย #<?php echo $order; ?></h3>
  <table class="table table-bordered bg-white">
    <thead><tr><th>ชื่อสินค้า</th><th>ราคา (ต่อหน่วย)</th><th>จำนวน</th><th>ราคารวม</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['c_ProductName']); ?></td>
        <td><?php echo number_format($r['i_Price'],2); ?></td>
        <td><?php echo $r['i_Quantity']; ?></td>
        <td><?php echo number_format($r['total'],2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="table-secondary">
        <th>รวม</th><th></th><th><?php echo $sum_qty; ?></th><th><?php echo number_format($sum_price,2); ?></th>
      </tr>
    </tfoot>
  </table>
  <a href="sales_list.php" class="btn btn-secondary">กลับ</a>
</div>
</body>
</html>
