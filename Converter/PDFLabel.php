<?php

/*
 * Converter
 *
 * Copyright (c) 2013 David Yilma (david.yilma@dyksoft.cz).
 * The MIT Licence (http://opensource.org/licenses/MIT)
 * 
 */
namespace Converter;

require_once 'PDFWriter.php';

use Exception;
use Converter\PDFWriter;

/**
 * Class PDFLabel create sheet of label in PDF file from structured array.
 *
 * @author David Yilma, 2013
 * @version 0.9
 */
class PDFLabel extends PDFWriter{
    
    private $fontSize;
    private $col;
    private $controlPage;
    
    private $yMove;
    private $lableOnPage;
    
    public function __construct($fontSize = 8, $orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct();
        
        $this->AddPage();
        
        $this->fontSize = $fontSize;
        $this->col = 1;
        $this->controlPage = false;
    }

    public function createLabel($lables, $number = null, $lableOnPage = null) {
        /** @var array Dimension of Lable */
        $dimension = array();
        $this->lableOnPage = $lableOnPage;
        $numberLabel = ($number == null) ? count($lables) : $number;

        //$this->SetCol(4);
        $this->SetFontSize($this->fontSize);
        //$this->SetAutoPageBreak(false, 20);
        
        /** @todo How cerate more lable at 1 page. */
        for($lable = 0; $lable < $numberLabel; $lable++) {
            
            $y = 5 + $this->yMove;
            $dimension = ($number == null) ? $this->labelInTime($lables[$lable], 10, $y, 5.5, 5) : $this->labelInTime($lables[0], 10, $y, 5.5, 5);
            //var_dump($dimension);
    
            if($lable === $this->lableOnPage) {
                $this->AddPage();
                $this->resetPosition();
                $this->yMove = 0;
            } else {
                $this->yMove = $dimension['height'];
            }
        }

        if($this->savePdf()) {
            return true;
        } else {
            throw new Exception('PDF file have not been created.');
        }
    }
    
    private function labelInTime($label, $labelX, $labelY, $shiftRight, $line) {
        
        
        $endAxis[] = $this->labelInTimeLeft($label, $labelX, $labelY, $line);

        $movePart = $labelX + $endAxis[0]['right'];
        $endAxis[] = $this->labelInTimeRight($label, $movePart, $labelY, $line);
        
        $lableAxis['width'] = max(array($endAxis[0]['right'], $endAxis[1]['right']));
        $lableAxis['height'] = max(array($endAxis[0]['bottom'],  $endAxis[1]['bottom']));
        $this->Text($lableAxis['width'], $lableAxis['height'], '*');
        return $lableAxis;
    }
    
    private function labelInTimeLeft($data, $startX, $startY, $line) {
        
        $movePart = (count($data['sender']) * $line) + $startY + ($this->FontSize);

        $this->singleColumn($data['sender'], $startX, $startY, $line);
        $endPart = $this->doubleColumn($data['services'], $startX, $movePart, $line);
        return $endPart;
    }
    
    private function labelInTimeRight($data, $startX, $startY, $line) {

        $movePart = (count($data['packet']) * $line) + $startY + ($this->FontSize);

        $endPart = $this->doubleColumn($data['packet'], $startX, $startY, $line);
        $this->singleColumn($data['receiver'], $startX, $movePart, $line);
        return $endPart;
    }
    
    protected function singleColumn($data, $axisX, $axisY, $lineStep = 10) {

        $cellWidth = max(array_map('strlen', $data)) * ($this->FontSize / 1.8);
        $lineStep = ($this->FontSize);
        
        $y = 0;
        foreach($data as $row) {

            $y = $y + $lineStep;
            
            $this->SetXY($axisX, $axisY + $y);
            $this->Cell($cellWidth, $lineStep, $row, 1);
        }
        
        $endEdge['right'] = $axisX + $cellWidth;
        $endEdge['bottom'] = $axisY + $y + $lineStep;
        return $endEdge;
    }
    
    protected function doubleColumn($data, $axisX, $axisY, $lineStep = 10) {

        $keys = array_keys($data);
        $xMove = max(array_map('strlen', $keys)) * ($this->FontSize / 1.8);
        $cellWidth = max(array_map('strlen', $data)) * ($this->FontSize / 1.8);
        $lineStep = ($this->FontSize);
        
        $y = 0;
        foreach($data as $head => $body) {
            
            $y = $y + $lineStep;

            $this->SetXY($axisX, $axisY + $y);
            $this->Cell($xMove, $lineStep, $head, 1);
            $this->SetXY($axisX + $xMove, $axisY + $y);
            $this->Cell($cellWidth, $lineStep, $body, 1);
        }

        $endEdge['right'] = $axisX + $xMove + $cellWidth;
        $endEdge['bottom'] = $axisY + $y + $lineStep;
        return $endEdge;
    }
    
    /*function SetCol($col) {
        // Move position to a column
        $this->col = $col;
        $x = 10 + $col * 100;
        
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }*/

    /*function AcceptPageBreak() {
    
        if($this->controlPage === false) {
            $this->resetPosition();
            $this->yMove = 0;
            $this->controlPage = true;
            return true;
        } else {
            $this->controlPage = false;
            return false;
        }
        
    }*/
}