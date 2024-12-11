<?php

ob_start();

if (isset($_POST['internal_request']) && $_POST['internal_request'] === true) {
    // 内部リクエスト時の特別な処理
    error_log("内部リクエスト処理中");
    // 必要に応じてヘッダーを設定しない、または制限する
} else {
    // 外部リクエストの場合
    header('Content-Type: application/json');
}

require 'includes/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("無効なリクエストメソッドです。");
    }

    $formType = $_POST['form_type'] ?? null;
    $groupId = $_POST['group_id'] ?? null;

    // グループIDを検証
    $groupId = filter_var($groupId, FILTER_VALIDATE_INT);
    if ($groupId === false) {
        throw new Exception("無効なグループIDです。");
    }

    if ($formType === 'charaeno') {
        $sourceUrl = $_POST['charaeno_url'] ?? null;

        if (!$sourceUrl) {
            throw new Exception("キャラエノのURLが入力されていません。");
        }

        // キャラエノIDを抽出
        $characterId = extractCharaenoId($sourceUrl);

        // 6th版APIエンドポイント
        $apiUrl = "https://charaeno.com/api/v1/6th/$characterId/summary";

        // APIリクエスト
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $error = error_get_last();
            error_log("APIエラー: " . print_r($error, true)); // APIエラーをログ
            throw new Exception("キャラエノAPIからデータを取得できませんでした。");
        }

        $characterData = json_decode($response, true);
        if (!$characterData) {
            error_log("APIレスポンスが無効: " . $response); // 無効なレスポンスをログ
            throw new Exception("キャラエノAPIのデータが無効です。");
        }

        try {
            // データベース挿入処理
            insertCharacterToDatabase($pdo, $characterData, $groupId, $sourceUrl);
        } catch (Exception $e) {
            error_log("データベースエラー: " . $e->getMessage()); // データベースエラーをログ
            error_log("入力データ: " . print_r($characterData, true)); // デバッグ用データ記録
            throw $e;
        }

        echo json_encode(['success' => true, 'message' => '登録が完了しました！']);
    } else {
        throw new Exception("無効なフォームタイプです。");
    }
} catch (Exception $e) {
    error_log("エラー: " . $e->getMessage());
    error_log("スタックトレース: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    ob_end_flush();
}


function extractCharaenoId($url)
{
    if (preg_match('/https:\/\/charaeno\.com\/6th\/([^\/]+)/', $url, $matches)) {
        return $matches[1];
    }
    throw new Exception("キャラエノのURL形式が正しくありません。");
}

function insertCharacterToDatabase($pdo, $data, $groupId, $sourceUrl)
{
    $pdo->beginTransaction();

    try {
        // 空文字列をNULLに変換
        $income = (is_numeric($data['credit']['income']) && $data['credit']['income'] !== '') ? $data['credit']['income'] : null;
        $cash = (is_numeric($data['credit']['cash']) && $data['credit']['cash'] !== '') ? $data['credit']['cash'] : null;
        $deposit = (is_numeric($data['credit']['deposit']) && $data['credit']['deposit'] !== '') ? $data['credit']['deposit'] : null;

        // characters テーブル登録
        $stmt = $pdo->prepare("
            INSERT INTO `characters` 
(`name`, `occupation`, `birthplace`, `degree`, `age`, `sex`, `address`, `description`, `family`, 
 `injuries`, `scar`, `income`, `cash`, `deposit`, `personal_property`, `real_estate`, 
 `mythos_tomes`, `artifacts_and_spells`, `encounters`, `note`, `chatpalette`, `portrait_url`, `source_url`, `group_id`)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

        ");

        $stmt->execute([
            $data['name'],
            $data['occupation'] ?? null,
            $data['birthplace'] ?? null,
            $data['degree'] ?? null,
            $data['age'] ?? null,
            $data['sex'] ?? null,
            $data['personalData']['address'] ?? null,
            $data['personalData']['description'] ?? null,
            $data['personalData']['family'] ?? null,
            $data['personalData']['injuries'] ?? null,
            $data['personalData']['scar'] ?? null,
            $income,
            $cash,
            $deposit,
            $data['credit']['personalProperty'] ?? null,
            $data['credit']['realEstate'] ?? null,
            $data['mythosTomes'] ?? null,
            $data['artifactsAndSpells'] ?? null,
            $data['encounters'] ?? null,
            $data['note'] ?? null,
            $data['chatpalette'] ?? null,
            $data['portraitURL'] ?? null,
            $sourceUrl,
            $groupId,
        ]);

        $characterId = $pdo->lastInsertId();

        // デバッグ用：登録されたキャラクターIDを記録
        error_log("登録されたキャラクターID: $characterId");

        // character_attributes テーブル登録
        $stmt = $pdo->prepare("
            INSERT INTO `character_attributes` 
(`character_id`, `str`, `con`, `pow`, `dex`, `app`, `siz`, `int_value`, `edu`, `hp`, `mp`, `db`, `san_current`, `san_max`)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

        ");
        $stmt->execute([
            $characterId,
            $data['characteristics']['str'] ?? null,
            $data['characteristics']['con'] ?? null,
            $data['characteristics']['pow'] ?? null,
            $data['characteristics']['dex'] ?? null,
            $data['characteristics']['app'] ?? null,
            $data['characteristics']['siz'] ?? null,
            $data['characteristics']['int'] ?? null,
            $data['characteristics']['edu'] ?? null,
            $data['attribute']['hp'] ?? null,
            $data['attribute']['mp'] ?? null,
            $data['attribute']['db'] ?? null,
            $data['attribute']['san']['value'] ?? null,
            $data['attribute']['san']['max'] ?? null,
        ]);

        // character_skills テーブル登録
        if (!empty($data['skills'])) {
            foreach ($data['skills'] as $skill) {

                // 挿入前データをログに記録
                error_log("挿入前データ: " . print_r($skill, true));

                $stmt = $pdo->prepare("
    INSERT INTO `character_skills` (`character_id`, `skill_name`, `skill_value`, `edited`)
    VALUES (?, ?, ?, ?)
");

                $stmt->execute([
                    $characterId,
                    $skill['name'] ?? null,
                    $skill['value'] ?? null,
                    isset($skill['edited']) && is_numeric($skill['edited']) ? (int) $skill['edited'] : 0, // デフォルトで0を設定
                ]);
            }
        }

        // デバッグ用: 変換された値をログに記録
        error_log(print_r([
            'income' => $income,
            'cash' => $cash,
            'deposit' => $deposit,
        ], true));

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("エラー: " . $e->getMessage());
        error_log("入力データ: " . print_r($data, true));
        error_log("スタックトレース: " . $e->getTraceAsString());
        throw $e;
    }
}
