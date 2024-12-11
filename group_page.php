<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'includes/db.php';
require 'fetch_skills.php';

$group_id = $_GET['group_id'] ?? null;

if (!$group_id || !is_numeric($group_id)) {
    echo "<p>無効なグループIDです。<a href='index.php'>戻る</a></p>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT `characters`.`id`, `characters`.`name`, `characters`.`image_path`,
           `characters`.`occupation`, `characters`.`birthplace`, 
           `characters`.`degree`, `characters`.`age`, `characters`.`sex`, 
           COALESCE(`characters`.`color_code`, '#FFFFFF') AS color_code,
           `characters`.`player_name`, 
           `character_attributes`.`str`, `character_attributes`.`con`, 
           `character_attributes`.`pow`, `character_attributes`.`dex`, 
           `character_attributes`.`app`, `character_attributes`.`siz`,
           `character_attributes`.`int_value`, `character_attributes`.`edu`, 
           `character_attributes`.`hp`, `character_attributes`.`mp`, 
           `character_attributes`.`db`, `character_attributes`.`san_current`, 
           `character_attributes`.`san_max`
    FROM `characters`
    LEFT JOIN `character_attributes` 
           ON `characters`.`id` = `character_attributes`.`character_id`
    WHERE `characters`.`group_id` = ?
");

$stmt->execute([$group_id]);
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

function adjustTextColor($backgroundColor)
{
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $backgroundColor)) {
        return '#000000';
    }
    $r = hexdec(substr($backgroundColor, 1, 2));
    $g = hexdec(substr($backgroundColor, 3, 2));
    $b = hexdec(substr($backgroundColor, 5, 2));
    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
    return $brightness < 128 ? '#FFFFFF' : '#000000';
}

if (empty($characters)) {
    echo "<p>このグループにはキャラクターが登録されていません。</p>";
    echo "<a href='add_character.php?group_id=" . htmlspecialchars($group_id) . "'>キャラクターを追加する</a>";
    exit;
}

$skillsData = fetchSkills($group_id, $pdo);
$skills = $skillsData['skills'] ?? [];
$all_skills = $skillsData['all_skills'] ?? [];

$stmt = $pdo->prepare("SELECT * FROM categories WHERE group_id = ?");
$stmt->execute([$group_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activeTab = $_GET['activeTab'] ?? 'basic';

echo "<script>const characters = " . json_encode($characters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ";</script>";
?>



<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>グループ詳細</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- スクリプト -->
    <script src="assets/js/tabs.js" defer></script>
    <script src="assets/js/sort_table.js" defer></script>
    <script src="assets/js/search_table.js" defer></script>
    <script src="assets/js/other_tab.js" defer></script>
    <script src="assets/js/edit_category.js" defer></script>
    <script src="assets/js/edit_value.js" defer></script>
    <script src="assets/js/edit_toggle.js" defer></script>
    <script src="assets/js/edit_basic_value.js" defer></script>
    <script src="assets/js/edit_ability_value.js" defer></script>
    <script src="assets/js/edit_color_code.js" defer></script>
    <script src="assets/js/dynamic_lighten_color.js" defer></script>

</head>

<body data-group-id="<?= htmlspecialchars($group_id) ?>">
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="container">
        <div class="tabs">
            <button class="tab-button active" data-tab="basic">基本</button>
            <button class="tab-button" data-tab="abilities">能力</button>
            <button class="tab-button" data-tab="skills">技能</button>
            <button class="tab-button" data-tab="other">その他</button>
        </div>

        <!-- 基本情報タブ -->
        <div id="basic" class="tab-content active">
            <table id="sortable-table">
                <div>
                    <input type="text" class="column-search" placeholder="検索: 例 年齢, STR, 目星">
                </div>
                <thead>
                    <tr>
                        <th>カラム</th>
                        <?php foreach ($characters as $character): ?>

                            <th data-color="<?= htmlspecialchars($character['color_code']) ?>"
                                style="text-align: center; background-color: <?= htmlspecialchars($character['color_code']) ?>;">

                                <!-- PL 名表示 -->
                                <span class="pl-name">
                                    <?= htmlspecialchars($character['player_name'] ?? '未設定') ?>
                                </span>
                                <br>

                                <!-- 画像を表示 -->
                                <?php
                                // デフォルト画像パス
                                $defaultImagePath = 'images/kumaaikon.png';

                                // キャラクターの画像パスを取得（なければデフォルト画像を表示）
                                $iconPath = !empty($character['image_path'])
                                    ? htmlspecialchars($character['image_path'])
                                    : $defaultImagePath;
                                ?>
                                <img src="<?= htmlspecialchars($iconPath) ?>?v=<?= time(); ?>"
                                    alt="<?= htmlspecialchars($character['name']) ?>"
                                    style="width: 100px; height: 100px; object-fit: scale-down; border-radius: 20%;">

                                <br>

                                <!-- キャラクター名 -->
                                <span style="color: <?= adjustTextColor($character['color_code']) ?>;">
                                    <?= htmlspecialchars($character['name']) ?>
                                </span>
                                <br>
                                <!-- 色コード -->
                                <span style="color: <?= adjustTextColor($character['color_code']) ?>;">
                                    <?= htmlspecialchars($character['color_code']) ?>
                                </span>
                                <div id="color-edit-<?= $character['id'] ?>" style="display: none; margin-top: 5px;">
                                    <input type="color" id="color-picker-<?= $character['id'] ?>"
                                        value="<?= htmlspecialchars($character['color_code']) ?>">
                                    <input type="text" id="color-input-<?= $character['id'] ?>"
                                        value="<?= htmlspecialchars($character['color_code']) ?>"
                                        style="width: 80px; text-align: center;">
                                </div>
                            </th>



                        <?php endforeach; ?>
                        <th>以上</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>職業</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="basic-cell-<?= $c['id'] ?>-occupation"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['occupation']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editBasicValue(<?= $c['id'] ?>, 'occupation')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>住所</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="basic-cell-<?= $c['id'] ?>-birthplace"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['birthplace']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editBasicValue(<?= $c['id'] ?>, 'birthplace')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>年齢</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="basic-cell-<?= $c['id'] ?>-age" data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['age']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editBasicValue(<?= $c['id'] ?>, 'age')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>性別</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="basic-cell-<?= $c['id'] ?>-sex" data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['sex']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editBasicValue(<?= $c['id'] ?>, 'sex')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>

                </tbody>
            </table>

            <div class="button-container">
                <button id="toggle-image-pl-form">画像とPL名を登録</button>
            </div>

            <div id="image-pl-form-section" style="display: none; margin-top: 20px;">
                <!-- 画像とPL名の登録フォーム -->
                <form id="image-pl-form" action="upload_image.php" method="POST" enctype="multipart/form-data">
                    <!-- キャラクター選択 -->
                    <label for="character-select">キャラクターを選択:</label>
                    <select name="character_id" id="character-select" style="margin-bottom: 10px;">
                        <?php foreach ($characters as $character): ?>
                            <option value="<?= htmlspecialchars($character['id']) ?>">
                                <?= htmlspecialchars($character['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- グループID -->
                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id) ?>">

                    <!-- PL名の入力 -->
                    <label for="pl-name">PL名:</label>
                    <input type="text" name="player_name" id="pl-name" placeholder="PL名を入力"
                        style="width: 80%; padding: 5px; margin-bottom: 10px;">

                    <!-- 画像のアップロード -->
                    <label for="character-image">画像を選択:</label>
                    <input type="file" name="character_image" id="character-image" accept="image/*"
                        style="margin-bottom: 10px;">

                    <!-- 送信ボタン -->
                    <button type="submit" style="margin-top: 5px;">登録</button>
                </form>
            </div>

            <button id="toggle-basic-edit-mode">基本情報の変更</button>

        </div>



        <!-- 能力値タブ -->
        <div id="abilities" class="tab-content">
            <table id="sortable-table">
                <div>
                    <input type="text" class="column-search" placeholder="検索: 例 STR, DEX, POW">
                </div>
                <thead>
                    <tr>
                        <th>カラム</th>
                        <?php foreach ($characters as $character): ?>
                            <th data-color="<?= htmlspecialchars($character['color_code']) ?>"
                                style="text-align: center; background-color: <?= htmlspecialchars($character['color_code']) ?>;">

                                <!-- PL 名表示 -->
                                <span class="pl-name">
                                    <?= htmlspecialchars($character['player_name'] ?? '未設定') ?>
                                </span>
                                <br>

                                <!-- 画像を表示 -->
                                <?php
                                // デフォルト画像パス
                                $defaultImagePath = 'images/kumaaikon.png';

                                // キャラクターの画像パスを取得（なければデフォルト画像を表示）
                                $iconPath = !empty($character['image_path'])
                                    ? htmlspecialchars($character['image_path'])
                                    : $defaultImagePath;
                                ?>
                                <img src="<?= htmlspecialchars($iconPath) ?>?v=<?= time(); ?>"
                                    alt="<?= htmlspecialchars($character['name']) ?>"
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 20%;">
                                <br>

                                <!-- キャラクター名 -->
                                <span style="color: <?= adjustTextColor($character['color_code']) ?>;">
                                    <?= htmlspecialchars($character['name']) ?>
                                </span>
                            </th>
                        <?php endforeach; ?>
                        <th>以上</th> <!-- 操作列 -->
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>STR</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-str"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['str']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'str')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>CON</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-con"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['con']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'con')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>POW</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-pow"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['pow']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'pow')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>DEX</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-dex"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['dex']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'dex')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>APP</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-app"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['app']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'app')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>SIZ</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-siz"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['siz']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'siz')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>INT</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-int_value"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['int_value']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'int_value')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>EDU</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-edu"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['edu']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'edu')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>HP</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-hp"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['hp']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'hp')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>MP</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-mp"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['mp']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'mp')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>DB</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-db"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['db']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'db')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>現在SAN</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-san_current"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['san_current']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'san_current')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                    <tr>
                        <td>最大SAN</td>
                        <?php foreach ($characters as $c): ?>
                            <td id="abilities-cell-<?= $c['id'] ?>-san_max"
                                data-color="<?= htmlspecialchars($c['color_code']) ?>">
                                <span class="value-display"><?= htmlspecialchars($c['san_max']) ?></span>
                                <button class="edit-button" style="display: none;"
                                    onclick="editAbilityValue(<?= $c['id'] ?>, 'san_max')">編集</button>
                            </td>
                        <?php endforeach; ?>
                        <td></td> <!-- 操作列の空白 -->
                    </tr>
                </tbody>
            </table>
            <button id="toggle-abilities-edit-mode">能力値の変更</button>
        </div>



        <!-- 技能タブ -->
        <div id="skills" class="tab-content">
            <table id="sortable-table">
                <div>
                    <input type="text" class="column-search" placeholder="検索: 例 STR, DEX, POW">
                </div>
                <thead>
                    <tr>
                        <th>技能</th>
                        <?php foreach ($characters as $character): ?>
                            <th data-color="<?= htmlspecialchars($character['color_code']) ?>"
                                style="text-align: center; background-color: <?= htmlspecialchars($character['color_code']) ?>;">

                                <!-- PL 名表示 -->
                                <span class="pl-name">
                                    <?= htmlspecialchars($character['player_name'] ?? '未設定') ?>
                                </span>
                                <br>

                                <!-- 画像を表示 -->
                                <?php
                                // デフォルト画像パス
                                $defaultImagePath = 'images/kumaaikon.png';

                                // キャラクターの画像パスを取得（なければデフォルト画像を表示）
                                $iconPath = !empty($character['image_path'])
                                    ? htmlspecialchars($character['image_path'])
                                    : $defaultImagePath;
                                ?>
                                <img src="<?= htmlspecialchars($iconPath) ?>?v=<?= time(); ?>"
                                    alt="<?= htmlspecialchars($character['name']) ?>"
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 20%;">
                                <br>

                                <!-- キャラクター名 -->
                                <span style="color: <?= adjustTextColor($character['color_code']) ?>;">
                                    <?= htmlspecialchars($character['name']) ?>
                                </span>
                            </th>
                        <?php endforeach; ?>
                        <th>以上</th> <!-- 操作列 -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_skills as $skill_name): ?>
                        <tr>
                            <td><?= htmlspecialchars($skill_name) ?></td>
                            <?php foreach ($characters as $character): ?>
                                <td id="skills-cell-<?= $character['id'] ?>-<?= htmlspecialchars($skill_name) ?>"
                                    data-color="<?= htmlspecialchars($character['color_code']) ?>">
                                    <span
                                        class="value-display"><?= htmlspecialchars($skills[$character['id']][$skill_name] ?? '-') ?></span>
                                </td>
                            <?php endforeach; ?>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>




        <!-- その他タブ -->
        <div id="other" class="tab-content">
            <table id="sortable-table">
                <div>
                    <input type="text" class="column-search" placeholder="検索: 例 年齢, STR, 目星">
                </div>
                <thead>
                    <tr>
                        <th>項目</th>
                        <?php foreach ($characters as $character): ?>
                            <th data-color="<?= htmlspecialchars($character['color_code']) ?>"
                                style="text-align: center; background-color: <?= htmlspecialchars($character['color_code']) ?>;">

                                <!-- PL 名表示 -->
                                <span class="pl-name">
                                    <?= htmlspecialchars($character['player_name'] ?? '未設定') ?>
                                </span>
                                <br>

                                <!-- 画像を表示 -->
                                <?php
                                // デフォルト画像パス
                                $defaultImagePath = 'images/kumaaikon.png';

                                // キャラクターの画像パスを取得（なければデフォルト画像を表示）
                                $iconPath = !empty($character['image_path'])
                                    ? htmlspecialchars($character['image_path'])
                                    : $defaultImagePath;
                                ?>
                                <img src="<?= htmlspecialchars($iconPath) ?>?v=<?= time(); ?>"
                                    alt="<?= htmlspecialchars($character['name']) ?>"
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 20%;">
                                <br>

                                <!-- キャラクター名 -->
                                <span style="color: <?= adjustTextColor($character['color_code']) ?>;">
                                    <?= htmlspecialchars($character['name']) ?>
                                </span>
                            </th>
                        <?php endforeach; ?>
                        <th>操作</th> <!-- 削除ボタン用の列 -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // カテゴリを取得
                    $stmt = $pdo->prepare("SELECT * FROM `categories` WHERE `group_id` = ?");
                    $stmt->execute([$group_id]);
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['name']); ?></td>
                            <?php foreach ($characters as $character): ?>
                                <td id="value-cell-<?= $character['id'] ?>-<?= $category['id'] ?>"
                                    data-color="<?= htmlspecialchars($character['color_code']) ?>">
                                    <?php
                                    $stmtValue = $pdo->prepare("
                        SELECT value
                        FROM charactervalues
                        WHERE character_id = ? AND category_id = ?
                    ");
                                    $stmtValue->execute([$character['id'], $category['id']]);
                                    $value = $stmtValue->fetchColumn();
                                    ?>
                                    <span class="value-display"><?= htmlspecialchars($value ?? '-') ?></span>
                                    <button class="edit-button" style="display: none;"
                                        onclick="editValue(<?= $character['id'] ?>, <?= $category['id'] ?>)">編集</button>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <form method="POST" action="delete_category.php" style="display: inline;">
                                    <input type="hidden" name="category_id"
                                        value="<?= htmlspecialchars($category['id']); ?>">
                                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id); ?>">
                                    <button type="submit" class="delete-button">削除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- カテゴリ追加フォーム -->
            <form id="add-category-form" method="POST" action="add_category.php">
                <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id); ?>">
                <input type="text" id="category-name" name="category_name" required placeholder="追加する項目名">
                <button type="submit">＋</button>
            </form>

            <!-- 値の変更ボタン -->
            <button id="toggle-edit-mode">キャラクター情報の変更</button>
        </div>




    </main>
    <script>
        const groupId = <?= json_encode($group_id); ?>;
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleImagePLButton = document.getElementById("toggle-image-pl-form");
            const imagePLFormSection = document.getElementById("image-pl-form-section");

            if (toggleImagePLButton && imagePLFormSection) {
                toggleImagePLButton.addEventListener("click", () => {
                    // 現在の表示状態を取得
                    const isHidden = window.getComputedStyle(imagePLFormSection).display === "none";

                    // 表示状態を切り替え
                    imagePLFormSection.style.display = isHidden ? "block" : "none";

                    // ボタンテキストを切り替え
                    toggleImagePLButton.dataset.toggleState =
                        toggleImagePLButton.dataset.toggleState === "open" ? "closed" : "open";

                    toggleImagePLButton.textContent =
                        toggleImagePLButton.dataset.toggleState === "open"
                            ? "登録フォームを閉じる"
                            : "画像とPL名を登録";
                });
            } else {
                if (!toggleImagePLButton) {
                    console.error("Toggle button with ID 'toggle-image-pl-form' not found.");
                }
                if (!imagePLFormSection) {
                    console.error("Form section with ID 'image-pl-form-section' not found.");
                }
            }
        });

    </script>


</body>

</html>