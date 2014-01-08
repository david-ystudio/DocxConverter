<?php

/*
 * Converter
 * 
 * Copyright (c) 2013, David Yilma (david.yilma@dyksoft.cz)
 * The MIT Licence (http://opensource.org/licenses/MIT)
 * 
 */
namespace Converter;

require_once '../fpdf/fpdf.php';
require_once 'Service.php';

use Exception;
use FPDF;

/**
 * Description of PDFWrite
 *
 * @author David Yilma
 */

class PDFWriter extends FPDF {
    
    const PDF_FONT_SIZE = 12;
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {

        $this->FPDF($orientation, $unit, $size);
        $this->SetFont('times', '', self::PDF_FONT_SIZE);
    }
    
    public function table($header, $data, $border) {
        
        $this->tableHead($header, $border);
        $this->tableBody($data, $border);
        
    }
    
    protected function tableHead($header, $border) {
        
        /* Dimension of table */
        $width = 15;
        $height = 12;
        
        foreach($header as $col) {
            $this->Cell($width, $height, $col, $border);
        }
        $this->Ln();
    }
    
    protected function tableBody($data, $border) {

        /* Dimension of table */
        $width = 30;
        $height = 10;

        foreach($data as $row) {
            foreach($row as $head => $col) {
                $this->Cell($width, $height, $head, $border);
                $this->Cell($width, $height, $col, $border);
                $this->Ln();
            }
            $this->Ln();
        }
    }

    /**
     * Reset position to original or zero coordinate
     * @param array $old
     */
    protected function resetPosition($old = null) {

        if(is_array($old)) {
            $this->SetXY($old['x'], $old['y']);
        } else {
            $this->SetXY(0, 0);
        }
    }
    
    /**
     * Output PDF file
     * @param string $pathPDF
     * @return boolean
     */
    protected function savePdf($pathPDF = null) {
        
        if(isset($pathPDF)) {
            $this->Output($pathPDF);
            return true;
        } else {
            $this->Output();
        }
    }

}
