<?php
require('libs/fpdf.php');
include 'db.php';

if (isset($_POST['employee_id'], $_POST['month'])) {
    $employee_id = $_POST['employee_id'];
    $month = $_POST['month'];

    $stmt = $conn->prepare("SELECT * FROM payroll WHERE user_id = ? AND month = ?");
    $stmt->bind_param("is", $employee_id, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'COMPANY NAME',1,1,'C');
        $pdf->SetFont('Arial','',10);

        // Header
        $pdf->Cell(70,10,'Hourly Pay',1,0,'C');
        $pdf->Cell(70,10,'Payments',1,0,'C');
        $pdf->Cell(70,10,'Deductions',1,1,'C');

        // Header row
        $pdf->Cell(35,8,'DESCRIPTION',1,0,'C');
        $pdf->Cell(15,8,'HOURS',1,0,'C');
        $pdf->Cell(15,8,'RATE',1,0,'C');
        $pdf->Cell(25,8,'AMOUNT',1,0,'C');
        $pdf->Cell(35,8,'DESCRIPTION',1,0,'C');
        $pdf->Cell(50,8,'AMOUNT',1,0,'C');
        $pdf->Cell(35,8,'DESCRIPTION',1,0,'C');
        $pdf->Cell(50,8,'AMOUNT',1,1,'C');

        // Hourly Pay
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,8,'Standard Rate',1,0);
        $pdf->Cell(15,8,'',1,0);
        $pdf->Cell(15,8,'',1,0);
        $pdf->Cell(25,8,'',1,0);
        $pdf->Cell(35,8,'Total Hourly Pay',1,0);
        $pdf->Cell(50,8,number_format($row['monthly_salary'],2),1,0);
        $pdf->Cell(35,8,'Income Tax',1,0);
        $pdf->Cell(50,8,number_format($row['income_tax'],2),1,1);

        $pdf->Cell(35,8,'Overtime',1,0);
        $pdf->Cell(15,8,'',1,0);
        $pdf->Cell(15,8,'',1,0);
        $pdf->Cell(25,8,number_format($row['overtime'],2),1,0);
        $pdf->Cell(35,8,'Basic Pay',1,0);
        $pdf->Cell(50,8,number_format($row['monthly_salary'],2),1,0);
        $pdf->Cell(35,8,'National Insurance',1,0);
        $pdf->Cell(50,8,number_format($row['employee_ni'],2),1,1);

        $pdf->Cell(35,8,'Totals',1,0);
        $pdf->Cell(15,8,'',1,0);
        $pdf->Cell(15,8,'',1,0);
        $pdf->Cell(25,8,number_format($row['monthly_salary'] + $row['overtime'],2),1,0);
        $pdf->Cell(35,8,'Total Payments',1,0);
        $pdf->Cell(50,8,number_format($row['monthly_salary'] + $row['overtime'],2),1,0);
        $pdf->Cell(35,8,'Total Deductions',1,0);
        $pdf->Cell(50,8,number_format($row['income_tax'] + $row['employee_ni'] + $row['deductions'],2),1,1);

        // Footer
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(40,8,'EMPLOYEE NAME:',1,0);
        $pdf->Cell(60,8,$row['name'],1,0);
        $pdf->Cell(40,8,'NET PAY:',1,0);
        $net_pay = $row['monthly_salary'] + $row['overtime'] - $row['deductions'] - $row['income_tax'] - $row['employee_ni'];
        $pdf->Cell(40,8,number_format($net_pay,2),1,1);

        $pdf->Output('I', 'Payslip_'.$row['name'].'_'.$row['month'].'.pdf');
        exit;
    } else {
        echo "Payroll record not found.";
    }
} else {
    echo "Invalid request.";
}
?>