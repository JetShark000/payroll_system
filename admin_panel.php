<?php
session_start();
include 'db.php';
include 'index.php'; // for calculation functions

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Payroll creation logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payroll'])) {
    $employee_id = $_POST['employee_id'];
    $month = $_POST['month'];
    $monthly_salary = $_POST['monthly_salary'];
    $ni_category = $_POST['ni_category'];
    $overtime = isset($_POST['overtime']) ? floatval($_POST['overtime']) : 0;
    $deductions = isset($_POST['deductions']) ? floatval($_POST['deductions']) : 0;

    // Fetch employee name and role
    $stmt = $conn->prepare("SELECT name, role, ni_no FROM users WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $payroll_error = "Employee not found.";
    } else {
        $name = $user['name'];
        $role = $user['role'];
        $ni_no = $user['ni_no']; // hashed NI number

        // Calculate gross and payroll
        $gross = $monthly_salary + $overtime - $deductions;
        $income_tax = calculateMonthlyTax($gross);
        $employee_ni = calculateMonthlyEmployeeNI($gross, $ni_category);
        $employer_ni = calculateMonthlyEmployerNI($gross, $ni_category);

        // Check for duplicate
        $check = $conn->prepare("SELECT id FROM payroll WHERE user_id = ? AND month = ?");
        $check->bind_param("is", $employee_id, $month);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $payroll_error = "A payroll record for this employee and month already exists.";
            $check->close();
        } else {
            $check->close();
            // Insert into payroll table
            $stmt = $conn->prepare("INSERT INTO payroll 
                (user_id, name, monthly_salary, ni_category, income_tax, employee_ni, employer_ni, role, overtime, deductions, month, ni_no)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "isdssssdddss",
                $employee_id,
                $name,
                $monthly_salary,
                $ni_category,
                $income_tax,
                $employee_ni,
                $employer_ni,
                $role,
                $overtime,
                $deductions,
                $month,
                $ni_no
            );

            if ($stmt->execute()) {
                $payroll_success = "Payroll record created successfully.";
            } else {
                $payroll_error = "Failed to create payroll record: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Delete payroll record logic
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM payroll WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_panel.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Payroll Admin</a>
        <div class="d-flex">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container">

    <?php if (isset($payroll_error)): ?>
        <div class="alert alert-danger"><?php echo $payroll_error; ?></div>
    <?php endif; ?>
    <?php if (isset($payroll_success)): ?>
        <div class="alert alert-success"><?php echo $payroll_success; ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-5">
        <!-- Create User -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">Create User</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>User ID</label>
                            <input type="number" name="new_user_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>NI Number</label>
                            <input type="text" name="ni_no" class="form-control" required>
                        </div>
                        <button type="submit" name="create_user" class="btn btn-info w-100 text-white">Create User</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generate Payslip -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">Generate Payslip</div>
                <div class="card-body">
                    <form method="post" action="generate_payslip.php" target="_blank">
                        <div class="mb-3">
                            <label>Employee ID</label>
                            <input type="number" name="employee_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Month</label>
                            <input type="month" name="month" class="form-control" required>
                        </div>
                        <button type="submit" name="generate_payslip" class="btn btn-secondary w-100">Generate Payslip PDF</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Payroll Record -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Create Payroll Record</div>
                <div class="card-body">
                    <form method="post" action="admin_panel.php">
                        <div class="mb-3">
                            <label>Employee ID</label>
                            <?php
                            $employees = $conn->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name ASC");
                            ?>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                <?php while ($emp = $employees->fetch_assoc()): ?>
                                    <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['name']) . " (ID: {$emp['id']})"; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Month</label>
                            <input type="month" name="month" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Monthly Salary</label>
                            <input type="number" name="monthly_salary" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Overtime (£)</label>
                            <input type="number" name="overtime" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="mb-3">
                            <label>Deductions (£)</label>
                            <input type="number" name="deductions" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="mb-3">
                            <label>NI Category</label>
                            <select name="ni_category" class="form-select" required>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="D">D</option>
                                <option value="H">H</option>
                                <option value="M">M</option>
                                <option value="N">N</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                        <button type="submit" name="create_payroll" class="btn btn-success w-100">Create Payroll Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3>Existing Payroll Records</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Employee</th>
                    <th>Month</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            if ($search !== '') {
                $stmt = $conn->prepare("SELECT p.id, u.name, p.month, p.monthly_salary FROM payroll p JOIN users u ON p.user_id = u.id WHERE u.name LIKE ? OR u.id = ? ORDER BY p.month DESC");
                $like = "%$search%";
                $stmt->bind_param("si", $like, $search);
                $stmt->execute();
                $records = $stmt->get_result();
                $stmt->close();
            } else {
                $records = $conn->query("SELECT p.id, u.name, p.month, p.monthly_salary FROM payroll p JOIN users u ON p.user_id = u.id ORDER BY p.month DESC");
            }
            while ($rec = $records->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($rec['name']); ?></td>
                    <td><?php echo htmlspecialchars($rec['month']); ?></td>
                    <td>£<?php echo number_format($rec['monthly_salary'], 2); ?></td>
                    <td>
                        <a href="edit_payslip.php?id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_payslip.php?id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <form method="get" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="text" name="search" class="form-control" placeholder="Search employee name or ID" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <?php
if (isset($_POST['create_user'])) {
    $id = $_POST['new_user_id'];
    $name = $_POST['name'];
    $password = $_POST['new_password'];
    $role = $_POST['role'];
    $ni_no = $_POST['ni_no'];

    if (empty($id) || empty($name) || empty($password) || empty($role) || empty($ni_no)) {
        echo "<div class='alert alert-danger mt-3'>All fields are required.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_ni_no = password_hash($ni_no, PASSWORD_DEFAULT); // Hash NI number

        $stmt = $conn->prepare("INSERT INTO users (id, name, password, role, ni_no) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id, $name, $hashed_password, $role, $hashed_ni_no);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-3'>User created successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Error creating user: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>
</div>
</body>
</html>
