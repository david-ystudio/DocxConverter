<?php
/*
 * Converter
 * 
 * Copyright (c) 2013, David Yilma (david.yilma@dyksoft.cz)
 * The MIT Licence (http://opensource.org/licenses/MIT)
 * 
 */

namespace Converter;

use ZipArchive;
use Exception;

/**
 * Class Service, just service for other classes
 *
 * @author David Yilma, 2013
 * @version 0.9
 */
class Service {
    
    /**
     * Open ZIP file and extract selected file
     * @param string $zipName
     * @param string $destination
     * @param string $selectFile
     * @return mixed Path to extracted files of FALSE.
     * @throws Exception
     */
    public static function openZip($zipName, $destination, $selectFile) {
        
        $zipArchive = new ZipArchive();
        $archiveOpened = $zipArchive->open($zipName);

        if($archiveOpened === TRUE) {
            $archiveUnziped = $zipArchive->extractTo($destination, $selectFile);
            $zipArchive->close();
            
            if($archiveUnziped === TRUE) {
                return $path = $destination.$selectFile;
            } else {
                return $archiveUnziped;                
            }
            
        } else {
            throw new Exception('Open ZIP file failed.', $archiveOpened);
        }
    }
}
