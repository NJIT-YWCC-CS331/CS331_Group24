<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $car_id = $_POST['car_id'];
    $total_cost = $_POST['total_cost'];
    $payment_method = $_POST['payment_method'];

    $customer_id = $_SESSION['user']['customer_id'] ?? null;
    if (!$customer_id) {
        die("Error: No customer is logged in.");
    }

    function generateUniqueID($conn, $table, $column) {
        do {
            $id = rand(1000, 9999);
            $check = mysqli_query($conn, "SELECT $column FROM $table WHERE $column = $id");
        } while (mysqli_num_rows($check) > 0);
        return $id;
    }

    $update_car = "UPDATE CAR SET Rental_Status = 'RENTED' WHERE Car_ID = $car_id";
    if (!mysqli_query($conn, $update_car)) {
        die("Failed to update car status: " . mysqli_error($conn));
    }

    $rental_id = generateUniqueID($conn, "RENTAL_AGREEMENT", "Rental_ID");

    $days = (strtotime($end_date) - strtotime($start_date)) / 86400;
    $daily_rate = $days > 0 ? ($total_cost-50) / $days : 0;

    $insert_rental = "
        INSERT INTO RENTAL_AGREEMENT (Rental_ID, Start_Date, End_Date, Daily_Rate, Total_Cost, Car_ID, Customer_ID)
        VALUES (
            $rental_id,
            '$start_date',
            '$end_date',
            ($daily_rate),
            $total_cost,
            $car_id,
            $customer_id
        )
    ";

    if (!mysqli_query($conn, $insert_rental)) {
        die("Failed to insert rental agreement: " . mysqli_error($conn));
    }

    $payment_id = generateUniqueID($conn, "PAYMENT", "Payment_ID");

    $insert_payment = "
        INSERT INTO PAYMENT (Payment_ID, Rental_ID, Payment_Date, Method, Amount)
        VALUES (
            $payment_id,
            $rental_id,
            '$end_date',
            '$payment_method',
            $total_cost
        )
    ";

    if (!mysqli_query($conn, $insert_payment)) {
        die("Failed to insert payment: " . mysqli_error($conn));
    }

    header("Location: user_hub.php");
    exit();
}

// Fetch cars for dropdown and compute daily rates
$query = "SELECT Car_ID, Brand, Model, Category, Rental_Status FROM CAR ORDER BY Brand, Model";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

$cars = [];
$daily_rates = [];

while ($car = mysqli_fetch_assoc($result)) {
    $cars[] = $car;

    $car_id = $car['Car_ID'];
    $rate_query = "SELECT Daily_Rate FROM RENTAL_AGREEMENT WHERE Car_ID = $car_id ORDER BY Rental_ID DESC LIMIT 1";
    $rate_result = mysqli_query($conn, $rate_query);

    if ($rate_result && mysqli_num_rows($rate_result) > 0) {
        $row = mysqli_fetch_assoc($rate_result);
        $daily_rates[$car_id] = floatval($row['Daily_Rate']);
    } else {
        $daily_rates[$car_id] = rand(2000, 5000) / 100; // Random $20-$50
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Rental Request</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>

<div class="container" id="list">
    <h2>Request a Car Rental</h2>
    <form method="POST">

        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>

        <div style="padding-top: 5px;"></div>

        <div class="form-group">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
        </div>

        <div style="padding-top: 5px;"></div>

        <div class="form-group">
            <label for="car_id">Choose a Car:</label>
            <select id="car_id" name="car_id" required>
                <option value="">Select a car</option>

                <?php foreach ($cars as $car) : 
                    $car_id = $car["Car_ID"];
                    $car_name = "{$car['Brand']} {$car['Model']} (ID: {$car_id})";
                    $status = $car["Rental_Status"];
                    $available = !($status === "RENTED" || $status === "UNDER MAINTENANCE");
                ?>
                    <option 
                        value="<?= $available ? $car_id : '' ?>"
                        <?= $available ? '' : 'disabled' ?>
                        data-dailyrate="<?= $daily_rates[$car_id] ?? 0 ?>"
                    >
                        <?= htmlspecialchars($car_name) ?>
                        <?= $available ? '' : " - $status (Unavailable)" ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div style="padding-top: 5px;"></div>

        <div class="form-group">
            <label>Total Cost:</label>
            <div id="total_cost_box">$0.00</div>
            <input type="hidden" id="total_cost" name="total_cost" value="0">
        </div>

        <div style="padding-top: 5px;"></div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">Select payment method</option>
                <option value="cash">Cash</option>
                <option value="online transfer">Online Transfer</option>
                <option value="credit card">Credit Card</option>
            </select>
        </div>

        <div style="padding-top: 5px;"></div>

        <button type="submit" class="btn">Submit Request</button>

    </form>

    <br><br>
    <a class="back-link" href="user_hub.php">Back</a>

</div>

<script>
function calculateTotal() {
    const start = document.getElementById("start_date").value;
    const endDate = document.getElementById("end_date").value;
    const carSelect = document.getElementById("car_id");
    const selected = carSelect.options[carSelect.selectedIndex];

    if (!start || !endDate || !selected.value) {
        document.getElementById("total_cost_box").innerText = "$0.00";
        document.getElementById("total_cost").value = 0;
        return;
    }

    const rate = parseFloat(selected.dataset.dailyrate || 0);
    const startDateObj = new Date(start);
    const endDateObj = new Date(endDate);
    const diffTime = endDateObj - startDateObj;
    const days = diffTime / (1000 * 60 * 60 * 24);

    if (days <= 0) {
        document.getElementById("total_cost_box").innerText = "$0.00";
        document.getElementById("total_cost").value = 0;
        return;
    }

    const total = (50+(days * rate)).toFixed(2);

    document.getElementById("total_cost_box").innerText = "$" + total;
    document.getElementById("total_cost").value = total;
}

document.getElementById("start_date").addEventListener("change", calculateTotal);
document.getElementById("end_date").addEventListener("change", calculateTotal);
document.getElementById("car_id").addEventListener("change", calculateTotal);
</script>

</body>
</html>
