document.addEventListener("DOMContentLoaded", () => {
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");

  // アクティブなタブを設定する関数
  const activateTab = (tabId) => {
    tabButtons.forEach((btn) => btn.classList.remove("active"));
    tabContents.forEach((content) => content.classList.remove("active"));

    const activeButton = document.querySelector(
      `.tab-button[data-tab="${tabId}"]`
    );
    const activeContent = document.getElementById(tabId);

    if (activeButton && activeContent) {
      activeButton.classList.add("active");
      activeContent.classList.add("active");

      // 現在のタブを localStorage に保存
      localStorage.setItem("activeTab", tabId);
    }
  };

  // ページ読み込み時に localStorage からアクティブタブを復元
  const savedTab = localStorage.getItem("activeTab") || "basic";
  activateTab(savedTab);

  // タブのクリックイベントを設定
  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const tabId = button.dataset.tab;
      activateTab(tabId);
    });
  });
});
