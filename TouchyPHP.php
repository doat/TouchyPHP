<?php
/* 
 * Copyright 2011 (c) doat.com
 * Copying and/or distribution of this file is prohibited.
 */

class TouchyPHP {    
    /**
    * constructor
    */
    public function __construct() {}

    
    /**
     * Returns the contents of the CSS/JS file
     */
    public function getFile($filename, $forceInclude = null){
        $value = $filename;
        $format = pathinfo($filename, PATHINFO_EXTENSION);
    
        if ($forceInclude !== false){
            $content = file_get_contents($filename);
            switch ($format){
                case 'css':
                    $content = self::_replaceImageUrls($content);
                case 'css':
                case 'js':
                    $value = self::_getInternalTag($format, $content);
                default:
                    if (self::_shouldConvertToBase64($filename)){
                        $value = self::_getEncodedImage($filename);
                    }
            }
        }
        else {
            switch ($format){
                case 'css':
                case 'js':
                    $value = self::_getExternalTag($format, $filename);
            }
        }
        return $value;
    }
    
    /**
     * Gets an image filename and returns if it should be converted to base64
     * @param <string> $fileName
     * @return <boolean>
     */
    private function _shouldConvertToBase64($filename) {
        // List of image formats allowed for base64 encoding
        $formatArray = array('jpeg', 'jpg', 'jpe', 'png', 'gif');

        // Get file format
        $format = pathinfo($filename, PATHINFO_EXTENSION);
        // Remove trailing querystring
        if (strrpos($format, '?')){
            $format = substr($format, 0, strrpos($format, '?'));
        }

        // If the image is not in the list of allowed formats or compression is false, don't encode it, just display it as is
        return (in_array($format, $formatArray));
    }
    
    /**
     * Gets an image filename and returns it converted to base64 data string
     */
    private static function _getEncodedImage($filename) {
        // Get file format
        $format = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Do the magic
        $data = base64_encode(file_get_contents($filename));

        // Return the image URI
        return 'data:image/'.$format.';base64,'.$data;
    }
    
    private static function _replaceImageUrls($content){
        return preg_replace_callback('/url\([\'"]?([^\)\'"]+)/',
                                        function($m){
                                            return str_replace($m[1], '*** REPLACE THIS RANBENA ***', $m[0]);
                                        },
                                        $content);
    }
    
    private static function _getExternalTag($format, $filename){
        switch ($format) {
            case 'css':
                $template = '<link href="{$filename}" rel="stylesheet" type="text/css" />';
                break;
            case 'js':
                $template = '<script src="{$filename}" type="text/javascript"></script>';
        }
    
        return str_replace('{$filename}', $filename, $template);
    }
    
    private static function _getInternalTag($format, $content){
        switch ($format){
            case 'css':
                $template = '<style type="text/css">{content}</style>';
                break;
            case 'js':
                $template = '<script type="text/javascript">{content}</script>';
                break;
        }
    
        return str_replace('{content}', $content, $template);
    }
}