<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'] ?? null;
    $itemName = $_POST['item_name'] ?? null;

    if (!$groupId || !$itemName) {
        echo json_encode(['success' => false, 'message' => 'グループIDまたは項目名が指定されていません']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO other_items (group_id, item_name) VALUES (?, ?)");
    $stmt->execute([$groupId, $itemName]);

    echo json_encode(['success' => true, 'message' => '項目が追加されました']);
    exit;
}
echo json_encode(['success' => false, 'message' => '無効なリクエスト']);
