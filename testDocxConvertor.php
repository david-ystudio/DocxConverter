<?php
require_once 'Converter/DocxReader.php';
require_once 'Converter/PDFDocument.php';
require_once 'Converter/PDFLabel.php';

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

/*$content = array(
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
    );*/
    
$figures = array('../source/fig1.jpg', '../source/fig2.jpg');
    
    try {
        $pathDocx = '../source/level3.docx';
        $pathPdf = '../source/test.pdf';
        
        $docx = new DocxReader();
        $content = $docx->readWord($pathDocx);
        //var_export($content);
        
        $docPdf = new PDFDocument();
        $docPdf->createDocument($content, $figures, $pathPdf);

        //$lable = new PDFLabel();
        //$lable->createLabel($content, 12, 20, true);

    } catch (Exception $e) {
        exit($e->getMessage() . ' Error code: ' . $e->getCode());
    }
            
        
        
