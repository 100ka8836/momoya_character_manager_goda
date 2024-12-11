<?php
require_once __DIR__ . '/../vendor/autoload.php';


use Dotenv\Dotenv;

// .env ファイルを読み込む
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8';
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log('データベース接続エラー: ' . $e->getMessage());
    die('データベース接続エラーが発生しました。管理者に連絡してください。');
}

// 共通エラーレスポンス関数
if (!function_exists('sendErrorResponse')) {
    function sendErrorResponse($message, $statusCode = 500)
    {
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}