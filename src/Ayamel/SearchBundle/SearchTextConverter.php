<?php

namespace Ayamel\SearchBundle;

/**
 * Detects and converts text from various formats to UTF-8.  Unfortunately, this is a finicky process
 * in general, and requires manual management of detect orders.
 */
class SearchTextConverter
{

    private $encodingDetectionOrder = [];
    
    public function __construct($encodingDetectionOrder = [])
    {
        $this->encodingDetectionOrder = $encodingDetectionOrder;
    }
    
    /**
     * Detect the format of the text - returned value may or may not actually be accurate, as the provided
     * detection order matters.
     */
    public function detectTextEncoding($string)
    {
        foreach ($this->encodingDetectionOrder as $enc) {
            if (mb_check_encoding($string, $enc)) {
                return $enc;
            }
        }
        
        return false;
    }
    
    /**
     * Return string converted to a target format.  If not provided, the "from" format
     * will attempt to be detected.
     */
    public function convertTextEncoding($string, $to = 'UTF-8', $from = null)
    {
        $from = ($from) ? $from : $this->detectTextEncoding($string);
        if (!$from) {
            return false;
        }
        
        //do not re-encode UTF to UTF, that creates gibberish
        if ((stripos($to, 'UTF') === 0) && (stripos($from, 'UTF') === 0)) {
            return $string;
        }
        
        return mb_convert_encoding($string, $to, $from);
    }

}
