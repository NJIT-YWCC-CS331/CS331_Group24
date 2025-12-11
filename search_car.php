<?php
session_start();
require_once 'db_connect.php';

$query = "SELECT * FROM CAR ORDER BY Car_ID ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Cars</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="container" id="list">
    <h2>All Cars</h2>

    <table>
        <thead>
            <tr>
                <th>Car ID</th>
                <th>License Plate</th>
                <th>Year</th>
                <th>Rental Status</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Branch ID</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['Car_ID']) ?></td>
                    <td><?= htmlspecialchars($row['License_Plate_Number']) ?></td>
                    <td><?= htmlspecialchars($row['Year_of_Manufacture']) ?></td>
                    <td><?= htmlspecialchars($row['Rental_Status']) ?></td>
                    <td><?= htmlspecialchars($row['Category']) ?></td>
                    <td><?= htmlspecialchars($row['Brand']) ?></td>
                    <td><?= htmlspecialchars($row['Model']) ?></td>
                    <td><?= htmlspecialchars($row['Branch_ID']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="user_hub.php">Back</a>
</div>

</body>
</html>
