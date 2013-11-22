<?php
/*
 * Converter
 * 
 * Copyright (c) 2013, David Yilma (david.yilma@dyksoft.cz)
 * The MIT Licence (http://opensource.org/licenses/MIT)
 * 
 */

namespace Converter;

use XMLReader;
use Exception;
use Converter\Service;

/**
 * Class DocxReader read Word XML document to array
 * 
 * WARNING: Very early version!
 * 
 * @author David Yilma
 * @version 0.9
 */
class DocxReader {
    
    const ZIP_DESTINATION = '../source/test/';
    const ZIP_PART = 'word/document.xml';
    
    /** @var XMLReader XMLReader object for XML manipulation */
    private $xmlReader;
    
    /** @var array Definition of Word XML for rPr tag. */
    private $rPrNodes;
    /** @var array Definition of Word XML for pPr tag. */
    private $pPrNodes;
    /** @var array Nodes definition of Word XML. */
    private $nodes;
    
    /** @var array Content form Word XMl file */
    private $xmlValue;
    
    private $i;
    private $j;
    
    public function __construct() {
        
        $this->rPrNodes = array(
            'w:sz' => 'w:val',
            'w:color' => 'w:val',
            'w:b' => 'b',
            'w:i' => 'i',
            'w:u' => 'w:val',
            'w:vertAlign' => 'w:val'
        );

        $this->pPrNodes = array(
        'w:spacing' => array(
            'w:before' => 'before',
            'w:line' => 'line',
            'w:after' => 'after'
            ),
        'w:sz' => array(
            'w:val' => 'value',
            ),
        'w:color' => array(
            'w:val' => 'value'
            ),
         'w:jc' => array(
             'w:val' => 'value'
         )
        );
        
        $this->nodes = array(
        'w:sz' => 'size',
        'w:color' => 'color',
        'w:jc' => 'aligment'
        );
        
        $this->i = -1;
        $this->j = -1;
    }
    
    /**
     * Read Word document content to structured array
     * @param string $pathDocx
     * @return boolean
     */
    public function readWord($pathDocx) {

        $pathXML = Service::openZip($pathDocx, DocxReader::ZIP_DESTINATION, DocxReader::ZIP_PART);
        if(is_file($pathXML)) {

            $this->openWordXML($pathXML);
            while($this->xmlReader->read()) {
                $this->readWordParagraph();
                $this->readWordRun();
            }
            $this->closeWordXML();

            return $this->xmlValue;
            
        } else {
            return false;
        }
    }
    
    /** @todo Need done. */
    public function readWordRichText() {

        while($this->xmlReader->read()) {


            $this->readWordElement();

            if($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->name == 'w:i' || $xmlReader->name == 'w:b' || $xmlReader->name == 'w:u') {
                $this->xmlValue[$this->i][$this->j]['typeface'][] = trim(strstr($xmlReader->name, ':'),':');
                //Remove duplicate value.
                $this->xmlValue[$this->i][$this->j]['typeface'] = array_unique($this->xmlValue[$this->i][$this->j]['typeface']);
            }

            $this->readWordAttribute();
        }
    }
    
    /**
     * Read all Paragraphs and their properties from Word document
     */
    public function readWordParagraph() {
        
        if($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == 'w:p') {
            $this->xmlValue[++$this->i]['content'] = trim($this->xmlReader->readString());
        }

        foreach($this->pPrNodes as $node => $element) {

            foreach($element as $attribute => $value) {
                
                if($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == $node) {

                        ($this->xmlReader->getAttribute($attribute) != null) ? $this->xmlValue[$this->i][trim($node, 'w:')][$value] = $this->xmlReader->getAttribute($attribute) : '';
                }
            }
        }
    }
    
    /**
     * Read all Runes and their properties from Word document
     * @param type $string
     */
    public function readWordRun($string = false) {

        if($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == 'w:r') {
            $this->xmlValue[$this->i]['run'][++$this->j]['content'] = $this->xmlReader->readString();

            $depth = true;
            while($this->xmlReader->read() && $depth == true) {

                if ($this->xmlReader->nodeType == XMLReader::ELEMENT) { $depth = true; }
                foreach($this->rPrNodes as $node => $value) {

                    if($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == $node) {

                        if(($this->xmlReader->getAttribute($value) == null) && ($string == true)) {
                            $this->xmlValue[$this->i]['run'][$this->j]['style'] .= trim($node, 'w:');
                        } else if(($this->xmlReader->getAttribute($value) == null) && ($string == false)) {
                            $this->xmlValue[$this->i]['run'][$this->j][trim($node, 'w:')] = trim($node, 'w:');
    
                        } else {
                            $this->xmlValue[$this->i]['run'][$this->j][trim($node, 'w:')] = $this->xmlReader->getAttribute($value);
                        }
                    }
                }
                if ($this->xmlReader->nodeType == XMLReader::END_ELEMENT) { $depth = false; }
            }
       }
   }
   
    /**
     * Open XML with content from DOCX file
     * @param string $xmlSource
     * @return array
     */
    private function openWordXML($xmlSource) {

        try{
            $this->xmlReader = new XMLReader();
            $this->xmlReader->open($xmlSource);
            
        } catch(Exception $e) {

            $errors = libxml_get_errors();
            foreach($errors as $error) {
                $xmlError[] = $error->message;
            }
            
            libxml_clear_errors();
            return $xmlError;
        }
    }
    
    /**
     * Close XML Reader
     */
    private function closeWordXML() {
        
        $this->xmlReader->close();
    }

    private function readWordElement() {
        
        foreach($this->nodes as $node => $value) {
            if($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == $node) {

                ($this->xmlReader->getAttribute('w:val') != null) ? $this->xmlValue[$this->i][trim($node, 'w:')][$value] = $this->xmlReader->getAttribute('w:val') : '';
            }
        }
    }
    
    /**
     * Read Attributes from XML tag
     */
    private function readWordAttribute() {
        
        foreach($this->wordAttributeNodes as $node => $element) {

            foreach($element as $attribute => $value) {
                
                if($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == $node) {

                        ($this->xmlReader->getAttribute($attribute) != null) ? $this->xmlValue[$this->i][trim($node, 'w:')][$value] = $this->xmlReader->getAttribute($attribute) : '';
                }
            }
        }
    }
}