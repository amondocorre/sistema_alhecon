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
$margen=1;
$alineado='R';
$data = json_decode($json);
$pageLayout = array(80, 100);
$pdf = new MYPDF('P', 'mm', $pageLayout, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Alhecons');
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
  $pdf->SetFont('helvetica', 'B', 12);
  $pdf->Cell(0, 10, "$data->empresa", 0, 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(0, 5, "APERTURA DE TURNO", 0, 1, 'C');
  $pdf->Cell(0, 0, '-------------------------------------', 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(19, 5, "Sucursal: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(48, 5, "$data->sucursal", $margen, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(19, 5, "Usuario: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->MultiCell(48, 5, "$data->usuario", $margen, 'L', false);
  
  $x = $pdf->GetX();
  $y = $pdf->GetY();
  $pdf->SetXY($x, $y+3);
  
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(20, 5, "Monto: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(48, 5, "$data->monto", $margen, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(20, 5, "Fecha: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(48, 5, "$data->fecha", $margen, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(20, 5, "Hora: ", $margen, 0, $alineado);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(48, 5, "$data->hora", $margen, 1,'L');  
  //$pdf->Cell(0, 0, '', 'T', 1, 'C');
  $pdf->Ln(8);
  $pdf->Cell(0, 3, "----------------------", 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->MultiCell(0, 5, $data->usuario, 0, 'C',false);
  $pdf->Output('movimiento_caja.pdf', 'I');

?>