<?php
require 'connDB.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}
$pdo->beginTransaction();
try {
    $i_OrderID = intval($_POST['i_OrderID']);
    $i_CustomerID = intval($_POST['i_CustomerID']);
    $i_EmployeeID = intval($_POST['i_EmployeeID']);
    $c_OrderDate = $_POST['c_OrderDate'];
    $i_ShipperID = intval($_POST['i_ShipperID']);
    // Insert into tb_orders
    $stmt = $pdo->prepare('INSERT INTO tb_orders (i_OrderID, i_CustomerID, i_EmployeeID, c_OrderDate, i_ShipperID) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$i_OrderID, $i_CustomerID, $i_EmployeeID, $c_OrderDate, $i_ShipperID]);

    // Prepare orderdetail insertion
    $prod_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    // find current max i_OrderDetailID
    $row = $pdo->query('SELECT MAX(i_OrderDetailID) AS mx FROM tb_orderdetails')->fetch(PDO::FETCH_ASSOC);
    $nextDetail = $row && $row['mx'] ? intval($row['mx']) + 1 : 1;

    $insertDetail = $pdo->prepare('INSERT INTO tb_orderdetails (i_OrderDetailID, i_OrderID, i_ProductID, i_Quantity) VALUES (?, ?, ?, ?)');
    for($i=0;$i<count($prod_ids);$i++){
        $pid = intval($prod_ids[$i]);
        $qty = intval($quantities[$i]);
        if ($pid>0 && $qty>0) {
            $insertDetail->execute([$nextDetail, $i_OrderID, $pid, $qty]);
            $nextDetail++;
        }
    }

    $pdo->commit();
    header('Location: sales_list.php?msg=created'); exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>