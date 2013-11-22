<?php
require_once 'Converter/DocxReader.php';
require_once 'Converter/PdfWriter.php';

use Converter\DocxReader;
use Converter\PdfWriter;

    try {
        $pathDocx = '../source/level3.docx';
        $pathPdf = '../source/test.pdf';
        
        $docx = new DocxReader();
        $content = $docx->readWord($pathDocx);
        ///var_export($content);
        
        $docPdf = new PdfWriter();
        $docPdf->createPdf($content, $pathPdf);

    } catch (Exception $e) {
        exit($e->getMessage() . ' Error code: ' . $e->getCode());
    }
            
        
        
