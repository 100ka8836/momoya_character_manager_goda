<?php
require 'includes/db.php';

// POSTリクエストかつ必要なデータが送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_value'], $_POST['character_id'], $_POST['category_id'], $_POST['group_id'])) {
    $newValue = trim($_POST['new_value']);
    $characterId = intval($_POST['character_id']);
    $categoryId = intval($_POST['category_id']);
    $groupId = intval($_POST['group_id']); // グループIDを取得

    try {
        // 値を更新または挿入
        $stmt = $pdo->prepare("
            INSERT INTO charactervalues (character_id, category_id, value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE value = ?
        ");
        $stmt->execute([$characterId, $categoryId, $newValue, $newValue]);

        // 成功時にリダイレクト
        header("Location: group_page.php?group_id=$groupId&success=1");
        exit;
    } catch (PDOException $e) {
        die('値の更新中にエラーが発生しました: ' . $e->getMessage());
    }
} else {
    die('無効なリクエストです。グループIDがありません。');
}
?>