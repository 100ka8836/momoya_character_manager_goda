// 色を淡くする関数
function lightenColor(hex, percent) {
  const num = parseInt(hex.slice(1), 16);
  const r = (num >> 16) + Math.floor(((255 - (num >> 16)) * percent) / 100);
  const g =
    ((num >> 8) & 0x00ff) +
    Math.floor(((255 - ((num >> 8) & 0x00ff)) * percent) / 100);
  const b =
    (num & 0x0000ff) + Math.floor(((255 - (num & 0x0000ff)) * percent) / 100);
  return `rgb(${Math.min(255, r)}, ${Math.min(255, g)}, ${Math.min(255, b)})`;
}

document.addEventListener("DOMContentLoaded", () => {
  // 値の背景色を淡くする
  document.querySelectorAll("td[data-color]").forEach((cell) => {
    const colorCode = cell.getAttribute("data-color");
    if (colorCode) {
      const lightenedColor = lightenColor(colorCode, 80); // 80%淡くする
      cell.style.backgroundColor = lightenedColor; // 値の背景色を設定
    }
  });

  // キャラクター名（<th>）の背景色は変更しないので何もしない
});
