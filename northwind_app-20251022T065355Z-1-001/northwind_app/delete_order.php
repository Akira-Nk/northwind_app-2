<?php
require 'connDB.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: sales_list.php'); exit; }
$i_OrderID = intval($_POST['i_OrderID']);
try {
  $pdo->beginTransaction();
  // delete order details first
  $delDetails = $pdo->prepare('DELETE FROM tb_orderdetails WHERE i_OrderID = ?');
  $delDetails->execute([$i_OrderID]);
  // delete order
  $delOrder = $pdo->prepare('DELETE FROM tb_orders WHERE i_OrderID = ?');
  $delOrder->execute([$i_OrderID]);
  $pdo->commit();
  header('Location: sales_list.php?msg=deleted'); exit;
} catch (Exception $e) {
  $pdo->rollBack();
  echo 'Delete failed: ' . htmlspecialchars($e->getMessage());
}

?>
