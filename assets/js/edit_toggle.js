document.addEventListener("DOMContentLoaded", () => {
  // 基本タブの編集ボタン表示切り替え
  const toggleBasicButton = document.getElementById("toggle-basic-edit-mode");
  if (toggleBasicButton) {
    toggleBasicButton.addEventListener("click", () => {
      document.querySelectorAll("#basic .edit-button").forEach((btn) => {
        btn.style.display =
          btn.style.display === "none" ? "inline-block" : "none";
      });
    });
  } else {
    console.error("Basic edit toggle button not found.");
  }

  // 能力タブの編集ボタン表示切り替え
  const toggleAbilitiesButton = document.getElementById(
    "toggle-abilities-edit-mode"
  );
  if (toggleAbilitiesButton) {
    toggleAbilitiesButton.addEventListener("click", () => {
      document.querySelectorAll("#abilities .edit-button").forEach((btn) => {
        btn.style.display =
          btn.style.display === "none" ? "inline-block" : "none";
      });
    });
  } else {
    console.error("Abilities edit toggle button not found.");
  }
});
