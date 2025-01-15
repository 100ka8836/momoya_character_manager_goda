<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $characterId = $_POST['character_id'] ?? null;
        $eventAge = $_POST['event_age'] ?? null;
        $eventTitle = $_POST['event_title'] ?? null;
        $eventColor = $_POST['event_color'] ?? '#FFFFFF';

        // 必須フィールドの検証
        if (!$characterId || !$eventAge || !$eventTitle) {
            http_response_code(400);
            echo json_encode(["error" => "入力が不完全です。必要なデータが不足しています。"]);
            exit;
        }

        // 現在の年齢が送信されていることを確認
        $currentAge = $_POST['current_age'] ?? null;
        if (!$currentAge) {
            http_response_code(400);
            echo json_encode(["error" => "現在の年齢が不明です。"]);
            exit;
        }

        // 現在年と開始年を設定
        $currentYear = 1801; // 現在の年を1801年に設定
        $startReferenceYear = 1700; // 開始年を1700年に設定
        $birthYear = $currentYear - $currentAge; // 生誕年を計算
        $startYear = $birthYear + $eventAge; // イベント年を計算

        // 開始年と終了年の補正
        if ($startYear < $startReferenceYear) {
            $startYear = $startReferenceYear; // 開始年が基準より前なら補正
        }
        $endYear = $currentYear; // 終了年は常に1801年

        // データベースにイベントを保存
        $stmt = $pdo->prepare("
            INSERT INTO timeline_events (character_id, start_year, end_year, title, color)
            VALUES (:character_id, :start_year, :end_year, :title, :color)
        ");
        $stmt->execute([
            ':character_id' => $characterId,
            ':start_year' => $startYear,
            ':end_year' => $endYear,
            ':title' => $eventTitle,
            ':color' => $eventColor
        ]);

        // 成功時のレスポンス
        echo json_encode(["message" => "イベントが正常に追加されました。"]);
    } catch (PDOException $e) {
        // SQLエラーのハンドリング
        http_response_code(500);
        echo json_encode(["error" => "データベースエラー: " . $e->getMessage()]);
    } catch (Exception $e) {
        // その他のエラー
        http_response_code(500);
        echo json_encode(["error" => "エラー: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "許可されていないリクエストです。"]);
}
