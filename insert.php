<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $month = $_POST['month'];
    $amount = $_POST['amount'];
    $note = $_POST['note'];

    if (empty($month) || empty($amount)) {
        $error = "Month and amount are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO salaries (user_id, month, amount, note) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $user_id, $month, $amount, $note);

        if ($stmt->execute()) {
            $success = "Payslip added successfully.";
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Payslip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .insert-container { max-width: 400px; margin: 60px auto; }
    </style>
</head>
<body>
<div class="insert-container bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-center text-primary">Add Payslip</h2>
    <?php
    if (isset($error)) echo "<div class='alert alert-danger'>$error</div>";
    if (isset($success)) echo "<div class='alert alert-success'>$success</div>";
    ?>
    <form method="post">
        <div class="mb-3">
            <label for="month" class="form-label">Month</label>
            <input type="month" name="month" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" name="amount" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea name="note" rows="3" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
    <a class="btn btn-outline-danger w-100 mt-3" href="logout.php">Logout</a>
</div>
</body>
</html>
