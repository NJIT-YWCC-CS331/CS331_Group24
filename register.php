<?php
session_start();
require_once 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {
        $check = mysqli_prepare($conn, "SELECT email FROM app_user_login WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);
        $existing = mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($check);

        $result = mysqli_query($conn, "SELECT Customer_ID FROM customer ORDER BY Customer_ID ASC");

        $used = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $used[] = (int)$row["Customer_ID"];
        }

        $customer_id = 1;
        while (in_array($customer_id, $used)) {
            $customer_id++;
        }

        if ($existing) {
            $error = "An account with this email already exists.";
        } else {
            mysqli_begin_transaction($conn);

            try {
                
                $custStmt = mysqli_prepare($conn,
                    "INSERT INTO customer (Customer_ID, Name, Email, Phone)
                    VALUES (?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($custStmt, "isss",
                    $customer_id,
                    $name,
                    $email,
                    $phone
                );
                mysqli_stmt_execute($custStmt);

                if (mysqli_stmt_affected_rows($custStmt) !== 1) {
                    throw new Exception("Customer insert failed.");
                }

                mysqli_stmt_close($custStmt);

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $loginStmt = mysqli_prepare($conn,
                    "INSERT INTO app_user_login (email, customer_id, password_hash, user_role, is_active)
                     VALUES (?, ?, ?, 'user', 1)"
                );
                mysqli_stmt_bind_param($loginStmt, "sis", $email, $customer_id, $hash);
                mysqli_stmt_execute($loginStmt);

                if (mysqli_stmt_affected_rows($loginStmt) !== 1) {
                    throw new Exception("Login insert failed.");
                }
                mysqli_stmt_close($loginStmt);

                mysqli_commit($conn);

                session_regenerate_id(true);
                $_SESSION["user"] = [
                    "email" => $email,
                    "customer_id" => $customer_id,
                    "role" => "user"
                ];

                header("Location: login.php");
                exit;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Registration failed. Check CUSTOMER columns/constraints.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="login-box">
    <h2>Create Account</h2>

    <form method="post" action="register.php">
        Name:
        <input type="text" name="name" required>

        Email:
        <input type="email" name="email" required>

        Phone:
        <input type="text" name="phone" required>

        Password:
        <input type="password" name="password" required>

        <input type="submit" value="Sign Up">
    </form>

    <p>
        Already have an account?
        <a href="login.php">Login</a>
    </p>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>

</body>
</html>
