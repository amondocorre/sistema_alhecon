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
$pageLayout = array(80, 170);
$pdf = new MYPDF('P', 'mm', $pageLayout, true, 'UTF-8', false);
//$pdf->SetAutoPageBreak(true, 10); 
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Alhecon');
$pdf->SetTitle('recibo pago');
$pdf->SetSubject('recibo pago');
$pdf->SetKeywords('TCPDF, CodeIgniter, PDF, Voucher');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetHeaderMargin(5);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
  require_once(dirname(__FILE__) . '/lang/eng.php');
  $pdf->setLanguageArray($l);
}
$pdf->setFontSubsetting(true);
  $pdf->SetMargins(3, 3, 3);
  $pdf->SetAutoPageBreak(TRUE, 10);
  $pdf->AddPage();
  $logoWidth = 25;
  $pageWidth = $pdf->getPageWidth(); 
  $logoX = ($pageWidth - $logoWidth) / 2;
  $logoY = 3; 
  $pdf->Image($data->logo, $logoX, $logoY, $logoWidth, '', 'PNG');
  $pdf->Ln($logoWidth);
  $pdf->SetFont('helvetica', 'B', 14);
  $pdf->Cell(0, 5, "$data->empresa", 0, 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->MultiCell(70, 5, "$data->direccion", 0, 'C', false);
  $pdf->Cell(0, 5, "$data->celular", 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "NIT:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(37, 5, "$data->nit", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 11);
  $pdf->Cell(0, 2, "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 14);
  $pdf->Cell(0, 7, "RECIBO DE PAGO N°: $data->numero", 0, 1, 'C');
  
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "FECHA:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '',9);
  $pdf->Cell(37, 5, "$data->fecha $data->hora", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "Operario:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '',10);
  $pdf->Cell(37, 5, "$data->usuario", 0, 1, 'L');

  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "Cliente:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(37, 5, "$data->cliente", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "ci:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(37, 5, "$data->ci_cli", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "telefono:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(37, 5, "$data->celular_cli", 0, 1, 'L');
  $pdf->SetFont('helvetica', 'B', 11);
  $pdf->Cell(0, 2, "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
  
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(55, 5, "Referencia a Nota de Venta: ".($data->id_alquiler_documento??''), 0, 1, 'C');
  $tam = 5;
  $pdf->SetFont('helvetica', 'B', 11);
  $pdf->Cell(0, 2, "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');

  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "TOTAL PAGADO:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(37, 5, " Bs $data->monto", 0, 1, 'L');

  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(37, 5, "Método de pago:", 0, 0, 'R');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(37, 5, "$data->forma_pago", 0, 1, 'L');

  $pdf->SetFont('helvetica', 'B', 10);
  //$pdf->Cell(30, 5, "Observaciones: ", 0, 0, 'L');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->MultiCell(74, 5, "Observaciones: Este comprobante certifica el pago realizado en referencia al N° Pedido mencionada.", 0, 'L', false);
  
  $pdf->Ln(8);
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(0, 5, "- - - - - - - - - - - - - - ", 0, 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(0, 5, "Firma", 0, 1, 'C');

  $pdf->Output('movimiento_caja.pdf', 'I');

?>