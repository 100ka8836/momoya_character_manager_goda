document.addEventListener("DOMContentLoaded", () => {
  // キャラクター名の行数を調整
  const labels = document.querySelectorAll(".label .character-name");

  labels.forEach((label) => {
    const lineHeight = parseFloat(getComputedStyle(label).lineHeight);
    const height = label.offsetHeight;
    const lines = Math.ceil(height / lineHeight);

    // 行数をデータ属性に設定
    label.dataset.lines = lines;

    // 必要に応じてフォントサイズを調整
    if (lines > 1) {
      label.style.fontSize = `${16 - (lines - 1) * 2}px`;
    }
  });

  // イベントフォームの管理
  const addEventButton = document.getElementById("add-event-button");
  const formContainer = document.getElementById("add-event-form-container");
  const form = document.getElementById("add-event-form");

  if (addEventButton && formContainer && form) {
    // イベントを追加ボタンのクリックでフォーム表示/非表示切り替え
    addEventButton.addEventListener("click", () => {
      formContainer.style.display =
        formContainer.style.display === "none" ? "block" : "none";
    });

    // フォーム送信処理
    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      const formData = new FormData(form);

      try {
        const response = await fetch("add_timeline_event.php", {
          method: "POST",
          body: formData
        });

        if (response.ok) {
          // 成功した場合はページをリロード
          window.location.reload();
        } else {
          const errorResult = await response.json();
          console.error(`エラー: ${errorResult.error}`);
        }
      } catch (error) {
        console.error("通信エラー:", error);
      }
    });
  } else {
    console.error("必要な要素が見つかりません: ボタン、フォームまたはコンテナ");
  }

  // ユーザーが移動可能な縦線の処理
  const movableLine = document.getElementById("movable-line");
  const timeline = document.getElementById("timeline-container");
  let isDragging = false;

  const disableTextSelection = () => {
    document.body.style.userSelect = "none";
  };

  const enableTextSelection = () => {
    document.body.style.userSelect = "";
  };

  if (movableLine && timeline) {
    timeline.addEventListener("mousedown", (e) => {
      if (e.target === movableLine) {
        isDragging = true;
        disableTextSelection(); // テキスト選択を無効化
      }
    });

    document.addEventListener("mousemove", (e) => {
      if (isDragging) {
        const timelineRect = timeline.getBoundingClientRect();
        let newLeft = e.clientX - timelineRect.left;

        // 左右の境界を制限
        if (newLeft < 0) newLeft = 0;
        if (newLeft > timelineRect.width) newLeft = timelineRect.width;

        movableLine.style.left = `${newLeft}px`;
      }
    });

    document.addEventListener("mouseup", () => {
      if (isDragging) {
        isDragging = false;
        enableTextSelection(); // テキスト選択を再び有効化
      }
    });
  } else {
    console.error(
      "必要な要素が見つかりません: movableLine または timeline-container"
    );
  }
});
