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
    private $yMove;
    private $controlPage;
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct();
        
        $this->AddPage();
        
        $this->fontSize = 10;
        $this->yAxis = 5;
        $this->xAxis = 10;
        $this->controlPage = false;
    }

    public function createLabel($lables, $number = null, $lableOnPage = 6, $report = false) {
        
        //Set and check variable
        $dimension = array();
        $numberLabel = ($number == null) ? count($lables) : $number;
        $lableOnPage = ($lableOnPage > $number) ? 6 : $lableOnPage;
        $pages = ceil($numberLabel / $lableOnPage);
        
        $this->SetFontSize($this->fontSize);
        
        for($page = 1; $page <= $pages; $page++) {
            
            if(($page == $pages) && ($numberLabel % $lableOnPage != 0)) {
                $lablePage = $numberLabel % $lableOnPage;
            } else {
                $lablePage = $lableOnPage;
            }
            
            for($lable = 0; $lable < $lablePage; $lable++) {

                $y = $this->yAxis + 5;
                $dimension = ($number == null) ? $this->labelInTime($lables[$lable], $this->xAxis, $y, 3) : $this->labelInTime($lables[0], $this->xAxis, $y, 3);

                $this->yAxis = $dimension['height'];
            }
            
            if($page <= $pages - 1) {
                $this->AddPage();
                $this->resetPosition();
                $this->yAxis = 5;
            } 
            
            if($page == $pages && $report === true) {
                $this->report($numberLabel, $lableOnPage);
            }
        }
        
        if($this->savePdf()) {
            return true;
        } else {
            throw new Exception('PDF file have not been created.');
        }
    }
    
    private function labelInTime($label, $labelX, $labelY, $line) {
        
        
        $endAxis['left'] = $this->labelInTimeLeft($label, $labelX, $labelY, $line);

        $movePart = $endAxis['left'][0]['right'];
        $endAxis['right'] = $this->labelInTimeRight($label, $movePart, $labelY, $line);
        
        $lableAxis['width'] = max(array($endAxis['left'][0]['right'], $endAxis['right'][0]['right']));
        $lableAxis['height'] = max(array($endAxis['left'][1]['bottom'],  $endAxis['right'][1]['bottom']));
        $this->Text($lableAxis['width'] + 5, $lableAxis['height'] + 2.5, '+');
        return $lableAxis;
    }
    
    private function labelInTimeLeft($data, $startX, $startY, $line) {
        
        $movePart = (count($data['sender']) * $line) + $startY + ($this->FontSize);

        $endPart[] = $this->singleColumn($data['sender'], $startX, $startY, $line);
        $endPart[] = $this->doubleColumn($data['services'], $startX, $movePart, $line);
        return $endPart;
    }
    
    private function labelInTimeRight($data, $startX, $startY, $line) {

        $movePart = (count($data['packet']) * $line) + $startY + ($this->FontSize);

        $endPart[] = $this->doubleColumn($data['packet'], $startX, $startY, $line);
        $endPart[] = $this->singleColumn($data['receiver'], $startX, $movePart, $line);
        return $endPart;
    }
    
    protected function singleColumn($data, $axisX, $axisY, $lineStep = 5) {

        $cellWidth = max(array_map('strlen', $data)) * ($this->FontSize / 1.8);
        $lineStep = $this->FontSize;
        
        $y = 0;
        foreach($data as $row) {

            $y = $y + $lineStep;
            
            $this->Text($axisX, $axisY + $y, $row, 1);
        }
        
        $endEdge['right'] = $axisX + $cellWidth;
        $endEdge['bottom'] = $axisY + $y + $lineStep;
        return $endEdge;
    }
    
    protected function doubleColumn($data, $axisX, $axisY, $lineStep = 5) {

        $keys = array_keys($data);
        $xMove = max(array_map('strlen', $keys)) * ($this->FontSize / 1.8);
        $cellWidth = max(array_map('strlen', $data)) * ($this->FontSize / 1.8);
        $lineStep = $this->FontSize;
        
        $y = 0;
        foreach($data as $head => $body) {
            
            $y = $y + $lineStep;

            $this->Text($axisX, $axisY + $y, $head, 1);
            $this->Text($axisX + $xMove, $axisY + $y, $body, 1);
        }

        $endEdge['right'] = $axisX + $xMove + $cellWidth;
        $endEdge['bottom'] = $axisY + $y + $lineStep;
        return $endEdge;
    }
    
    private function report($numberLabel, $lableOnPage) {

        $this->AddPage();

        $this->Cell(0, 10, 'Report', 1, 1);
        $this->Cell(0, 10, 'Lables: '.$numberLabel, 1, 1);
        $this->Cell(0, 10, 'Page(s): '.ceil($numberLabel / $lableOnPage), 1, 1);
        $this->Cell(0, 10, 'Lables per Page: '.$lableOnPage.' and '.$numberLabel % $lableOnPage, 1, 1);
    }
}