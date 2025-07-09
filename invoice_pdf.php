<?php
require('fpdf.php');
include "db.php";

$order_id = $_GET["order_id"];

$sql = "SELECT o.id, c.name AS cake_name, c.price, o.quantity, o.order_date
        FROM orders o
        JOIN cakes c ON o.cake_id = c.id
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$total = $data["price"] * $data["quantity"];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'វិក័យបត្រ',0,1,'C');
$pdf->SetFont('Arial','',12);

$pdf->Cell(50,10,'លេខវិក័យបត្រ:',0,0);
$pdf->Cell(0,10,$data["id"],0,1);

$pdf->Cell(50,10,'ថ្ងៃបញ្ជាទិញ:',0,0);
$pdf->Cell(0,10,$data["order_date"],0,1);

$pdf->Ln(5);
$pdf->Cell(50,10,'ឈ្មោះនំ:',0,0);
$pdf->Cell(0,10,$data["cake_name"],0,1);

$pdf->Cell(50,10,'តម្លៃ ($):',0,0);
$pdf->Cell(0,10,$data["price"],0,1);

$pdf->Cell(50,10,'បរិមាណ:',0,0);
$pdf->Cell(0,10,$data["quantity"],0,1);

$pdf->Cell(50,10,'សរុប ($):',0,0);
$pdf->Cell(0,10,number_format($total, 2),0,1);

$pdf->Output();
