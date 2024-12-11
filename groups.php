<?php
require 'includes/db.php';

// グループのデータを取得
$stmt = $pdo->query("SELECT `id`, `name` FROM `groups`");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>グループ一覧</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* グループ名のデザイン */
        .group-name {
            font-size: 1.5em;
            /* 大きめのフォントサイズ */
            font-weight: bold;
            /* 太字 */
            color: #333;
            /* 濃いグレー */
            cursor: pointer;
            /* クリック可能なポインタ */
            text-decoration: none;
            /* 下線なし */
            margin-bottom: 10px;
            /* 下部の余白 */
            display: inline-block;
            /* ブロック風に見える */
            padding: 10px 15px;
            /* 内側の余白を追加 */
            border: 2px solid #000;
            /* 黒い枠線 */
            border-radius: 8px;
            /* 少し角を丸くする */
            background-color: #fff;
            /* 白い背景 */
            transition: box-shadow 0.3s, transform 0.3s;
            /* ホバー時のアニメーション */
        }

        /* ホバー時のエフェクト */
        .group-name:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            /* 黒い影を追加 */
            transform: translateY(-4px);
            /* 少し浮き上がる */
        }

        .group-item {
            margin-bottom: 15px;
            /* 各グループ間の余白 */
        }

        .group-buttons {
            display: inline-flex;
            /* ボタンを横並びに */
            gap: 10px;
            /* ボタン間の間隔 */
            align-items: center;
            /* ボタンの高さを揃える */
        }

        .group-buttons button,
        .group-buttons a {
            display: inline-block;
            /* ボタン風に表示 */
            padding: 5px 10px;
            /* ボタンの内側余白 */
            border: 1px solid #000;
            /* 黒い枠線 */
            border-radius: 5px;
            /* 角丸 */
            text-decoration: none;
            /* 下線を消す */
            background-color: #f5f5f5;
            /* ボタン背景色（薄いグレー） */
            color: #000;
            /* テキストカラー */
            cursor: pointer;
            /* クリック可能 */
            margin: 0;
            /* 不要な余白を削除 */
            vertical-align: middle;
            /* 縦位置を揃える */
            transition: background-color 0.3s;
            /* ホバー時の色変化 */
        }

        .group-buttons button:hover,
        .group-buttons a:hover {
            background-color: #ddd;
            /* ホバー時に少し暗く */
        }
    </style>
    <!-- グループ管理スクリプト: グループの選択、パスワード入力モーダルの表示、認証処理を管理 -->
    <script src="assets/js/group_handler.js" defer></script>

    <!-- フォーム送信管理スクリプト: フォームの非同期送信を管理し、サーバー応答をユーザーに表示 -->
    <script src="assets/js/form_handler.js" defer></script>


</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main>

        <?php if (isset($_GET['created'])): ?>
            <p style="color: green;">グループが作成されました。</p>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <p style="color: green;">グループが削除されました。</p>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <p style="color: green;">グループが更新されました。</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;">エラー: <?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form method="POST" action="create_group_handler.php">
            <label>グループ名: <input type="text" name="name" required></label>
            <label>パスワード: <input type="password" name="password" required></label>
            <button type="submit">グループを作成</button>
            <div class="message"></div> <!-- メッセージ表示用 -->
        </form>



        <!-- グループリスト -->
        <h2>既存のグループ</h2>
        <ul>
            <?php foreach ($groups as $group): ?>
                <li class="group-item">
                    <div>
                        <!-- グループ名 -->
                        <span class="group-name" data-group-id="<?= htmlspecialchars($group['id']) ?>">
                            <?= htmlspecialchars($group['name']) ?>
                        </span>
                    </div>
                    <!-- 編集と削除ボタンを横並び -->
                    <div class="group-buttons">
                        <a href="edit_group.php?group_id=<?= htmlspecialchars($group['id']) ?>">編集</a>
                        <form method="POST" action="delete_group_handler.php" style="display:inline;">
                            <input type="hidden" name="group_id" value="<?= htmlspecialchars($group['id']) ?>">
                            <button type="submit" onclick="return confirm('本当に削除しますか？');">削除</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>


        <!-- グループ一覧の下など、HTMLの最後に追加 -->
        <div id="password-modal" style="display: none;">
            <div class="modal-content">
                <span id="modal-close">&times;</span>
                <form id="password-form">
                    <input type="hidden" name="group_id" id="group-id">
                    <label for="password">グループパスワード:</label>
                    <input type="password" name="password" id="password" required>
                    <button type="submit">入室</button>
                </form>
                <p id="error-message" style="display: none; color: red;"></p>
            </div>
        </div>


    </main>
</body>

</html>