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
$pageLayout = [216, 279];
$pdf = new MYPDF('P', 'mm', $pageLayout, true, 'UTF-8', false);
//$pdf->SetAutoPageBreak(true, 10); 
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Chuñitos');
$pdf->SetTitle('nota venta');
$pdf->SetSubject('nota venta');
$pdf->SetKeywords('TCPDF, CodeIgniter, PDF, Voucher');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetHeaderMargin(5);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
  require_once(dirname(__FILE__) . '/lang/eng.php');
  $pdf->setLanguageArray($l);
}
$pdf->setFontSubsetting(true);
$pdf->SetMargins(25, 20, 20);
$pdf->SetAutoPageBreak(TRUE, 10);
if(true){
  $pdf->AddPage();
  $logoWidth = 25;
  $logoX = $pdf->GetX();
  $logoY = $pdf->GetY();
  $pdf->Image($data->logo, $logoX-10, $logoY-10, $logoWidth, '', 'PNG');
  $pdf->SetFont('helvetica', 'B', 14);
  $afterLogoY = $logoY + $logoWidth + 2;
  $pdf->SetXY($pdf->getMargins()['left']-10,$afterLogoY-10);
  $pdf->Cell(0, 5, "$data->empresa", 0, 1, 'L');
  $pdf->SetXY(116,$pdf->getMargins()['top']);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->MultiCell(80, 5, "$data->direccion", 0, 'R', false);
  $pdf->SetXY(116,$pdf->getMargins()['top']+5);
  $pdf->Cell(0, 5, "Telfs.: ".$data->celular, 0, 1, 'R');
  $pdf->Cell(0, 5, "Usuario: ".$data->contrato->usuario, 0, 1, 'R');
  $pdf->SetXY(156,$pdf->GetY());
  $pdf->Cell(25, 5, "Pedido #", 'TLB', 0, 'R');
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(15, 5, "".$data->contrato->numero, 'TRB', 1, 'C');
  $pdf->Cell(0, 5, "", 0, 1, 'C');
  $pdf->SetFont('helvetica', 'B', 16);
  $pdf->SetXY(96,$pdf->GetY());
  $pdf->Cell(40, 7, "Hoja de Pedido", 'B', 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(0, 5, "", 0, 1, 'C');
  $pdf->Cell(20, 5, "Pedido por:", 0, 0, 'L');
  $pdf->Cell(120, 5, utf8_encode($data->contrato->cliente), 'B', 0, 'L');
  $pdf->Cell(15, 5, "Celular:", 0, 0, 'L');
  $pdf->Cell(21, 5, $data->contrato->telefono, 'B', 1, 'L');
  $pdf->Cell(20, 6, "Dir. Obra:", 0, 0, 'R');
  $pdf->Cell(156, 5, $data->contrato->director_obra??'', 'B', 1, 'L');
  $pdf->ln(5);
  $pdf->SetX($pdf->getMargins()['left']+5);
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(106, 5, "Detalle", 1, 0, 'C');
  $pdf->Cell(20, 5, "Cantidad", 'TRB', 0, 'C');
  $pdf->Cell(20, 5, "P. unit.", 'TRB', 0, 'C');
  $pdf->Cell(20, 5, "P. Total", 'TRB', 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  foreach($data->contrato->detalle as $key => $producto){
    $pdf->SetX($pdf->getMargins()['left']+5);
    $y = $pdf->GetY();
    $pdf->MultiCell(106, 6, $producto->nombre??''.'', 'LRB', 'L', false);
    $newY = $pdf->GetY() - $y;
    $pdf->setXY($pdf->getMargins()['left']+111,$y);
    $pdf->Cell(20, $newY, number_format($producto->cantidad,0), 'RB', 0, 'C');
    $pdf->Cell(20, $newY, number_format($producto->precio_unitario,2), 'RB', 0, 'C');
    $pdf->Cell(20, $newY, number_format($producto->sub_total,2), 'RB', 1, 'C');
  }
  
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(151, 5, 'Precio Total Productos:', 0, 0, 'R');
  $pdf->Cell(20, 5, number_format($data->contrato->sub_total,2), 'LRB', 1, 'C');
  
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(151, 5, 'Descuento:', 0, 0, 'R');
  $pdf->Cell(20, 5, number_format($data->contrato->descuento,2), 'LRB', 1, 'C');
  
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(151, 5, 'Precio Transporte:', 0, 0, 'R');
  $pdf->Cell(20, 5, number_format($data->contrato->tansporte??0,2), 'LRB', 1, 'C');
  
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(151, 5, 'Total Precio Final:', 0, 0, 'R');
  $pdf->Cell(20, 5, number_format($data->contrato->total,2), 'LRB', 1, 'C');
  
  $pdf->Ln(10);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(30, 5, 'Contacto en obra:', 0, 0, 'R');
  $pdf->Cell(96, 5, '', 'B', 0, 'C');
  $pdf->Cell(20, 5, 'Celular:', 0, 0, 'R');
  $pdf->Cell(30, 5, '', 'B', 1, 'C');
  
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(30, 5, 'Dueño de la obra:', 0, 0, 'R');
  $pdf->Cell(96, 5, '', 'B', 0, 'C');
  $pdf->Cell(20, 5, 'Celular:', 0, 0, 'R');
  $pdf->Cell(30, 5, '', 'B', 1, 'C');
    
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(30, 5, 'Garantia:', 0, 0, 'R');
  $pdf->Cell(96, 5, number_format($data->contrato->garantia,2).' Bs.', 'B', 1, 'L');
  $pdf->Ln(5);
  $pdf->SetFont('helvetica', 'B', 10);  
  /*$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'Nota: El material debera ser devuelto en las mismas condiciones y de la misma manera en que fue entregado.
                            Ademas de que debera ser devuelto como maximo en la fecha de cumplimiento de alquiler, caso contrario sera
                            retenido la totalidad de la garantia recibida y cancelar una multa por el perjuicio ocasionado a la empresa. Si existe o
                            se presenta algun retraso, el cliente debera informar sobre este a la empresa antes de cumplir el tiempo de alquiler y
                            realizar el pago respectivo por el tiempo extra que sera usado. En caso que se extravie algun material o accesorio
                            consistente en:......................................................., el cliente debera resarcir el daño, cancelando a la empresa la suma
                            de Bs:....................... por el material extraviado.'), 0, 'L', false);
  */;
  $text = preg_replace('/\s+/', ' ', 'Nota: El material debera ser devuelto en las mismas condiciones y de la misma manera en que fue entregado.
                            Ademas de que debera ser devuelto como maximo en la fecha de cumplimiento de alquiler, caso contrario sera
                            retenido la totalidad de la garantia recibida y cancelar una multa por el perjuicio ocasionado a la empresa. Si existe o
                            se presenta algun retraso, el cliente debera informar sobre este a la empresa antes de cumplir el tiempo de alquiler y
                            realizar el pago respectivo por el tiempo extra que sera usado. En caso que se extravie algun material o accesorio
                            consistente en:......................................................., el cliente debera resarcir el daño, cancelando a la empresa la suma
                            de Bs:....................... por el material extraviado.');
  $html = '<div align="justify">'.$text.'</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');
  $pdf->Ln(5);
  $pdf->SetFont('helvetica', '', 10);
  $pdf->Cell(40, 5, '', 'B', 1, 'R');
  $pdf->Cell(40, 5, 'Firma Recibido', 0, 1, 'C');
  $pdf->setY($pdf->GetY()-5);
  $pdf->Cell(146, 5, 'Fecha de pedido:', 0, 0, 'R');
  $pdf->Cell(30, 5, $data->contrato->fecha_emision, 'B', 1, 'C');
  $pdf->Cell(146, 5, 'Fecha de entrega:', 0, 0, 'R');
  $pdf->Cell(30, 5, $data->contrato->fecha_entrega, 'B', 1, 'C');

  $pdf->Cell(40, 5, 'Nombre:', 0, 0, 'R');
  $pdf->Cell(40, 5, '', 'B', 1, 'C');
  $pdf->Cell(40, 5, 'CI:', 0, 0, 'R');
  $pdf->Cell(40, 5, '', 'B', 1, 'C');
  $pdf->Cell(176, 5, '', 'B', 1, 'C');
  
  $pdf->Cell(60, 5, 'Fecha de cumplimiento del alquiler:', 0, 0, 'R');
  $pdf->Cell(71, 5, $data->contrato->fecha_devolucion??'', 'B', 1, 'C');
  $pdf->Cell(60, 5, 'Entregado por:', 0, 0, 'R');
  $pdf->Cell(71, 5, '', 'B', 0, 'C');
  $pdf->Cell(15, 5, 'Firma:', 0, 0, 'R');
  $pdf->Cell(30, 5, '', 'B', 1, 'C');
}
if(true){

  $pdf->AddPage();
  $logoWidth = 20;
  $logoX = $pdf->GetX();
  $logoY = $pdf->GetY();
  $pdf->Image($data->logo, $logoX-10, $logoY-10, $logoWidth, '', 'PNG');
  $pdf->SetFont('helvetica', 'B', 16);
  $afterLogoY = $logoY + $logoWidth + 2;
  $pdf->SetXY($pdf->getMargins()['left']+$logoWidth-10,$pdf->getMargins()['top']);
  $pdf->Cell(0, 5, "$data->empresa", 0, 1, 'L');
  $pdf->SetXY($pdf->getMargins()['left'],$pdf->getMargins()['top']+$logoWidth-10);
  $pdf->SetFont('helvetica', 'B', 15);
  $pdf->MultiCell(0, 5, "DOCUMENTO ACLARATORIO", 0, 'C', false);
  $pdf->Ln(7);
  $pdf->SetFont('helvetica', '', 10);
  /*$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'La empresa '.$data->empresa.', alquiler de material de construcción mediante el presente
                                                        documento tiene como finalidad aclarar.'), 0, 'L', false,1);
  */$text ="La empresa ".$data->empresa.", alquiler de material de construcción mediante el presente documento tiene como finalidad aclarar.";
  
  $html = '<div align="justify">'.$text.'</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');
  //$pdf->writeHTML($html, true, false, true, false, '');
  $pdf->Ln(2);
  $pdf->Cell(0, 5, 'a) Se da en alquiler lo siguiente:', '', 1, 'L');
  foreach($data->productos as $key=>$producto){
    $pdf->SetX($pdf->getMargins()['left']+5);
    $pdf->Cell(2, 5, '- ', '', 0, 'L');
    $textProducto = ''.$producto->cantidad.' '.$producto->producto;
    if($producto->es_combo=='0'){
      $textProducto .= ' individales, ';
    }else{
      $textProducto .= ' cada uno con todos sus accesorios completos, es decir con ';
      $cant = count($producto->detalle);
      foreach($producto->detalle as $key2=>$det){
        if ($key2+1 === $cant) {
          $textProducto .= $det->nombre.'. ';
        }elseif($key2 === $cant){
          $textProducto .= $det->nombre.' y ';
        }else{
          $textProducto .= $det->nombre.', ';
        }
      }
    }
    $textProducto .= 'los cuales se encuentran en perfecto estado para ser utílizado en la construcción de la obra.'; 
    //$pdf->MultiCell(169, 6,  preg_replace('/\s+/', ' ', $textProducto), 0, 'L', false,1);
    $html = '<div align="justify">'.$textProducto.'</div>';
    $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');
  }
  $pdf->SetX($pdf->getMargins()['left']+5);
  $pdf->Ln(2);
  $pdf->SetFont('helvetica', '', 10);
  //$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'b) Los ARRENDATARIOS manifiestan haber recibido las herramientas entregados en condiciones completas y funcionales, previa verificación de su estado, conforme a la cantidad pactada. En caso de identificar alguna anomalía, daño o faltante, deberán notificarlo de inmediato adjuntando evidencia fotográfica y video.'), 0, 'L', false,1);
  $html = '<div align="justify">b) Los ARRENDATARIOS manifiestan haber recibido las herramientas entregados en condiciones completas y funcionales, previa verificación de su estado, conforme a la cantidad pactada. En caso de identificar alguna anomalía, daño o faltante, deberán notificarlo de inmediato adjuntando evidencia fotográfica y video.</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');

  $pdf->SetX($pdf->getMargins()['left']+5);
  $pdf->Ln(2);
  $pdf->SetFont('helvetica', '', 10);
  //$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'c) Se establece que los equipos o herramientas entregadas en calidad de alquiler quedan bajo la exclusiva responsabilidad del arrendatario. En caso de pérdida, daño o deterioro atribuible a un uso inadecuado o negligente (incluyendo golpes, deformaciones o cualquier condición que comprometa la integridad o funcionalidad del bien), el arrendatario se compromete a cubrir los costos correspondientes según lo estipulado.'), 0, 'L', false,1);
  $html = '<div align="justify">c) Se establece que los equipos o herramientas entregadas en calidad de alquiler quedan bajo la exclusiva responsabilidad del arrendatario. En caso de pérdida, daño o deterioro atribuible a un uso inadecuado o negligente (incluyendo golpes, deformaciones o cualquier condición que comprometa la integridad o funcionalidad del bien), el arrendatario se compromete a cubrir los costos correspondientes según lo estipulado.</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');

  $pdf->Ln(2);
  $pdf->SetFont('helvetica', '', 10);
  //$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'En caso de que el equipo, herramienta o accesorio entregado en alquiler sea devuelto en condiciones no óptimas o presente deterioro atribuible al uso inapropiado, el arrendatario se compromete a cubrir el costo correspondiente según el siguiente detalle:'), 0, 'L', false,1);
  $html = '<div align="justify">En caso de que el equipo, herramienta o accesorio entregado en alquiler sea devuelto en condiciones no óptimas o presente deterioro atribuible al uso inapropiado, el arrendatario se compromete a cubrir el costo correspondiente según el siguiente detalle:</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');

  $pdf->Ln(2);
  $pdf->SetX($pdf->getMargins()['left']+2);
  $pdf->SetFont('helvetica', 'B', 10);
  //$pdf->SetFillColor(200, 200, 200); 
  $pdf->Cell(141, 6, 'Producto', 1, 0, 'C');
  $pdf->Cell(25, 6, 'Precio', 1, 1, 'C');
  $pdf->SetFont('helvetica', '', 10);
  foreach ($data->productos as $prod) {
      // Si es combo y tiene detalle
      $pdf->SetX($pdf->getMargins()['left']+2);
      if ($prod->es_combo == '1') {
        $y = $pdf->GetY();
        $pdf->MultiCell(141, 6, $prod->producto??''.'', 'RL', 'L', false);
        $newY = $pdf->GetY() - $y;
        $pdf->setXY($pdf->getMargins()['left']+143,$y);
        $pdf->Cell(25, $newY,'', 'LR', 1, 'C');
        $cant = count($prod->detalle);
          foreach ($prod->detalle as $key=>$item) {
            $pdf->SetX($pdf->getMargins()['left']+2);
            if ($key+1 == $cant) {
              //$pdf->SetDrawColor(0, 102, 204);
              $pdf->SetFont('dejavusans', '', 9);
              //$pdf->Cell(10); // sangría
              $pdf->Cell(141, 6, '  ↳ ' . $item->nombre, 'LRB', 0, 'L');
              $pdf->Cell(25, 6, number_format($item->precio_reposicion, 2), 'LRB', 1, 'C');
              $pdf->SetFont('helvetica', '', 10);
            }else{
              $pdf->SetFont('dejavusans', '', 9);
              $pdf->Cell(141, 6, '  ↳ ' . $item->nombre, 'LR', 0, 'L');
              $pdf->Cell(25, 6, number_format($item->precio_reposicion, 2), 'LR', 1, 'C');
              $pdf->SetFont('helvetica', '', 10);
            }
          }
      }else{
        $pdf->Cell(141, 6, $prod->producto, 'LBR', 0, 'L');
        $pdf->Cell(25, 6, number_format($prod->precio_reposicion, 2), 'LBR', 1, 'C');
      }
  }
  $pdf->Ln(2);
  $pdf->SetFont('helvetica', '', 10);
  /*$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'd) El precio libremente convenido, de manera voluntaria sin que medie, presión, violencia o dolo en el
                                                          consentimiento. En caso de haber cumplido el mes de alquiler sin concretar el siguiente período completo, se aplicará la siguiente tarifa:'), 0, 'L', false,1);
  */
  $text= preg_replace('/\s+/', ' ', 'd) El precio libremente convenido, de manera voluntaria sin que medie, presión, violencia o dolo en el
                                                          consentimiento. En caso de haber cumplido el mes de alquiler sin concretar el siguiente período completo, se aplicará la siguiente tarifa:');
  $html = '<div align="justify">'.$text.'</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');
  $pdf->Ln(2);
  $pdf->SetX($pdf->getMargins()['left']+2);
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(126, 6, 'Producto', 1, 0, 'C');
  $pdf->Cell(20, 6, 'P. Día', 1, 0, 'C');
  $pdf->Cell(20, 6, 'P. Més', 1, 1, 'C');
  $pdf->SetFont('helvetica', '', 10);

  $cant = count($data->productos);
  foreach ($data->productos as $key=>$prod) {
    
    $pdf->SetX($pdf->getMargins()['left']+2);
    $y = $pdf->GetY();
    if ($key+1 == $cant) {
      $pdf->MultiCell(126, 6, $prod->producto??''.'', 'RBL', 'L', false);
      $newY = $pdf->GetY() - $y;
      $pdf->setXY($pdf->getMargins()['left']+128,$y);
      $pdf->Cell(20, $newY,number_format($prod->precio_dia,2), 'LBR', 0, 'C');
      $pdf->Cell(20, $newY,number_format($prod->precio_30dias,2), 'LBR', 1, 'C');
    }else{
      $pdf->MultiCell(126, 6, $prod->producto??''.'', 'RL', 'L', false);
      $newY = $pdf->GetY() - $y;
      $pdf->setXY($pdf->getMargins()['left']+128,$y);
      $pdf->Cell(20, $newY,number_format($prod->precio_dia,2), 'LR', 0, 'C');
      $pdf->Cell(20, $newY,number_format($prod->precio_30dias,2), 'LR', 1, 'C');
    }
  }
  
  $pdf->Ln(2);
  $pdf->SetFont('helvetica', 'B', 10);
  //$pdf->MultiCell(176, 6,  preg_replace('/\s+/', ' ', 'e) La empresa '.$data->empresa.' no se hace cargo del transporte de entrega y recojo, ni de la carga y descarga del material en la obra.'), 0, 'L', false,1);
  $html = '<div align="justify">e) La empresa '.$data->empresa.' no se hace cargo del transporte de entrega y recojo, ni de la carga y descarga del material en la obra.</div>';
  $pdf->WriteHTMLCell(0, 5, $pdf->GetX(), $pdf->GetY(), $html, 0, 1, false, true, 'J');
  $pdf->Ln(5);
  $pdf->SetX($pdf->getMargins()['left']+30);
  $pdf->Cell(40, 5, '', 'B', 0, 'R');
  $pdf->SetX($pdf->getMargins()['left']+100);
  $pdf->Cell(40, 5, '', 'B', 1, 'R');
  $pdf->SetX($pdf->getMargins()['left']+30);
  $pdf->Cell(40, 5, ''.$data->contrato->cliente, '', 0, 'C');
  $pdf->SetX($pdf->getMargins()['left']+100);
  $pdf->Cell(40, 5, 'ALHECON ESPINOZA', '', 1, 'C');
  
  //var_dump($data->contrato->cliente);
}
  /*176 */
$pdf->Output('movimiento_caja.pdf', 'I');
?>