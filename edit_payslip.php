<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No payslip ID specified.";
    exit();
}

$payslip_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monthly_salary = isset($_POST['monthly_salary']) ? floatval($_POST['monthly_salary']) : 0;
    $overtime = isset($_POST['overtime']) ? floatval($_POST['overtime']) : 0;
    $deductions = isset($_POST['deductions']) ? floatval($_POST['deductions']) : 0;
    $income_tax = isset($_POST['income_tax']) ? floatval($_POST['income_tax']) : 0;
    $employee_ni = isset($_POST['employee_ni']) ? floatval($_POST['employee_ni']) : 0;
    $employer_ni = isset($_POST['employer_ni']) ? floatval($_POST['employer_ni']) : 0;
    $ni_category = $_POST['ni_category'];
    $role = $_POST['role'];
    $month = $_POST['month'];

    $stmt = $conn->prepare("UPDATE payroll SET monthly_salary=?, overtime=?, deductions=?, income_tax=?, employee_ni=?, employer_ni=?, ni_category=?, role=?, month=? WHERE id=?");
    $stmt->bind_param("ddddddsssi", $monthly_salary, $overtime, $deductions, $income_tax, $employee_ni, $employer_ni, $ni_category, $role, $month, $payslip_id);

    if ($stmt->execute()) {
        $success = "Payslip updated successfully.";
    } else {
        $error = "Failed to update payslip: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch payslip data
$stmt = $conn->prepare("SELECT * FROM payroll WHERE id = ?");
$stmt->bind_param("i", $payslip_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    echo "Payslip not found.";
    exit();
}
$payslip = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Payslip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .edit-container { max-width: 500px; margin: 40px auto; }
    </style>
</head>
<body>
<div class="edit-container bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-center text-primary">Edit Payslip</h2>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>".htmlspecialchars($error)."</div>"; ?>
    <?php if (isset($success)) echo "<div class='alert alert-success'>".htmlspecialchars($success)."</div>"; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Month</label>
            <input type="month" name="month" class="form-control" value="<?php echo htmlspecialchars($payslip['month']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Monthly Salary</label>
            <input type="number" name="monthly_salary" step="0.01" class="form-control" value="<?php echo htmlspecialchars($payslip['monthly_salary']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Overtime (£)</label>
            <input type="number" name="overtime" step="0.01" class="form-control" value="<?php echo htmlspecialchars($payslip['overtime']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Deductions (£)</label>
            <input type="number" name="deductions" step="0.01" class="form-control" value="<?php echo htmlspecialchars($payslip['deductions']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Income Tax (£)</label>
            <input type="number" name="income_tax" step="0.01" class="form-control" value="<?php echo htmlspecialchars($payslip['income_tax']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Employee NI (£)</label>
            <input type="number" name="employee_ni" step="0.01" class="form-control" value="<?php echo htmlspecialchars($payslip['employee_ni']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Employer NI (£)</label>
            <input type="number" name="employer_ni" step="0.01" class="form-control" value="<?php echo htmlspecialchars($payslip['employer_ni']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">NI Category</label>
            <select name="ni_category" class="form-select" required>
                <?php
                $categories = ['A','B','D','H','M','N','V'];
                foreach ($categories as $cat) {
                    $selected = ($payslip['ni_category'] == $cat) ? 'selected' : '';
                    echo "<option value=\"$cat\" $selected>$cat</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" name="role" class="form-control" value="<?php echo htmlspecialchars($payslip['role']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Payslip</button>
    </form>
    <a class="btn btn-outline-secondary w-100 mt-3" href="admin_panel.php">Back to Admin Panel</a>
</div>
</body>
</html>