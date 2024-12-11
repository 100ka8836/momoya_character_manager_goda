<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'], $_POST['group_id'])) {
    $categoryId = intval($_POST['category_id']);
    $groupId = intval($_POST['group_id']);

    try {
        // カテゴリの削除
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND group_id = ?");
        $stmt->execute([$categoryId, $groupId]);

        // 該当カテゴリに関連するcharactervaluesも削除
        $stmt = $pdo->prepare("DELETE FROM charactervalues WHERE category_id = ?");
        $stmt->execute([$categoryId]);

        // 成功時にリダイレクト
        header("Location: group_page.php?group_id=$groupId&success=1");
        exit;
    } catch (PDOException $e) {
        die('削除中にエラーが発生しました: ' . $e->getMessage());
    }
} else {
    die('無効なリクエストです。');
}
