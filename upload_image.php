<?php
require __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $characterId = $_POST['character_id'] ?? null;
    $groupId = $_POST['group_id'] ?? null;
    $playerName = $_POST['player_name'] ?? null;
    $image = $_FILES['character_image'] ?? null;

    if (!$characterId || !$groupId) {
        echo "<script>alert('無効なリクエストです。'); window.location.href = 'group_page.php';</script>";
        exit;
    }

    try {
        // PL名の更新処理
        if ($playerName) {
            $stmt = $pdo->prepare("UPDATE `characters` SET `player_name` = ? WHERE `id` = ?");
            $stmt->execute([$playerName, $characterId]);
        }

        // 画像アップロード処理
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileName = uniqid() . '_' . basename($image['name']);
            $filePath = $uploadDir . $fileName;

            // 現在の画像パスを取得
            $stmt = $pdo->prepare("SELECT `image_path` FROM `characters` WHERE `id` = ?");
            $stmt->execute([$characterId]);
            $currentImagePath = $stmt->fetchColumn();

            // 古い画像の削除
            if ($currentImagePath && file_exists($currentImagePath)) {
                unlink($currentImagePath);
            }

            // ファイルの検証とアップロード
            if (in_array($image['type'], $allowedTypes)) {
                if (move_uploaded_file($image['tmp_name'], $filePath)) {
                    // データベースの更新
                    $stmt = $pdo->prepare("UPDATE `characters` SET `image_path` = ? WHERE `id` = ?");
                    $stmt->execute([$filePath, $characterId]);
                } else {
                    echo "<script>alert('画像のアップロードに失敗しました。'); window.location.href = 'group_page.php?group_id=" . htmlspecialchars($groupId) . "';</script>";
                    exit;
                }
            } else {
                echo "<script>alert('対応していないファイル形式です。'); window.location.href = 'group_page.php?group_id=" . htmlspecialchars($groupId) . "';</script>";
                exit;
            }
        }

        // 完了メッセージとリダイレクト
        echo "<script>alert('情報が更新されました。'); window.location.href = 'group_page.php?group_id=" . htmlspecialchars($groupId) . "';</script>";
    } catch (Exception $e) {
        echo "<script>alert('エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "'); window.location.href = 'group_page.php?group_id=" . htmlspecialchars($groupId) . "';</script>";
    }
}
?>