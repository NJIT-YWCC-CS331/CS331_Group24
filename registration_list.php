<?php
session_start();
require_once 'db_connect.php';

$query = "SELECT * FROM RENTAL_AGREEMENT ORDER BY Rental_ID ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Rental Agreements</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="container" id="list">
    <h2>All Rental Agreements</h2>

    <table>
        <thead>
            <tr>
                <th>Rental ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Daily Rate</th>
                <th>Total Cost</th>
                <th>Car ID</th>
                <th>Customer ID</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['Rental_ID']) ?></td>
                    <td><?= htmlspecialchars($row['Start_Date']) ?></td>
                    <td><?= htmlspecialchars($row['End_Date']) ?></td>
                    <td><?= htmlspecialchars($row['Daily_Rate']) ?></td>
                    <td><?= htmlspecialchars($row['Total_Cost']) ?></td>
                    <td><?= htmlspecialchars($row['Car_ID']) ?></td>
                    <td><?= htmlspecialchars($row['Customer_ID']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a class="back-link" href="admin_hub.php">Back</a>
</div>

</body>
</html>
