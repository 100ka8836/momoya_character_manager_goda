// カラーコードを編集する関数
function editColorCode(characterId) {
  const colorPicker = document.getElementById(`color-picker-${characterId}`);
  const colorInput = document.getElementById(`color-input-${characterId}`);
  const colorDisplay = document.getElementById(`color-display-${characterId}`);
  const editContainer = document.getElementById(`color-edit-${characterId}`);

  // フォームを表示
  editContainer.style.display = "block";
  colorDisplay.style.display = "none";

  // カラーピッカーと入力欄を同期
  colorPicker.addEventListener("input", () => {
    colorInput.value = colorPicker.value;
  });

  colorInput.addEventListener("input", () => {
    if (/^#[0-9A-Fa-f]{6}$/.test(colorInput.value)) {
      colorPicker.value = colorInput.value;
    }
  });

  // 保存処理
  const saveColor = () => {
    const newColor = colorInput.value;

    if (!/^#[0-9A-Fa-f]{6}$/.test(newColor)) {
      alert("無効なカラーコードです。#111111 の形式で入力してください。");
      return;
    }

    const formData = new FormData();
    formData.append("character_id", characterId);
    formData.append("color_code", newColor);

    fetch("edit_color_code.php", {
      method: "POST",
      body: formData
    })
      .then((response) => response.text())
      .then((data) => {
        if (data === "success") {
          // 背景色と表示を更新
          colorDisplay.textContent = newColor;
          colorDisplay.style.color = adjustTextColor(newColor);
          colorDisplay.style.display = "inline";
          editContainer.style.display = "none";
        } else {
          console.error("サーバーエラー:", data);
        }
      })
      .catch((error) => {
        console.error("リクエストエラー:", error);
      });
  };

  // 保存をカラーピッカーや入力欄の変更時に呼び出す
  colorInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      saveColor();
    }
  });

  colorPicker.addEventListener("change", saveColor);
}

// 輝度による文字色の調整
function adjustTextColor(backgroundColor) {
  const r = parseInt(backgroundColor.substr(1, 2), 16);
  const g = parseInt(backgroundColor.substr(3, 2), 16);
  const b = parseInt(backgroundColor.substr(5, 2), 16);
  const brightness = (r * 299 + g * 587 + b * 114) / 1000;
  return brightness < 128 ? "#FFFFFF" : "#000000";
}

// 編集モードの表示切り替え
document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.getElementById("toggle-basic-edit-mode");
  if (toggleButton) {
    toggleButton.addEventListener("click", () => {
      const isEditMode = toggleButton.getAttribute("data-active") === "true";

      // ボタンテキストの切り替え
      toggleButton.textContent = isEditMode ? "基本情報の変更" : "完了";
      toggleButton.setAttribute("data-active", !isEditMode);

      // カラーコード編集モードの表示/非表示切り替え
      document.querySelectorAll('[id^="color-edit-"]').forEach((element) => {
        element.style.display = isEditMode ? "none" : "block";
      });

      // 完了ボタン時には保存処理を呼び出し
      if (isEditMode) {
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
    const characterId = input.id.split("-")[2];
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
