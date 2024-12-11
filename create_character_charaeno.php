<?php
require 'includes/db.php';
require 'includes/header.php';

$message = ''; // 登録完了メッセージを格納

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formType = $_POST['form_type'] ?? null;
        $groupId = $_POST['group_id'] ?? null;
        $sourceUrl = $_POST['charaeno_url'] ?? null;

        // 入力バリデーション
        if (!$formType || !$groupId || !$sourceUrl) {
            throw new Exception('必要なデータが不足しています。');
        }

        if (!filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('無効なURLが入力されました。');
        }

        if (strlen($sourceUrl) > 255) {
            throw new Exception('キャラエノURLが長すぎます。');
        }

        if (!is_numeric($groupId)) {
            throw new Exception('無効なグループIDです。');
        }

        // グループIDが有効か検証
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `groups` WHERE id = ?");
        $stmt->execute([$groupId]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('無効なグループIDが指定されました。');
        }

        // データ送信をcreate_character_handler.phpに委託 (内部リクエスト)
        ob_start();
        $_POST['internal_request'] = true; // 内部リクエストであることを示すフラグ

        // create_character_handler.php を直接呼び出し
        include 'create_character_handler.php';

        // 出力内容をキャプチャ
        $response = ob_get_clean();

        // HTTPレスポンスコードを確認
        $httpCode = http_response_code() ?: 200;

        if ($httpCode !== 200) {
            throw new Exception("データ送信に失敗しました。HTTPコード: $httpCode\nレスポンス内容: $response");
        }

        // デバッグ用: レスポンスをログに記録
        error_log("デバッグレスポンス: $response");

        // JSONデコード
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($responseData['success'])) {
            throw new Exception("JSONデコードエラー: " . json_last_error_msg() . "\nレスポンス内容: $response");
        }

        if ($responseData['success']) {
            $message = '登録できました！'; // 成功時メッセージ
        } else {
            $message = $responseData['message'] ?? '登録に失敗しました。';
        }
    } catch (Exception $e) {
        error_log("エラー: " . $e->getMessage());
        error_log("送信データ: " . print_r($_POST, true)); // デバッグログ追加
        $message = 'エラーが発生しました。詳細は管理者にお問い合わせください。';
    }

}

// データベースからグループ一覧を取得
$stmt = $pdo->prepare("SELECT id, name FROM `groups`");
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>キャラエノ登録</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <main>
        <h2>キャラエノから登録</h2>
        <form method="post" action="">
            <input type="hidden" name="form_type" value="charaeno">
            <label>キャラエノURL: <input type="url" name="charaeno_url" required></label><br>
            <label>所属グループ:
                <select name="group_id" required>
                    <option value="">選択してください</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']) ?>">
                            <?= htmlspecialchars($group['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label><br>
            <button type="submit">キャラエノ登録</button>
        </form>

        <!-- メッセージの表示 -->
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </main>
</body>

</html>