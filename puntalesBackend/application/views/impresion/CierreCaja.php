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
//$pageLayout = array(216, 279);//tamano carta
$pageLayout = array(80, 279);
$margen=1;
$alineado='R';
$pdf = new MYPDF('P', 'mm', $pageLayout, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Alhecons');
$pdf->SetTitle('Cierre Caja');
$pdf->SetSubject('Reporte Cierre Caja');
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
  $pdf->SetFont('helvetica', 'B', 12);
  $pdf->Cell(0, 5, "$data->empresa", 0, 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(0, 5, "ARQUEO DE CAJA POR TURNO", 0, 1, 'C');
  $pdf->Cell(0, 0, '--------------------------------------------------------', 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(18, 5, "Sucursal: ", $margen, 0, 'L');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(55, 5, "$data->sucursal", $margen, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(18, 5, "Usuario: ", $margen, 0, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $pdf->MultiCell(55, 5, "$data->usuario", $margen, 'L', false);
  
  $x = $pdf->GetX();
  $y = $pdf->GetY();
  $pdf->SetXY($x, $y+3);

  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(38, 5, "Fecha/Hora Apertura: ",$margen, 0, 'L');
  $pdf->SetFont('helvetica', '', 8);
  $pdf->Cell(35, 5, "$data->fechaIngreso", $margen, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Fecha/Hora Cierre: ", $margen, 0, 'L');
  $pdf->SetFont('helvetica', '', 8);
  $pdf->Cell(35, 5, "$data->fechaSalida", $margen, 1, 'L');  
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Monto inicial de turno: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->montoInicial", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Total Ingresos: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->ingresos", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Total Egresos: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->egresos", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Total Efectivo: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->efectivo", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Total Transferencia: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->transferencia", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Total Otros: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->otros", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Saldo teorico: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->saldoTeorico", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Dinero entregado: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->saldoReal", $margen, 1, $alineado);
  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(38, 5, "Descuadre: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 9);
  $pdf->Cell(35, 5, "$data->descuadre", $margen, 1, $alineado);
  $pdf->Cell(0, 0, '', 'T', 1, 'C');
  $pdf->Ln(8);
  $pdf->Cell(0, 3, "------------------------", 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->MultiCell(0, 5, $data->usuario, 0, 'C',false);
  
  $pdf->Output('movimiento_caja.pdf', 'I');

?>