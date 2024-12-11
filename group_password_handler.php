<?php
require 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$groupId || !$password) {
        echo json_encode(['success' => false, 'message' => 'グループIDまたはパスワードが入力されていません。']);
        exit();
    }

    // グループ情報を取得
    $stmt = $pdo->prepare("SELECT password FROM `groups` WHERE id = ?");
    $stmt->execute([$groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($group && password_verify($password, $group['password'])) {
        echo json_encode(['success' => true, 'redirect' => "group_page.php?group_id=$groupId"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'パスワードが正しくありません。']);
    }
}
