<?php
require 'includes/db.php';

// JSONデータを受け取る
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['item_name']) || empty($data['group_id'])) {
    echo json_encode(['success' => false, 'message' => '不正なデータです']);
    exit;
}

$item_name = $data['item_name'];
$group_id = $data['group_id'];

try {
    // テーブルにデータを挿入
    $stmt = $pdo->prepare("INSERT INTO other_items (group_id, item_name) VALUES (?, ?)");
    $stmt->execute([$group_id, $item_name]);

    // 挿入されたIDを取得
    $item_id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'item_id' => $item_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'データ保存に失敗しました: ' . $e->getMessage()]);
}
