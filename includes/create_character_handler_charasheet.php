<?php
require_once __DIR__ . '/db.php';

/**
 * URLからキャラクターシートIDを抽出
 *
 * @param string $url ユーザーが入力したURL
 * @return string 抽出したID
 * @throws Exception URL形式が無効な場合
 */
function fetchCharasheetId($url)
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception("無効なURLです。正しい形式で入力してください。");
    }

    if (preg_match('/vampire-blood\.net\/(\d+)/', $url, $matches)) {
        return $matches[1]; // 抽出したIDを返す
    }
    throw new Exception("URLからIDを抽出できませんでした。正しいURLを入力してください。");
}

/**
 * キャラクター保管所からJSONデータを取得
 *
 * @param string $id キャラクターシートのID
 * @return array|null デコードされたJSONデータまたはnull
 * @throws Exception データ取得に失敗した場合
 */
function fetchCharasheetData($id)
{
    $url = "https://charasheet.vampire-blood.net/{$id}.js";
    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception("キャラクターシートデータの取得に失敗しました。");
    }
    return json_decode($response, true);
}

/**
 * キャラクターとその関連データをデータベースに保存
 *
 * @param PDO $pdo PDOインスタンス
 * @param array $data キャラクターシートのデータ
 * @param array $skills ユーザー入力の技能値
 * @param array $customSkills ユーザーが追加したカスタム技能
 * @return string 処理結果メッセージ
 */
function saveCharacterToDatabase($pdo, $data, $skills, $customSkills)
{
    try {
        $pdo->beginTransaction();

        // characters テーブルへの挿入
        $stmt = $pdo->prepare("
        INSERT INTO characters 
        (name, age, occupation, birthplace, sex, description, note, source_url, group_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
        $stmt->execute([
            $data['pc_name'],
            $data['age'] ?? null,
            $data['shuzoku'] ?? null,
            $data['pc_kigen'] ?? null,
            $data['sex'] ?? null,
            $data['pc_making_memo'] ?? null,
            $data['pc_making_memo'] ?? null,
            $data['source_url'] ?? null,
            $_POST['group_id'] ?? null // 新たに追加
        ]);
        $characterId = $pdo->lastInsertId();

        // character_attributes テーブルへの挿入
        $stmt = $pdo->prepare("
            INSERT INTO character_attributes 
            (character_id, str, con, pow, dex, app, siz, int_value, edu, hp, mp, san_current, san_max, db)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $characterId, // キャラクターID
            $data['NP1'] ?? null, // 筋力
            $data['NP2'] ?? null, // 体力
            $data['NP3'] ?? null, // 精神力
            $data['NP4'] ?? null, // 敏捷性
            $data['NP5'] ?? null, // 外見
            $data['NP6'] ?? null, // サイズ
            $data['NP7'] ?? null, // 知性
            $data['NP8'] ?? null, // 教育
            $data['NP9'] ?? null, // ヒットポイント
            $data['NP10'] ?? null, // マジックポイント
            $data['SAN_Left'] ?? null, // 現在の正気度
            $data['SAN_Max'] ?? null, // 最大正気度
            $data['dmg_bonus'] ?? null // ダメージボーナス
        ]);

        // フォームから送信された技能値をすべて登録
        foreach ($skills as $skillName => $skillValue) {
            $stmt = $pdo->prepare("
                INSERT INTO character_skills (character_id, skill_name, skill_value)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$characterId, $skillName, $skillValue]);
        }

        // カスタム技能を登録
        if (!empty($customSkills)) {
            foreach ($customSkills as $customSkill) {
                $customSkillName = trim($customSkill['name']);
                $customSkillValue = $customSkill['value'];

                if (!empty($customSkillName) && is_numeric($customSkillValue)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO character_skills (character_id, skill_name, skill_value)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$characterId, $customSkillName, $customSkillValue]);
                }
            }
        }

        $pdo->commit();
        return "キャラクターの登録が完了しました！";
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("データベース登録エラー: " . $e->getMessage());
        return "登録中にエラーが発生しました: " . $e->getMessage();
    }
}

// メイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    try {
        $charasheetUrl = $_POST['charasheet_url'];
        $skills = $_POST['skills'] ?? []; // 入力された技能値
        $customSkills = $_POST['custom_skills'] ?? []; // ユーザーが追加した技能

        $charasheetId = fetchCharasheetId($charasheetUrl);
        $charasheetData = fetchCharasheetData($charasheetId);

        if ($charasheetData) {
            $message = saveCharacterToDatabase($pdo, $charasheetData, $skills, $customSkills);
        } else {
            $message = "キャラクターのデータ取得に失敗しました。";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    // メッセージをセッションに保存し、元のページへリダイレクト
    $_SESSION['message'] = $message;
    $_SESSION['message_class'] = strpos($message, 'エラー') !== false ? 'error-message' : 'success-message';


    // 現在のスキームとホストを取得
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];

    // フルURLを構築
    $redirectUrl = $protocol . $host . '/momoya_character_manager/create_character_charasheet.php?message=' . urlencode($message) . '&success=1';

    // 正しいリダイレクトURLを指定
    header('Location: ' . $redirectUrl);
    exit;


}
