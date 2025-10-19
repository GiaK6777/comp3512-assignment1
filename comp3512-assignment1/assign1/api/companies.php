<?php
require_once '../config.inc.php';  // go up one level since api/ is a subfolder
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

try {
    //$pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // If a specific company symbol is requested
    if (isset($_GET['symbol'])) {
        $sql = "SELECT 
                    symbol, name, sector, subindustry, address, exchange, website, description,
                    latitude, longitude, financials
                FROM companies
                WHERE symbol = :symbol";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':symbol', $_GET['symbol']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } 
    // Otherwise return all companies
    else {
        $sql = "SELECT symbol, name, sector FROM companies ORDER BY name ASC";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
