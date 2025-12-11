<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user"]["customer_id"];

$stmt = mysqli_prepare($conn,
    "SELECT c.Customer_ID, c.Name, c.Address, c.Phone, c.Email, c.Date_of_Birth,
            u.email AS login_email, u.user_role, u.is_active
     FROM customer c
     JOIN app_user_login u ON c.Customer_ID = u.customer_id
     WHERE c.Customer_ID = ?"
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Could not fetch your profile information.");
}

mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Profile</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="container" id="list">
    <h2>Your Profile Information</h2>

    <table>
        <thead>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Customer ID</td>
                <td><?= htmlspecialchars($user['Customer_ID']) ?></td>
            </tr>
            <tr>
                <td>Name</td>
                <td><?= htmlspecialchars($user['Name']) ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?= htmlspecialchars($user['Email']) ?></td>
            </tr>
            <tr>
                <td>Phone</td>
                <td><?= htmlspecialchars($user['Phone']) ?></td>
            </tr>
            <tr>
                <td>Address</td>
                <td><?= htmlspecialchars($user['Address']) ?></td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td><?= htmlspecialchars($user['Date_of_Birth']) ?></td>
            </tr>
            <tr>
                <td>User Role</td>
                <td><?= htmlspecialchars($user['user_role']) ?></td>
            </tr>
            <tr>
                <td>Account Status</td>
                <td><?= $user['is_active'] ? "Active" : "Disabled" ?></td>
            </tr>
        </tbody>
    </table>

    <a class="back-link" href="user_hub.php">Back</a>
</div>

</body>
</html>
