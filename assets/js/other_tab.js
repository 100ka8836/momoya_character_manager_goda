document.addEventListener("DOMContentLoaded", () => {
  const otherTabButton = document.querySelector(
    '.tab-button[data-tab="other"]'
  );
  const otherTabContent = document.querySelector("#other");

  if (otherTabButton && otherTabContent) {
    otherTabButton.addEventListener("click", () => {
      // 既存のアクティブなタブと内容を非アクティブ化
      const activeButton = document.querySelector(".tab-button.active");
      const activeContent = document.querySelector(".tab-content.active");

      if (activeButton) activeButton.classList.remove("active");
      if (activeContent) activeContent.classList.remove("active");

      // その他タブをアクティブ化
      otherTabButton.classList.add("active");
      otherTabContent.classList.add("active");
    });
  }
});
