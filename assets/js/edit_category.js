function editCategory(categoryId) {
  const categoryNameCell = document.getElementById(
    `category-name-${categoryId}`
  );
  const currentName = categoryNameCell.innerText.trim();

  const form = document.createElement("form");
  form.method = "POST";
  form.action = "edit_category.php";

  const input = document.createElement("input");
  input.type = "text";
  input.name = "new_category_name";
  input.value = currentName;
  input.required = true;

  const hiddenId = document.createElement("input");
  hiddenId.type = "hidden";
  hiddenId.name = "category_id";
  hiddenId.value = categoryId;

  const hiddenGroup = document.createElement("input");
  hiddenGroup.type = "hidden";
  hiddenGroup.name = "group_id";
  hiddenGroup.value = document.querySelector('input[name="group_id"]').value;

  const submitButton = document.createElement("button");
  submitButton.type = "submit";
  submitButton.innerText = "保存";

  form.appendChild(input);
  form.appendChild(hiddenId);
  form.appendChild(hiddenGroup);
  form.appendChild(submitButton);

  categoryNameCell.innerHTML = "";
  categoryNameCell.appendChild(form);
}
