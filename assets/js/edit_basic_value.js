function editBasicValue(characterId, fieldName) {
  // セルの取得
  const cell = document.getElementById(
    `basic-cell-${characterId}-${fieldName}`
  );
  const currentValue = cell.querySelector(".value-display").innerText.trim();

  // フォームを作成
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "edit_basic_value.php";

  // 入力フィールド
  const input = document.createElement("input");
  input.type = "text";
  input.name = "new_value";
  input.value = currentValue;
  input.required = true;

  // 隠しフィールド（キャラクターIDとフィールド名）
  const hiddenCharId = document.createElement("input");
  hiddenCharId.type = "hidden";
  hiddenCharId.name = "character_id";
  hiddenCharId.value = characterId;

  const hiddenFieldName = document.createElement("input");
  hiddenFieldName.type = "hidden";
  hiddenFieldName.name = "field_name";
  hiddenFieldName.value = fieldName;

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
  form.appendChild(hiddenFieldName);
  form.appendChild(hiddenGroupId);
  form.appendChild(saveButton);

  // 現在の値をフォームに置き換え
  cell.innerHTML = "";
  cell.appendChild(form);
}

document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.getElementById("toggle-basic-edit-mode");
  let isEditMode = false; // 編集モードを管理するフラグ

  if (toggleButton) {
    toggleButton.addEventListener("click", () => {
      // 編集モードの状態をトグル
      isEditMode = !isEditMode;

      // ボタンテキストの切り替え
      toggleButton.textContent = isEditMode ? "完了" : "基本情報の変更";

      // カラーコード編集モードの表示/非表示切り替え
      document.querySelectorAll('[id^="color-edit-"]').forEach((element) => {
        element.style.display = isEditMode ? "block" : "none";
      });

      // 完了時（編集モード終了）に保存処理を実行
      if (!isEditMode) {
        saveColorChanges();
      }
    });
  }
});

// カラーコードの変更をまとめて保存
function saveColorChanges() {
  const colorInputs = document.querySelectorAll('[id^="color-input-"]');
  const formData = new FormData();

  colorInputs.forEach((input) => {
    const characterId = input.id.split("-")[2]; // 入力欄のIDからキャラクターIDを取得
    formData.append(`color_codes[${characterId}]`, input.value);
  });

  // データをサーバーに送信
  fetch("save_color_codes.php", {
    method: "POST",
    body: formData
  })
    .then((response) => response.text())
    .then((data) => {
      if (data === "success") {
        location.reload(); // 保存後にリロード
      } else {
        console.error("保存エラー:", data);
      }
    })
    .catch((error) => {
      console.error("通信エラー:", error);
    });
}

// カラーピッカーとテキスト入力を同期
document.querySelectorAll('[id^="color-picker-"]').forEach((picker) => {
  const characterId = picker.id.split("-")[2];
  const colorInput = document.getElementById(`color-input-${characterId}`);

  picker.addEventListener("input", () => {
    colorInput.value = picker.value; // カラーピッカーの値を入力欄に反映
  });

  colorInput.addEventListener("input", () => {
    if (/^#[0-9A-Fa-f]{6}$/.test(colorInput.value)) {
      picker.value = colorInput.value; // 入力欄の値をカラーピッカーに反映
    }
  });
});
