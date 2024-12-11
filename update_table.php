<?php
require 'includes/db.php'; // データベース接続ファイルを読み込み

// リクエストの検証
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['data']) || !is_array($data['data'])) {
    echo json_encode(["success" => false, "message" => "無効なデータ形式"]);
    exit;
}

try {
    // トランザクションを開始
    $pdo->beginTransaction();

    foreach ($data['data'] as $row) {
        $itemId = $row['item_id'];
        $values = $row['values'];

        // 必要に応じて列のマッピングを変更
        $query = "
            UPDATE table_name
            SET col1 = ?, col2 = ?, col3 = ?
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([...$values, $itemId]);
    }

    // トランザクションをコミット
    $pdo->commit();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    // エラー時にロールバック
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
