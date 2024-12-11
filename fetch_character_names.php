<?php
require 'includes/db.php';

$groupId = $_GET['group_id'] ?? null;

if (!$groupId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT item_name FROM other_items WHERE group_id = ?");
$stmt->execute([$groupId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
