document.addEventListener("DOMContentLoaded", () => {
  const tables = document.querySelectorAll("table[id='sortable-table']");

  if (!tables) return;

  tables.forEach((table) => {
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    let sortOrder = "asc"; // 初期状態は昇順

    rows.forEach((row) => {
      const firstCell = row.querySelector("td:first-child");

      if (!firstCell) return;

      firstCell.addEventListener("click", () => {
        const cells = Array.from(row.querySelectorAll("td")).slice(1, -1);

        const columnData = cells.map((cell, index) => {
          // 数値部分のみを取得
          const spanValue = cell
            .querySelector(".value-display")
            .textContent.trim();

          const value = isNaN(spanValue)
            ? normalizeText(spanValue) // 文字列はそのまま
            : zeroPad(parseFloat(spanValue)); // 数値はゼロ埋め

          console.log(`Cell Value: ${spanValue}, Processed Value: ${value}`); // デバッグ用ログ

          return {
            index: index + 1,
            value: value
          };
        });

        // ソート処理
        columnData.sort((a, b) => {
          if (sortOrder === "asc") {
            return a.value.localeCompare(b.value); // 文字列として比較
          } else {
            return b.value.localeCompare(a.value);
          }
        });

        sortOrder = sortOrder === "asc" ? "desc" : "asc";

        // 新しい順序でセルを再配置
        const newOrder = columnData.map((data) => data.index);
        console.log("New Order:", newOrder); // 確認用ログ

        const headerRow = table.querySelector("thead tr");
        const allRows = [headerRow, ...rows];

        allRows.forEach((tr) => {
          const cells = Array.from(tr.children);
          const reorderedCells = [
            cells[0],
            ...newOrder.map((i) => cells[i]),
            cells[cells.length - 1]
          ];
          reorderedCells.forEach((cell, i) => tr.appendChild(cell));
        });
      });
    });
  });

  function normalizeText(text) {
    return text.toLocaleLowerCase().normalize("NFKC");
  }

  function zeroPad(num) {
    return num < 10 ? `0${num}` : `${num}`;
  }
});
