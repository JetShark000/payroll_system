<?php
session_start();
include 'db.php';

// Check if user is logged in and is an employee
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Get selected month or default to current month
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Fetch payroll record for selected month
$stmt = $conn->prepare("SELECT * FROM payroll WHERE user_id = ? AND month = ?");
$stmt->bind_param("is", $user_id, $selected_month);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Payroll Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f2f2f2; }
        .container { max-width: 500px; margin: 40px auto; }
    </style>
</head>
<body>
<div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-center text-primary">Your Payslip</h2>
    <form method="post" class="mb-4">
        <div class="input-group">
            <input type="month" name="month" value="<?php echo htmlspecialchars($selected_month); ?>" class="form-control" required>
            <button type="submit" class="btn btn-primary">View Payslip</button>
        </div>
    </form>
<?php
if ($result->num_rows === 1) {
    $payroll = $result->fetch_assoc();
    ?>
    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($payroll['name']); ?></li>
        <li class="list-group-item"><strong>Monthly Salary:</strong> £<?php echo number_format($payroll['monthly_salary'], 2); ?></li>
        <li class="list-group-item"><strong>Overtime:</strong> £<?php echo number_format($payroll['overtime'], 2); ?></li>
        <li class="list-group-item"><strong>Deductions:</strong> £<?php echo number_format($payroll['deductions'], 2); ?></li>
        <li class="list-group-item"><strong>Income Tax:</strong> £<?php echo number_format($payroll['income_tax'], 2); ?></li>
        <li class="list-group-item"><strong>Employee NI:</strong> £<?php echo number_format($payroll['employee_ni'], 2); ?></li>
        <li class="list-group-item"><strong>Employer NI:</strong> £<?php echo number_format($payroll['employer_ni'], 2); ?></li>
        <li class="list-group-item"><strong>NI Category:</strong> <?php echo htmlspecialchars($payroll['ni_category']); ?></li>
        <li class="list-group-item"><strong>Role:</strong> <?php echo htmlspecialchars($payroll['role']); ?></li>
        <li class="list-group-item"><strong>Net Pay:</strong> £<?php
            $net_pay = $payroll['monthly_salary'] + $payroll['overtime'] - $payroll['deductions'] - $payroll['income_tax'] - $payroll['employee_ni'];
            echo number_format($net_pay, 2);
        ?></li>
    </ul>
    <form method="post" action="generate_payslip.php" target="_blank">
        <input type="hidden" name="employee_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="month" value="<?php echo htmlspecialchars($selected_month); ?>">
        <button type="submit" class="btn btn-success w-100">Download Payslip (PDF)</button>
    </form>
<?php
} else {
    echo "<div class='alert alert-warning text-center'>No payroll record found for this month.</div>";
}
?>
    <a class="btn btn-outline-danger w-100 mt-3" href="logout.php">Logout</a>
</div>
</body>
</html>
