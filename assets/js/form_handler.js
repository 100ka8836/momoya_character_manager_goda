document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    form.addEventListener("submit", async (event) => {
      event.preventDefault(); // デフォルト送信を防ぐ
      const formData = new FormData(form);
      const messageDiv = form.querySelector(".message");

      try {
        const response = await fetch(form.action, {
          method: "POST",
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          messageDiv.textContent = result.message || "操作が成功しました！";
          messageDiv.style.color = "green";
          form.reset(); // フォームをリセット
          if (result.redirect) {
            window.location.href = result.redirect;
          }
        } else {
          messageDiv.textContent = result.message || "エラーが発生しました。";
          messageDiv.style.color = "red";
        }
      } catch (error) {
        console.error("通信エラー:", error);
        messageDiv.textContent = `通信エラー: ${error.message}`;
        messageDiv.style.color = "red";
      }
    });
  });
});
