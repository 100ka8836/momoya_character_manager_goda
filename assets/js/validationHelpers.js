// assets/js/validationHelpers.js

export function isValidValue(value) {
  // 必須チェック
  return value !== null && value.trim() !== "";
}

export function sanitizeInput(value) {
  // 入力値をサニタイズ（必要に応じて処理を追加）
  return value.replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
