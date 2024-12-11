function editAbilityValue(characterId, abilityName) {
  // セルの取得
  const cell = document.getElementById(
    `abilities-cell-${characterId}-${abilityName}`
  );
  if (!cell) {
    console.error(
      `Cell not found for ID: abilities-cell-${characterId}-${abilityName}`
    );
    return;
  }

  const currentValue = cell.querySelector(".value-display").innerText.trim();

  // フォームを作成
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "edit_ability_value.php";

  // 入力フィールド
  const input = document.createElement("input");
  input.type = "text";
  input.name = "new_value";
  input.value = currentValue;
  input.required = true;

  // 隠しフィールド（キャラクターIDと能力名）
  const hiddenCharId = document.createElement("input");
  hiddenCharId.type = "hidden";
  hiddenCharId.name = "character_id";
  hiddenCharId.value = characterId;

  const hiddenAbilityName = document.createElement("input");
  hiddenAbilityName.type = "hidden";
  hiddenAbilityName.name = "ability_name";
  hiddenAbilityName.value = abilityName;

  // 隠しフィールド（グループID）
  const hiddenGroupId = document.createElement("input");
  hiddenGroupId.type = "hidden";
  hiddenGroupId.name = "group_id";
  hiddenGroupId.value = document.body.getAttribute("data-group-id");

  // 保存ボタン
  const saveButton = document.createElement("button");
  saveButton.type = "submit";
  saveButton.innerText = "保存";

  // フォームに要素を追加
  form.appendChild(input);
  form.appendChild(hiddenCharId);
  form.appendChild(hiddenAbilityName);
  form.appendChild(hiddenGroupId);
  form.appendChild(saveButton);

  // 現在の値をフォームに置き換え
  cell.innerHTML = "";
  cell.appendChild(form);
}
