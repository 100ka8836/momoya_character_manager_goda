<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // 基本情報を登録
        $stmt = $pdo->prepare("
        INSERT INTO characters 
        (source_url, name, age, occupation, birthplace, sex, group_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
        $stmt->execute([
            $_POST['source_url'] ?? null,
            $_POST['name'],
            $_POST['age'] ?? null,
            $_POST['occupation'] ?? null,
            $_POST['birthplace'] ?? null,
            $_POST['sex'] ?? null,
            $_POST['group_id'] ?? null // 新たに追加
        ]);

        $characterId = $pdo->lastInsertId();

        // 能力値を登録
        $attributes = $_POST['attributes'] ?? [];
        $stmt = $pdo->prepare("
            INSERT INTO character_attributes (character_id, str, con, pow, dex, app, siz, int_value, edu, hp, mp, san_current, san_max, db)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $characterId,
            $attributes['str'] ?? null,
            $attributes['con'] ?? null,
            $attributes['pow'] ?? null,
            $attributes['dex'] ?? null,
            $attributes['app'] ?? null,
            $attributes['siz'] ?? null,
            $attributes['int_value'] ?? null,
            $attributes['edu'] ?? null,
            $attributes['hp'] ?? null,
            $attributes['mp'] ?? null,
            $attributes['san_current'] ?? null,
            $attributes['san_max'] ?? null,
            $attributes['db'] ?? null
        ]);

        // 技能値を登録
        $skills = $_POST['skills'] ?? [];
        foreach ($skills as $skillName => $skillValue) {
            $stmt = $pdo->prepare("
                INSERT INTO character_skills (character_id, skill_name, skill_value)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$characterId, $skillName, $skillValue]);
        }

        // 追加技能を登録
        $customSkills = $_POST['custom_skills'] ?? [];
        foreach ($customSkills as $customSkill) {
            $stmt = $pdo->prepare("
                INSERT INTO character_skills (character_id, skill_name, skill_value)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$characterId, $customSkill['name'], $customSkill['value']]);
        }

        $pdo->commit();

        // 動的にURLを生成してリダイレクト
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $redirectPath = "/momoya_character_manager/create_character_iachara.php";
        $redirectUrl = $protocol . $host . $redirectPath . "?message=" . urlencode("登録しました！") . "&success=1";
        header("Location: $redirectUrl");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("エラー: " . $e->getMessage());

        // 動的にURLを生成してエラーメッセージでリダイレクト
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $redirectPath = "/momoya_character_manager/create_character_iachara.php";
        $redirectUrl = $protocol . $host . $redirectPath . "?message=" . urlencode("エラーが発生しました") . "&success=0";
        header("Location: $redirectUrl");
        exit;
    }
}
