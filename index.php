<?php
function calculateMonthlyTax($monthlyGross) {
    $annual = $monthlyGross * 12;
    $tax = 0;

    if ($annual > 125140) {
        $tax += ($annual - 125140) * 0.45;
        $annual = 125140;
    }
    if ($annual > 50270) {
        $tax += ($annual - 50270) * 0.40;
        $annual = 50270;
    }
    if ($annual > 12570) {
        $tax += ($annual - 12570) * 0.20;
    }

    return round($tax / 12, 2);
}

function calculateMonthlyEmployeeNI($monthlyGross, $category = 'A') {
    $weekly = ($monthlyGross * 12) / 52;
    $ni = 0;

    switch ($category) {
        case 'A':
        case 'F':
        case 'H':
        case 'M':
        case 'N':
        case 'V':
            if ($weekly > 967) {
                $ni += (967 - 242) * 0.08;
                $ni += ($weekly - 967) * 0.02;
            } elseif ($weekly > 242) {
                $ni += ($weekly - 242) * 0.08;
            }
            break;
        default:
            // Basic fallback
            if ($weekly > 967) {
                $ni += ($weekly - 967) * 0.02;
            }
    }

    return round($ni * 52 / 12, 2);
}

function calculateMonthlyEmployerNI($monthlyGross, $category = 'A') {
    $weekly = ($monthlyGross * 12) / 52;
    if ($weekly > 481) {
        return round(($weekly * 0.15) * 52 / 12, 2);
    }
    return 0;
}
?>

<!-- Payroll calcualtor that started it all  -->
<!-- <!DOCTYPE html>
<html>
<head>
    <title>Monthly Payroll Calculator (UK)</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        input, select { margin: 5px; padding: 5px; }
        h2 { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Employee Payroll Calculator</h1>
    <form method="post" action="insert.php">
        <label>Employee Name:</label>
        <input type="text" name="name" required><br><br>

        <label>Monthly Gross Salary (£):</label>
        <input type="number" name="monthly_salary" step="0.01" required><br><br>

        <label>NI Category:</label>
        <select name="ni_category">
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="D">D</option>
            <option value="H">H</option>
            <option value="M">M</option>
            <option value="N">N</option>
            <option value="V">V</option>
        </select><br><br>

        <input type="submit" value="Calculate">
    </form> -->

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST['name'], $_POST['monthly_salary'], $_POST['ni_category'])) {
    $name = $_POST['name'];
    $monthlySalary = $_POST['monthly_salary'];
    $category = $_POST['ni_category'];

    if ($monthlySalary < 500) {
        echo "<p style='color:red'>Warning: Salary seems too low. Please check input.</p>";
    }

    $monthlyTax = calculateMonthlyTax($monthlySalary);
    $employeeNI = calculateMonthlyEmployeeNI($monthlySalary, $category);
    $employerNI = calculateMonthlyEmployerNI($monthlySalary, $category);
    $netPay = $monthlySalary - $monthlyTax - $employeeNI;

    $annualGross = $monthlySalary * 12;
    $annualNet = $netPay * 12;

    echo "<h2>Payroll Summary for $name</h2>";
    echo "Gross Monthly Salary: £" . number_format($monthlySalary, 2) . "<br>";
    echo "Income Tax (PAYE): £" . number_format($monthlyTax, 2) . "<br>";
    echo "Employee NI: £" . number_format($employeeNI, 2) . "<br>";
    echo "<strong>Net Monthly Pay: £" . number_format($netPay, 2) . "</strong><br><br>";
    echo "Employer NI Contribution: £" . number_format($employerNI, 2) . "<br><br>";

    echo "<strong>Annual Gross: £" . number_format($annualGross, 2) . "</strong><br>";
    echo "<strong>Annual Net: £" . number_format($annualNet, 2) . "</strong><br>";

    // Optional logging
    $log = "$name, £$monthlySalary, £$monthlyTax, £$employeeNI, £$netPay\n";
    file_put_contents("payroll_log.txt", $log, FILE_APPEND);
}
?>
</body>
</html>