<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colorCodes = $_POST['color_codes'] ?? [];

    foreach ($colorCodes as $characterId => $colorCode) {
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $colorCode)) {
            $stmt = $pdo->prepare("UPDATE characters SET color_code = ? WHERE id = ?");
            $stmt->execute([$colorCode, $characterId]);
        }
    }

    echo "success";
    exit;
}

echo "error";
?>