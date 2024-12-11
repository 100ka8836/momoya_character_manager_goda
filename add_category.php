<?php
require 'includes/db.php';

// カテゴリ名とグループIDがPOSTリクエストで送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'], $_POST['group_id'])) {
    $categoryName = trim($_POST['category_name']);
    $groupId = intval($_POST['group_id']); // グループIDを整数化

    // 入力のバリデーション
    if (empty($categoryName)) {
        die('カテゴリ名を入力してください。');
    }
    if ($groupId <= 0) {
        die('無効なグループIDです。');
    }

    try {
        // カテゴリをデータベースに追加
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, group_id) VALUES (:name, :group_id)
        ");
        $stmt->execute(['name' => htmlspecialchars($categoryName), 'group_id' => $groupId]);

        // 成功時にリダイレクト
        header('Location: group_page.php?group_id=' . $groupId . '&success=1');
        exit;
    } catch (PDOException $e) {
        die('カテゴリの追加中にエラーが発生しました: ' . $e->getMessage());
    }
} else {
    die('無効なリクエストです。');
}
?>