<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_id = $_POST['character_id'] ?? null;
    $color_code = $_POST['color_code'] ?? null;

    if ($character_id && $color_code && preg_match('/^#[0-9A-Fa-f]{6}$/', $color_code)) {
        try {
            $stmt = $pdo->prepare("UPDATE characters SET color_code = ? WHERE id = ?");
            $stmt->execute([$color_code, $character_id]);
            echo "success";
        } catch (Exception $e) {
            echo "データベースエラー: " . $e->getMessage();
        }
    } else {
        echo "入力エラー: 無効なデータ";
    }
}
