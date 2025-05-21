<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Payslips</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .container { max-width: 900px; margin: 40px auto; }
    </style>
</head>
<body>
<div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-primary">View Payslips</h2>

<?php
if ($role === 'admin') {
    // Admin view: Optionally filter by employee ID
    echo '<form method="get" class="row g-3 mb-4">';
    echo '<div class="col-auto"><label for="employee_id" class="col-form-label">Filter by Employee ID:</label></div>';
    echo '<div class="col-auto"><input type="text" name="employee_id" id="employee_id" class="form-control" value="' . (isset($_GET['employee_id']) ? htmlspecialchars($_GET['employee_id']) : '') . '"></div>';
    echo '<div class="col-auto"><button type="submit" class="btn btn-primary">Filter</button></div>';
    echo '</form>';

    // Prepare SQL query
    if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
        $filter_id = $_GET['employee_id'];
        $stmt = $conn->prepare("SELECT s.*, u.name FROM salaries s JOIN users u ON s.user_id = u.id WHERE s.user_id = ? ORDER BY s.month DESC");
        $stmt->bind_param("i", $filter_id);
    } else {
        $stmt = $conn->prepare("SELECT s.*, u.name FROM salaries s JOIN users u ON s.user_id = u.id ORDER BY s.month DESC");
    }
} else {
    // Employee view: Show only their own payslips
    $stmt = $conn->prepare("SELECT s.*, u.name FROM salaries s JOIN users u ON s.user_id = u.id WHERE s.user_id = ? ORDER BY s.month DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<div class="table-responsive"><table class="table table-striped table-hover align-middle">';
    echo '<thead class="table-primary"><tr>';
    if ($role === 'admin') {
        echo '<th>Employee Name</th>';
    }
    echo '<th>Month</th><th>Amount</th><th>Note</th></tr></thead><tbody>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        if ($role === 'admin') {
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        }
        echo '<td>' . htmlspecialchars($row['month']) . '</td>';
        echo '<td>£' . htmlspecialchars(number_format($row['amount'], 2)) . '</td>';
        echo '<td>' . htmlspecialchars($row['note']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo '<div class="alert alert-warning">No payslips found.</div>';
}

$stmt->close();
$conn->close();
?>
    <a class="btn btn-outline-danger w-100 mt-3" href="logout.php">Logout</a>
</div>
</body>
</html>
