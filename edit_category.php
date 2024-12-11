<?php
require 'includes/db.php';

// POSTリクエストかつ必要なデータが送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category_name'], $_POST['category_id'], $_POST['group_id'])) {
    $newCategoryName = trim($_POST['new_category_name']);
    $categoryId = intval($_POST['category_id']);
    $groupId = intval($_POST['group_id']);

    // 入力チェック
    if (empty($newCategoryName)) {
        die('カテゴリ名を入力してください。');
    }

    try {
        // データベースのカテゴリ名を更新
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND group_id = ?");
        $stmt->execute([$newCategoryName, $categoryId, $groupId]);

        // 成功時にリダイレクト
        header("Location: group_page.php?group_id=$groupId&success=1");
        exit;
    } catch (PDOException $e) {
        die('カテゴリ編集中にエラーが発生しました: ' . $e->getMessage());
    }
} else {
    die('無効なリクエストです。');
}
?>