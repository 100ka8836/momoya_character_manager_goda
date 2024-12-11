document.addEventListener("DOMContentLoaded", () => {
  const groupNames = document.querySelectorAll(".group-name");
  const modal = document.getElementById("password-modal");
  const modalClose = document.getElementById("modal-close");
  const groupIdInput = document.getElementById("group-id");
  const passwordForm = document.getElementById("password-form");
  const errorMessage = document.getElementById("error-message");

  groupNames.forEach((groupName) => {
    groupName.addEventListener("click", () => {
      const groupId = groupName.dataset.groupId;
      groupIdInput.value = groupId;
      modal.style.display = "flex";
    });
  });

  modalClose.addEventListener("click", () => {
    modal.style.display = "none";
    errorMessage.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
      errorMessage.style.display = "none";
    }
  });

  passwordForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(passwordForm);

    try {
      const response = await fetch("group_password_handler.php", {
        method: "POST",
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        window.location.href = result.redirect;
      } else {
        errorMessage.textContent = result.message;
        errorMessage.style.display = "block";
      }
    } catch (error) {
      console.error("通信エラー:", error);
      errorMessage.textContent = "通信エラーが発生しました。";
      errorMessage.style.display = "block";
    }
  });
});
