<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_id = $_POST['character_id'] ?? null;
    $field_name = $_POST['field_name'] ?? null;
    $new_value = $_POST['new_value'] ?? null;
    $group_id = $_POST['group_id'] ?? null;

    if ($character_id && $field_name && $new_value !== null && $group_id) {
        $stmt = $pdo->prepare("
            UPDATE characters
            SET $field_name = ?
            WHERE id = ?
        ");
        $stmt->execute([$new_value, $character_id]);

        // リダイレクト時に group_id を正しく渡す
        header('Location: group_page.php?group_id=' . urlencode($group_id));
        exit;
    } else {
        echo "必要なデータが不足しています。";
    }
}
?>