<?php
require_once 'Converter/DocxReader.php';
require_once 'Converter/PdfWriter.php';
require_once 'Converter/PdfLabel.php';

use Converter\DocxReader;
use Converter\PDFDocument;
use Converter\PDFLabel;

/*$content = array(
    'sender' => array(
        'company' => 'Christupher Robin s.r.o', 
        'address' => 'Hlavkova 5',
        'city' => 'Praha 2'),
    'receiver' => array(
        'name' => 'Jan Hnizdil', 
        'address' => 'Trubadurska 5',
        'city' => 'Brno'),
    'services' => array(
        'program' => 'S-24-CZ', 
        'dobirka' => 'Ano',
        'dokumenty' => 'Ne',
        'telefon' => 'Ne',
        'zepetna zasilka' => 'Ne'),
    'packet' => array(
        'colli:' => '001001',
        'zasilka c.:' => 'L6200000001',
        'referencni c.:' => '198612')
    );*/

$content = array(
    array(
        'sender' => array(
            'company' => 'Christupher Robin s.r.o', 
            'address' => 'Hlavkova 5',
            'city' => 'Praha 2'),
        'receiver' => array(
            'name' => 'Jan Hnizdil', 
            'address' => 'Trubadurska 5',
            'city' => 'Brno'),
        'services' => array(
            'program' => 'S-24-CZ', 
            'dobirka' => 'Ano',
            'dokumenty' => 'Ne',
            'telefon' => 'Ne',
            'zepetna zasilka' => 'Ne'),
        'packet' => array(
            'colli:' => '001001',
            'zasilka c.:' => 'L6200000001',
            'referencni c.:' => '198612')
        )
    );
    
    try {
        $pathDocx = '../source/level3.docx';
        $pathPdf = '../source/test.pdf';
        
        //$docx = new DocxReader();
        //$content = $docx->readWord($pathDocx);
        ///var_export($content);
        
        //$docPdf = new PDFDocument();
        //$docPdf->createDocument($content, $pathPdf);

        $lable = new PDFLabel();
        $lable->createLabel($content, 12, 20, true);

    } catch (Exception $e) {
        exit($e->getMessage() . ' Error code: ' . $e->getCode());
    }
            
        
        
