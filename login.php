<?php
session_start();
require_once 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["userName"] ?? "");
    $password = $_POST["password"] ?? "";
    $loginType = $_POST["login_type"] ?? ""; // Admin or User

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT email, customer_id, password_hash, user_role, is_active
             FROM app_user_login
             WHERE email = ?"
        );
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            $error = "Invalid credentials.";
        } elseif ((int)$user["is_active"] !== 1) {
            $error = "Account disabled.";
        } elseif (!password_verify($password, $user["password_hash"])) {
            $error = "Invalid credentials.";
        } else {
            if ($loginType === "Admin" && $user["user_role"] !== "admin") {
                $error = "You are not an admin.";
            } else {
                $_SESSION["user"] = [
                    "email" => $user["email"],
                    "customer_id" => $user["customer_id"],
                    "role" => $user["user_role"]
                ];

                // Redirect based on role or button
                if ($user["user_role"] === "admin") {
                    header("Location: admin_hub.php");
                } else {
                    header("Location: user_hub.php");
                }
                exit;
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="login-box">
    <form method="post" action="login.php">
        <h2>Car Rental System Login</h2>
        Email:
        <input type="text" name="userName" required>

        Password:
        <input type="password" name="password" required>

        <input type="submit" name="login_type" value="Admin">
        <div style="padding-top: 5px;"></div>
        <input type="submit" name="login_type" value="User">
    </form>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <p>
  New here?
  <a href="register.php">Create an account</a>
</p>

</div>

</body>
</html>
