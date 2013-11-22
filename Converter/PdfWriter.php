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
 * Class PdfWriter create PDF file from DocxReader array.
 *
 * @author David Yilma, 2013
 * @version 0.9
 */
class PDFWriter extends FPDF {
    
    const PDF_FONT_SIZE = 12;
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {

        $this->FPDF($orientation, $unit, $size);
        $this->SetFont('times', '', 0);
    }
    
    /**
     * Create PDF file from Word DOCX document
     * @param array $content
     * @param string $pathTarget
     * @return boolean
     * @throws Exception
     */
    public function createPdf($content, $pathTarget = null) {
        
        $this->AddPage();
                
        foreach($content as $paragraph) {
            $this->paragraph($paragraph, 'times', 2);
        }
        
        if($this->savePdf($pathTarget)) {
            return true;
        } else {
            throw new Exception('PDF file have not been created.');
        }
    }
    /**
     * Writes formatted text to paragraph
     * Content is arranged to DocxReader array.
     * @param array $paragraph Content must be from Word run tag (Office Open XML)
     * @param string $font
     * @param float $lineAfter
     */
    public function paragraph($paragraph, $font, $lineAfter) {

        if($paragraph['run'] != null) {
            foreach($paragraph['run'] as $run) {
                
                //String code
                $string = iconv('utf-8', 'ISO-8859-16//TRANSLIT', $run['content']);
                //Set style
                $style = $this->setStyle($run['b'], $run['i'], $run['u']);
                //Set color text
                $color = $this->setColor($run['color']);
                //Set font, line size and paragraph ater line
                $size = $this->getFontSize($run['sz']);
                $sizeLine = $this->setLineSize($size);
                $lineParagraph = $this->setParLine($size, $lineAfter);
                
                //Set shift of index
                $old = $this->setIndex($run['vertAlign'], $size, 0.1);

                //Write to PDF
                $this->SetFont($font, $style);
                $this->SetTextColor($color['r'], $color['g'], $color['b']);
                $this->Write($sizeLine, $string);
                
                //Set old coordinates for following text.
                $this->resetPosition($old);
            }
        }
        $this->Ln($lineParagraph);
    }
    
    /**
     * Set indexes in text
     * @param string $vertAlign
     * @param float $size
     * @param float $shift
     * @return int
     */
    private function setIndex($vertAlign, $size = false, $shift = false) {
        
        if(isset($vertAlign)) {

            if($shift === false) {$shift = 0.2;}
            if($size === false) {$size = PDFWriter::PDF_FONT_SIZE;}
            
            switch($vertAlign) {
                case 'superscript':
                    //Set new higher position and shift to right.
                    $oldPosition = $this->setPosition($shift, -1);
                    break;
                case 'subscript':
                    //Set new lower position and shift to right.
                    $oldPosition = $this->setPosition($shift, +1);
                    break;
            }
            //Set smaller font size.
            $this->SetFontSize($size/2);
            //Set new coordinates for following text.
            $oldPosition['x'] += 1;
            return $oldPosition;
        } else {
            $this->SetFontSize($size);
        }
    }
    
    /**
     * Get font size
     * @param integer $sz
     * @return integer
     */
    private function getFontSize($sz = null) {

        if(!$sz) {
            $size = PDFWriter::PDF_FONT_SIZE;
        } else {
            $size = $sz/2;
        }
        
        return $size;
    }
    
    /**
     * Set font style (bold/integer/underline)
     * @param string $bold
     * @param string $italic
     * @param string $underline
     * @return string
     */
    private function setStyle($bold = null, $italic = null, $underline = null) {
        
        if(!$bold) {
            $style = '';
        } else {
            $style = $bold;
        }

        if(!$italic) {
            $style .= '';
        } else {
            $style .= $italic;
        }

        if(!$underline) {
            $style .= '';
        } else {
            $style .= 'u';
        }
        
        return $style;
    }
    
    /**
     * Set hight of line.
     * @param float $sz
     * @return float
     */
    private function setLineSize($sz = null) {

        if(!$sz) {
            $sizeLine = PDFWriter::PDF_FONT_SIZE/2;
        } else {
            $sizeLine = $sz/2;
        }

        return $sizeLine;
    }
    
    /**
     * Set paragraph indetation
     * @param float $sz
     * @param int $lineAfter
     * @return float
     */
    private function setParLine($sz = null, $lineAfter = null) {
        
        if(!$lineAfter){$lineAfter = 1;}
        
        if(!$sz) {
            $lineParagraph = (PDFWriter::PDF_FONT_SIZE/3) * $lineAfter;
        } else {
            $sizesValue[] = ($sz/3) * $lineAfter;
            arsort($sizesValue);
            $lineParagraph = $sizesValue[0];
        }
        
        return $lineParagraph;
    }
    
    /**
     * Set text position
     * @param float $shiftX
     * @param float $shiftY
     * @return array
     */
    private function setPosition($shiftX, $shiftY) {

        $old['y'] = $this->GetY();
        $old['x'] = $this->GetX();

        $this->SetXY($old['x'] + ($shiftX), $old['y'] + ($shiftY));
        
        return $old;
    }
    
    /**
     * Reset position to original coordinate
     * @param array $old
     */
    private function resetPosition($old) {

        if(is_array($old)) {
            $this->SetXY($old['x'], $old['y']);
        }
    }
    
    /**
     * Set text color
     * @param string $colorHEX
     * @return array
     */
    private function setColor($colorHEX = null) {
        
        if(!$colorHEX) {
            $colorRGB['r'] = 0; $colorRGB['g'] = 0; $colorRGB['b'] = 0;
        } else {
            $colorRGB['r'] = hexdec(substr($colorHEX, 0, 2));
            $colorRGB['g'] = hexdec(substr($colorHEX, 2, 2));
            $colorRGB['b'] = hexdec(substr($colorHEX, 4, 2));
        }
        
        return $colorRGB;
        
    }
    
    /**
     * Output PDF file
     * @param string $pathPDF
     * @return boolean
     */
    private function savePdf($pathPDF = null) {
        
        if(isset($pathPDF)) {
            $this->Output($pathPDF);
            return true;
        } else {
            $this->Output();
        }
    }
}
