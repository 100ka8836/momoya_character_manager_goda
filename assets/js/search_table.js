document.addEventListener("DOMContentLoaded", () => {
  const tables = document.querySelectorAll("table");

  tables.forEach((table) => {
    // 検索ボックスが存在する場合のみ処理を行う
    const searchInput = table
      .closest(".tab-content")
      ?.querySelector(".column-search");

    if (!searchInput) {
      return; // 検索ボックスがない場合は処理をスキップ
    }

    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    searchInput.addEventListener("input", () => {
      const query = searchInput.value.trim().toLowerCase();

      // 検索結果をヒットする順に並び替え
      const sortedRows = rows.sort((a, b) => {
        const aText = a.textContent.toLowerCase();
        const bText = b.textContent.toLowerCase();
        const aIncludes = aText.includes(query) ? 0 : 1; // ヒットする行は 0
        const bIncludes = bText.includes(query) ? 0 : 1; // ヒットしない行は 1
        return aIncludes - bIncludes;
      });

      // 並び替えた行を再挿入
      sortedRows.forEach((row) => tbody.appendChild(row));
    });
  });
});
