document.addEventListener("DOMContentLoaded", () => {
  const toggleEditButton = document.getElementById("toggle-edit-mode");
  const editButtons = document.querySelectorAll(".edit-button");

  // 値の変更ボタンがクリックされたときの処理
  toggleEditButton.addEventListener("click", () => {
    editButtons.forEach((button) => {
      if (button.style.display === "none") {
        button.style.display = "inline-block"; // 表示
      } else {
        button.style.display = "none"; // 非表示
      }
    });
  });
});

function editValue(characterId, categoryId) {
  // 対象セルを取得
  const cell = document.getElementById(
    `value-cell-${characterId}-${categoryId}`
  );
  const currentValue = cell.querySelector(".value-display").innerText.trim();

  // 現在のグループIDを取得
  const groupId = document.querySelector('input[name="group_id"]').value;

  // フォームを生成
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "edit_value.php";

  // 入力フィールド
  const input = document.createElement("input");
  input.type = "text";
  input.name = "new_value";
  input.value = currentValue;
  input.required = true;

  // 隠しフィールド（キャラクターID）
  const hiddenCharId = document.createElement("input");
  hiddenCharId.type = "hidden";
  hiddenCharId.name = "character_id";
  hiddenCharId.value = characterId;

  // 隠しフィールド（カテゴリID）
  const hiddenCategoryId = document.createElement("input");
  hiddenCategoryId.type = "hidden";
  hiddenCategoryId.name = "category_id";
  hiddenCategoryId.value = categoryId;

  // 隠しフィールド（グループID）
  const hiddenGroupId = document.createElement("input");
  hiddenGroupId.type = "hidden";
  hiddenGroupId.name = "group_id";
  hiddenGroupId.value = groupId;

  // 保存ボタン
  const saveButton = document.createElement("button");
  saveButton.type = "submit";
  saveButton.innerText = "保存";

  // フォームに要素を追加
  form.appendChild(input);
  form.appendChild(hiddenCharId);
  form.appendChild(hiddenCategoryId);
  form.appendChild(hiddenGroupId);
  form.appendChild(saveButton);

  // セルの内容をフォームに置き換え
  cell.innerHTML = "";
  cell.appendChild(form);
}
