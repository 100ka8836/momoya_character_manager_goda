<?php
require 'includes/db.php';
require 'includes/header.php';

// メッセージの初期化
$message = $_POST['message'] ?? ''; // POSTから取得できない場合は空文字列
$messageClass = (strpos($message, 'エラー') !== false) ? 'error-message' : 'success-message';

// データベースからグループ一覧を取得
$stmt = $pdo->query("SELECT id, name FROM `groups`");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// グループデータの取得
$stmt = $pdo->query("SELECT id, name FROM `groups`");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>キャラクター保管所から登録</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <main>
        <h1>キャラクター保管所から登録</h1>

        <!-- メッセージ表示部分 -->
        <?php if ($message): ?>
            <p class="<?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="includes/create_character_handler_charasheet.php">
            <label>キャラクターシートのURLを入力してください:</label><br>
            <input type="url" name="charasheet_url" required><br>

            <!-- グループ選択を追加 -->
            <fieldset>
                <legend>所属グループ</legend>
                <label for="group">グループ:</label>
                <select name="group_id" id="group">
                    <option value="">-- グループを選択 --</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']) ?>"><?= htmlspecialchars($group['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </fieldset>


            <fieldset>
                <legend>技能値</legend>
                <div>
                    <p>―――――＜戦闘技能＞―――――</p>
                    <label>回避: <input type="number" name="skills[回避]" min="0" max="100" value="0"></label><br>
                    <label>キック: <input type="number" name="skills[キック]" min="0" max="100" value="25"></label><br>
                    <label>組みつき: <input type="number" name="skills[組みつき]" min="0" max="100" value="25"></label><br>
                    <label>こぶし: <input type="number" name="skills[こぶし]" min="0" max="100" value="50"></label><br>
                    <label>頭突き: <input type="number" name="skills[頭突き]" min="0" max="100" value="10"></label><br>
                    <label>投擲: <input type="number" name="skills[投擲]" min="0" max="100" value="25"></label><br>
                    <label>マーシャルアーツ: <input type="number" name="skills[マーシャルアーツ]" min="0" max="100"
                            value="1"></label><br>
                    <label>拳銃: <input type="number" name="skills[拳銃]" min="0" max="100" value="20"></label><br>
                    <label>サブマシンガン: <input type="number" name="skills[サブマシンガン]" min="0" max="100"
                            value="15"></label><br>
                    <label>ショットガン: <input type="number" name="skills[ショットガン]" min="0" max="100" value="30"></label><br>
                    <label>マシンガン: <input type="number" name="skills[マシンガン]" min="0" max="100" value="15"></label><br>
                    <label>ライフル: <input type="number" name="skills[ライフル]" min="0" max="100" value="25"></label><br>
                    <p>―――――＜探索技能＞―――――</p>
                    <label>応急手当: <input type="number" name="skills[応急手当]" min="0" max="100" value="30"></label><br>
                    <label>鍵開け: <input type="number" name="skills[鍵開け]" min="0" max="100" value="1"></label><br>
                    <label>隠す: <input type="number" name="skills[隠す]" min="0" max="100" value="15"></label><br>
                    <label>隠れる: <input type="number" name="skills[隠れる]" min="0" max="100" value="10"></label><br>
                    <label>聞き耳: <input type="number" name="skills[聞き耳]" min="0" max="100" value="25"></label><br>
                    <label>忍び歩き: <input type="number" name="skills[忍び歩き]" min="0" max="100" value="10"></label><br>
                    <label>写真術: <input type="number" name="skills[写真術]" min="0" max="100" value="10"></label><br>
                    <label>精神分析: <input type="number" name="skills[精神分析]" min="0" max="100" value="1"></label><br>
                    <label>追跡: <input type="number" name="skills[追跡]" min="0" max="100" value="10"></label><br>
                    <label>登攀: <input type="number" name="skills[登攀]" min="0" max="100" value="40"></label><br>
                    <label>図書館: <input type="number" name="skills[図書館]" min="0" max="100" value="25"></label><br>
                    <label>目星: <input type="number" name="skills[目星]" min="0" max="100" value="25"></label><br>
                    <p>―――――＜行動技能＞―――――</p>
                    <label>運転（自動車）: <input type="number" name="skills[運転（自動車）]" min="0" max="100"
                            value="20"></label><br>
                    <label>機械修理: <input type="number" name="skills[機械修理]" min="0" max="100" value="20"></label><br>
                    <label>重機械操作: <input type="number" name="skills[重機械操作]" min="0" max="100" value="1"></label><br>
                    <label>乗馬: <input type="number" name="skills[乗馬]" min="0" max="100" value="5"></label><br>
                    <label>水泳: <input type="number" name="skills[水泳]" min="0" max="100" value="25"></label><br>
                    <label>製作（）: <input type="number" name="skills[製作（）]" min="0" max="100" value="5"></label><br>
                    <label>操縦（）: <input type="number" name="skills[操縦（）]" min="0" max="100" value="1"></label><br>
                    <label>跳躍: <input type="number" name="skills[跳躍]" min="0" max="100" value="25"></label><br>
                    <label>電気修理: <input type="number" name="skills[電気修理]" min="0" max="100" value="10"></label><br>
                    <label>ナビゲート: <input type="number" name="skills[ナビゲート]" min="0" max="100" value="10"></label><br>
                    <label>変装: <input type="number" name="skills[変装]" min="0" max="100" value="1"></label><br>
                    <p>―――――＜交渉技能＞―――――</p>
                    <label>言いくるめ: <input type="number" name="skills[言いくるめ]" min="0" max="100" value="5"></label><br>
                    <label>信用: <input type="number" name="skills[信用]" min="0" max="100" value="15"></label><br>
                    <label>説得: <input type="number" name="skills[説得]" min="0" max="100" value="15"></label><br>
                    <label>値切り: <input type="number" name="skills[値切り]about:blank#blocked" min="0" max="100"
                            value="5"></label><br>
                    <label>母国語: <input type="number" name="skills[母国語]" min="0" max="100" value="0"></label><br>
                    <p>―――――＜知識技能＞―――――</p>
                    <label>医学: <input type="number" name="skills[医学]" min="0" max="100" value="5"></label><br>
                    <label>オカルト: <input type="number" name="skills[オカルト]" min="0" max="100" value="5"></label><br>
                    <label>化学: <input type="number" name="skills[化学]" min="0" max="100" value="1"></label><br>
                    <label>クトゥルフ神話: <input type="number" name="skills[クトゥルフ神話]" min="0" max="100" value="0"></label><br>
                    <label>芸術: <input type="number" name="skills[芸術]" min="0" max="100" value="5"></label><br>
                    <label>経理: <input type="number" name="skills[経理]" min="0" max="100" value="10"></label><br>
                    <label>考古学: <input type="number" name="skills[考古学]" min="0" max="100" value="1"></label><br>
                    <label>コンピューター: <input type="number" name="skills[コンピューター]" min="0" max="100" value="1"></label><br>
                    <label>心理学: <input type="number" name="skills[心理学]" min="0" max="100" value="5"></label><br>
                    <label>人類学: <input type="number" name="skills[人類学]" min="0" max="100" value="1"></label><br>
                    <label>生物学: <input type="number" name="skills[生物学]" min="0" max="100" value="1"></label><br>
                    <label>地質学: <input type="number" name="skills[地質学]" min="0" max="100" value="1"></label><br>
                    <label>電子工学: <input type="number" name="skills[電子工学]" min="0" max="100" value="1"></label><br>
                    <label>天文学: <input type="number" name="skills[天文学]" min="0" max="100" value="1"></label><br>
                    <label>博物学: <input type="number" name="skills[博物学]" min="0" max="100" value="10"></label><br>
                    <label>物理学: <input type="number" name="skills[物理学]" min="0" max="100" value="1"></label><br>
                    <label>法律: <input type="number" name="skills[法律]" min="0" max="100" value="5"></label><br>
                    <label>薬学: <input type="number" name="skills[薬学]" min="0" max="100" value="1"></label><br>
                    <label>歴史: <input type="number" name="skills[歴史]" min="0" max="100" value="20"></label><br>
                    <p>―――――＜追加技能＞―――――</p>
                </div>

                <div id="additional-skills"></div> <!-- 追加技能フォームがここに入る -->
                <button type="button" onclick="addSkillInput()">技能追加</button> <!-- 技能追加ボタン -->
            </fieldset>

            <button type="submit">登録</button>
        </form>

        <script>
            // 追加技能を管理する変数
            let skillCounter = 0;

            // 技能追加ボタンの動作
            function addSkillInput() {
                const container = document.getElementById("additional-skills");
                const div = document.createElement("div");

                div.innerHTML = `
                <label>技能名: <input type="text" name="custom_skills[${skillCounter}][name]" required></label>
                <label>技能値: <input type="number" name="custom_skills[${skillCounter}][value]" min="0" max="100" required></label>
            `;
                container.appendChild(div);
                skillCounter++;
            }
        </script>
    </main>
</body>

</html>