<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_id = $_POST['character_id'] ?? null;
    $ability_name = $_POST['ability_name'] ?? null;
    $new_value = $_POST['new_value'] ?? null;
    $group_id = $_POST['group_id'] ?? null;

    if ($character_id && $ability_name && $new_value !== null && $group_id) {
        try {
            $stmt = $pdo->prepare("
                UPDATE character_attributes
                SET {$ability_name} = ?
                WHERE character_id = ?
            ");
            $stmt->execute([$new_value, $character_id]);

            // 成功後にグループページへリダイレクト
            header('Location: group_page.php?group_id=' . urlencode($group_id));
            exit;
        } catch (Exception $e) {
            echo "エラー: " . $e->getMessage();
        }
    } else {
        echo "必要なデータが不足しています。";
    }
}
