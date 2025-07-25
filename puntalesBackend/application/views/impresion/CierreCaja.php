<?php 

class MYPDF extends TCPDF
{
  public function Header(){}
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
    }
}
$data = json_decode($json);
$pageLayout = array(80, 150);
$pdf = new MYPDF('P', 'mm', $pageLayout, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Chuñitos');
$pdf->SetTitle('Apertura Caja');
$pdf->SetSubject('Reporte Apertura Caja');
$pdf->SetKeywords('TCPDF, CodeIgniter, PDF, Voucher, Egreso, Ingreso');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetHeaderMargin(5);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
  require_once(dirname(__FILE__) . '/lang/eng.php');
  $pdf->setLanguageArray($l);
}
$pdf->setFontSubsetting(true);
  $pdf->SetMargins(5, 5, 5);
  $pdf->SetAutoPageBreak(TRUE, 10);
  $pdf->AddPage();
  $pdf->SetFont('helvetica', 'B', 14);
  $pdf->Cell(0, 10, "$data->empresa", 0, 1, 'C');
  $pdf->SetFont('helvetica', '', 12);
  $pdf->Cell(0, 7, "ARQUEO DE CAJA POR TURNO", 0, 1, 'C');
  $pdf->Cell(0, 0, '', 'T', 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(23, 7, "Sucursal: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(48, 7, "$data->sucursal", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(18, 7, "Usuario: ", 0, 0, 'L');
  $x = $pdf->GetX();
  $y = $pdf->GetY();
  $pdf->SetXY($x, $y+1);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->MultiCell(48, 7, "$data->usuario", 0, 'L', false);
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(35, 7, "Fecha/Hora Ingreso: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(48, 7, "$data->fechaIngreso", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(35, 7, "Fecha/Hora Salida: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(48, 7, "$data->fechaSalida", 0, 1, 'L');  
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Monto inicial de turno: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->montoInicial", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Total Ingresos: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->ingresos", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Total Egresos: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->egresos", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Total Efectivo: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->efectivo", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Total Transferencia: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->transferencia", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Total Otros: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->otros", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Saldo teorico: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->saldoTeorico", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Dinero entregado: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->saldoReal", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 7, "Descuadre: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(45, 7, "$data->descuadre", 0, 1, 'L');
  $pdf->Cell(0, 0, '', 'T', 1, 'C');
  $pdf->Ln(8);
  $pdf->Cell(0, 3, "----------------------", 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(0, 7, $data->usuario, 0, 1, 'C');
  $pdf->Output('movimiento_caja.pdf', 'I');

?>