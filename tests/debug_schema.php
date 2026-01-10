<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=rkkf', 'root', '');
    $stmt = $pdo->query('SHOW COLUMNS FROM fees');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "FEES TABLE SCHEMA:\n";
    foreach ($columns as $col) {
        echo "Field: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
