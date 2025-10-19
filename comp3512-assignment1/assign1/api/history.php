<?php
//../
require_once '../config.inc.php';  
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

try {
   // $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
   // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // get sympol element from database to check
    if (!isset($_GET['symbol'])) {
        echo json_encode(["error" => "Missing required parameter (symbol)"]);
        exit;
    }

    $symbol = $_GET['symbol'];

   
    if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
        $sql = "SELECT date, volume, open, close, high, low
                FROM history
                WHERE symbol = :symbol
                  AND date BETWEEN :startDate AND :endDate
                ORDER BY date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':symbol', $symbol);
        $stmt->bindValue(':startDate', $_GET['startDate']);
        $stmt->bindValue(':endDate', $_GET['endDate']);
    } else {
        
        $sql = "SELECT date, volume, open, close, high, low
                FROM history
                WHERE symbol = :symbol
                ORDER BY date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':symbol', $symbol);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
