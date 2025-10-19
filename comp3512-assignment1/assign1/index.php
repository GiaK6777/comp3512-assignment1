<?php

require_once 'config.inc.php';
$isPortfolioView = isset($_GET['ref']);
try {
    if ($isPortfolioView) {

        $userId = $_GET['ref'];
     
        $sqlUser = "SELECT id, firstName, lastName FROM users WHERE id = :userId";
        $stmt = $pdo->prepare($sqlUser);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die("<h3> Error: No customer found (User ID: " . htmlspecialchars($userId) . ")</h3>");
        }

        $sqlPortfolio = "
            WITH LatestStockPrices AS (
                SELECT h.symbol, h.close AS latestClose
                FROM history h
                INNER JOIN (
                    SELECT symbol, MAX(date) AS maxDate
                    FROM history
                    GROUP BY symbol
                ) sub ON h.symbol = sub.symbol AND h.date = sub.maxDate
            )
            SELECT p.symbol, c.name AS companyName, c.sector, p.amount,
                   l.latestClose, (p.amount * l.latestClose) AS stockValue
            FROM portfolio p
            JOIN companies c ON p.symbol = c.symbol
            JOIN LatestStockPrices l ON p.symbol = l.symbol
            WHERE p.userId = :userId";
        $stmtPortfolio = $pdo->prepare($sqlPortfolio);
        $stmtPortfolio->bindValue(':userId', $userId);
        $stmtPortfolio->execute();
        $portfolioDetails = $stmtPortfolio->fetchAll(PDO::FETCH_ASSOC);
     
        $sqlSummary = "
            SELECT 
                COUNT(p.symbol) AS totalCompanies,
                SUM(p.amount) AS totalShares,
                SUM(p.amount * l.latestClose) AS totalValue
            FROM portfolio p
            JOIN companies c ON p.symbol = c.symbol
            INNER JOIN (
                SELECT h.symbol, h.close AS latestClose
                FROM history h
                INNER JOIN (
                    SELECT symbol, MAX(date) AS maxDate
                    FROM history
                    GROUP BY symbol
                ) sub ON h.symbol = sub.symbol AND h.date = sub.maxDate
            ) l ON p.symbol = l.symbol
            WHERE p.userId = :userId";
        $stmtSummary = $pdo->prepare($sqlSummary);
        $stmtSummary->bindValue(':userId', $userId);
        $stmtSummary->execute();
        $portfolioSummary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

    } 
    
   
    $sql = "SELECT id, firstName, lastName FROM users ORDER BY lastName ASC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        $message = "No customer in database";
    }

} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Portfolio Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <link rel="stylesheet" href="style.css"> 
    <style>

        .summary-stats .statistic {
            margin-right: 1.5em !important;
        }
        .summary-stats .statistic .value {
            font-size: 1.8em !important; 
        }
        .summary-stats .segment {
            padding: 15px !important;
            border-radius: 6px !important;
            background-color: #f7f9fb;
            box-shadow: none;
            border: 1px solid #e0e0e0;
        }
        
        .twelve.wide.column .ui.dividing.header,
        .twelve.wide.column .ui.header.section-header {
            text-align: center !important;
        }
    </style>
</head>
<body>
    <?php include 'header.inc.php'; ?>

<div class="ui container">
    <div class="ui stackable grid">
        
        <div class="four wide column">
            <h2 class="ui dividing header">Customers List</h2> 
            <p>Select a customer's portfolio.</p>

            <?php if (isset($message)): ?>
                <div class="ui info message"><?= $message ?></div>
            <?php else: ?>
                <table class="ui celled selectable table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th> </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($u['lastname'] . ', ' . $u['firstname']) ?>
                                </td>
                                <td>
                                    <a class="ui mini blue button"
                                       href="index.php?ref=<?= urlencode($u['id']) ?>">
                                       Portfolio
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="twelve wide column">
            
            <?php if ($isPortfolioView): ?>
                
                <h2 class="ui dividing header">
                    Portfolio for: <?= htmlspecialchars($user['lastname'] . ', ' . $user['firstname']) ?> </h2>

                <h3 class="ui header section-header">Portfolio Summary</h3>
                
                <div class="ui three column stackable grid summary-stats" style="margin-bottom: 3em;">
                    <div class="column">
                        <div class="ui segment center aligned">
                            <div class="ui statistic">
                                <div class="value"><?= htmlspecialchars($portfolioSummary['totalCompanies']) ?></div>
                                <div class="label">Companies</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column">
                        <div class="ui segment center aligned">
                            <div class="ui statistic">
                                <div class="value"><?= htmlspecialchars(number_format($portfolioSummary['totalShares'])) ?></div>
                                <div class="label"># Shares</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column">
                        <div class="ui segment center aligned">
                            <div class="ui statistic">
                                <div class="value">$<?= htmlspecialchars(number_format($portfolioSummary['totalValue'], 2)) ?></div>
                                <div class="label">Total Value</div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="ui header section-header">Details</h3>
                <table class="ui celled striped table">
                    <thead>
                        <tr>
                            <th>Symbol</th>
                            <th>Name</th>
                            <th>Sector</th>
                            <th>Amount</th>
                            <th>Latest Close</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portfolioDetails as $holding): ?>
                            <tr>
                                <td>
                                    <a href="company.php?symbol=<?= urlencode($holding['symbol']) ?>">
                                        <?= htmlspecialchars($holding['symbol']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($holding['companyName']) ?></td>
                                <td><?= htmlspecialchars($holding['sector']) ?></td>
                                <td><?= htmlspecialchars($holding['amount']) ?></td>
                                <td>$<?= number_format($holding['latestClose'], 2) ?></td>
                                <td>$<?= number_format($holding['stockValue'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <a href="index.php" class="ui button" style="margin-top: 2em;">
                    <i class="left arrow icon"></i> Back to the intital page
                </a>

            <?php else: ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="user icon"></i>
                        Pease select a customer to view their investment portfolio details.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>
</body>
</html>
