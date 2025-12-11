<?php
session_start();
require_once 'db_connect.php';

$query = "
    SELECT u.email, u.user_role, u.is_active, c.name, c.address, c.phone
    FROM app_user_login u
    JOIN customer c ON u.customer_id = c.customer_id
    ORDER BY c.name ASC
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Users</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
    <body>

        <div class="container" id="list">
            <h2>All Users</h2>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Role</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($user["name"]) ?></td>
                            <td><?= htmlspecialchars($user["email"]) ?></td>
                            <td><?= htmlspecialchars($user["phone"]) ?></td>
                            <td><?= htmlspecialchars($user["address"]) ?></td>
                            <td><?= htmlspecialchars($user["user_role"]) ?></td>
                            <td><?= $user["is_active"] ? "Yes" : "No" ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="admin_hub.php">Back</a>
        </div>

    </body>
</html>
