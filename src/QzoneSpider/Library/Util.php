<?php
namespace MichaelLuthor\QzoneSpider\Library;
class Util {
    /**
     * @param unknown $length
     */
    public static function randNumber($length) {
        $number = array();
        for ( $i=0; $i<$length; $i++ ) {
            $number[] = rand(0, 9);
        }
        return implode('', $number);
    }
    
    /**
     * @param unknown $a1
     * @param unknown $a2
     */
    public static function arrayMerge( $a1, $a2 ) {
        foreach ( $a2 as $key => $value ) {
            $a1[$key] = $a2[$key];
        }
        return $a1;
    }
}