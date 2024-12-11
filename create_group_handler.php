<?php
require 'includes/db.php';

// ヘッダー設定
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$name || !$password) {
        echo json_encode(['success' => false, 'message' => 'グループ名またはパスワードが未入力です。']);
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        echo json_encode(['success' => false, 'message' => 'パスワードは英数字のみを使用してください。']);
        exit();
    }

    try {
        // パスワードをハッシュ化して保存
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // データベースにグループを挿入
        $stmt = $pdo->prepare("INSERT INTO `groups` (name, password) VALUES (?, ?)");
        $stmt->execute([$name, $hashed_password]);

        echo json_encode(['success' => true, 'message' => 'グループが作成されました！']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => '無効なリクエストです。']);
}
