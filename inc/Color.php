<?php

class Color {
  /**
   * Based on the Arlo Carreon phpColors Class
  */
    private $_hex;
    private $_hsl;
    private $_rgb;

    const DEFAULT_ADJUST = 10;

    function __construct( $hex ) {
        $color = str_replace("#", "", $hex);
        if( strlen($color) === 3 ) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        } else if( strlen($color) != 6 ) {
            throw new Exception("HEX color needs to be 6 or 3 digits long");
        }
        $this->_hsl = self::hexToHsl( $color );
        $this->_hex = $color;
        $this->_rgb = self::hexToRgb( $color );
    }
    public static function hexToHsl( $color ){
        $color = self::_checkHex($color);
        $R = hexdec($color[0].$color[1]);
        $G = hexdec($color[2].$color[3]);
        $B = hexdec($color[4].$color[5]);
        $HSL = array();
        $var_R = ($R / 255);
        $var_G = ($G / 255);
        $var_B = ($B / 255);
        $var_Min = min($var_R, $var_G, $var_B);
        $var_Max = max($var_R, $var_G, $var_B);
        $del_Max = $var_Max - $var_Min;
        $L = ($var_Max + $var_Min)/2;
        if ($del_Max == 0)
        {
            $H = 0;
            $S = 0;
        }
        else
        {
            if ( $L < 0.5 ) $S = $del_Max / ( $var_Max + $var_Min );
            else            $S = $del_Max / ( 2 - $var_Max - $var_Min );
            $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            if      ($var_R == $var_Max) $H = $del_B - $del_G;
            else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
            else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;
            if ($H<0) $H++;
            if ($H>1) $H--;
        }
        $HSL['H'] = ($H*360);
        $HSL['S'] = $S;
        $HSL['L'] = $L;
        return $HSL;
    }
    public static function hslToHex( $hsl = array() ){
        if(empty($hsl) || !isset($hsl["H"]) || !isset($hsl["S"]) || !isset($hsl["L"]) ) {
            throw new Exception("Param was not an HSL array");
        }
        list($H,$S,$L) = array( $hsl['H']/360,$hsl['S'],$hsl['L'] );
        if( $S == 0 ) {
            $r = $L * 255;
            $g = $L * 255;
            $b = $L * 255;
        } else {
            if($L<0.5) {
                $var_2 = $L*(1+$S);
            } else {
                $var_2 = ($L+$S) - ($S*$L);
            }
            $var_1 = 2 * $L - $var_2;
            $r = round(255 * self::_huetorgb( $var_1, $var_2, $H + (1/3) ));
            $g = round(255 * self::_huetorgb( $var_1, $var_2, $H ));
            $b = round(255 * self::_huetorgb( $var_1, $var_2, $H - (1/3) ));
        }
        $r = dechex($r);
        $g = dechex($g);
        $b = dechex($b);
        $r = (strlen("".$r)===1) ? "0".$r:$r;
        $g = (strlen("".$g)===1) ? "0".$g:$g;
        $b = (strlen("".$b)===1) ? "0".$b:$b;
        return $r.$g.$b;
    }
    public static function hexToRgb( $color ){
        $color = self::_checkHex($color);
        $R = hexdec($color[0].$color[1]);
        $G = hexdec($color[2].$color[3]);
        $B = hexdec($color[4].$color[5]);
        $RGB['R'] = $R;
        $RGB['G'] = $G;
        $RGB['B'] = $B;
        return $RGB;
    }
    public static function rgbToHex( $rgb = array() ){
        if(empty($rgb) || !isset($rgb["R"]) || !isset($rgb["G"]) || !isset($rgb["B"]) ) {
            throw new Exception("Param was not an RGB array");
        }
        $hex[0] = str_pad(dechex($rgb['R']), 2, '0', STR_PAD_LEFT);
        $hex[1] = str_pad(dechex($rgb['G']), 2, '0', STR_PAD_LEFT);
        $hex[2] = str_pad(dechex($rgb['B']), 2, '0', STR_PAD_LEFT);
        return implode( '', $hex );
  }
    public function lighten( $amount = self::DEFAULT_ADJUST ){
        $lighterHSL = $this->_lighten($this->_hsl, $amount);
        return self::hslToHex($lighterHSL);
    }
    public function getHsl() {
        return $this->_hsl;
    }
    public function getHex() {
        return $this->_hex;
    }
    public function getRgb() {
        return $this->_rgb;
    }
    private function _lighten( $hsl, $amount = self::DEFAULT_ADJUST){
        if( $amount ) {
            $hsl['L'] = ($hsl['L'] * 100) + $amount;
            $hsl['L'] = ($hsl['L'] > 100) ? 1:$hsl['L']/100;
        } else {
            $hsl['L'] += (1-$hsl['L'])/2;
        }

        return $hsl;
    }
    private static function _huetorgb( $v1,$v2,$vH ) {
        if( $vH < 0 ) {
            $vH += 1;
        }
        if( $vH > 1 ) {
            $vH -= 1;
        }
        if( (6*$vH) < 1 ) {
               return ($v1 + ($v2 - $v1) * 6 * $vH);
        }
        if( (2*$vH) < 1 ) {
            return $v2;
        }
        if( (3*$vH) < 2 ) {
            return ($v1 + ($v2-$v1) * ( (2/3)-$vH ) * 6);
        }
        return $v1;
    }
    private static function _checkHex( $hex ) {
        $color = str_replace("#", "", $hex);
        if( strlen($color) == 3 ) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        } else if( strlen($color) != 6 ) {
            throw new Exception("HEX color needs to be 6 or 3 digits long");
        }
        return $color;
    }
    public function __toString() {
        return "#".$this->getHex();
    }
    public function __get($name)
    {
        switch (strtolower($name))
        {
            case 'red':
            case 'r':
                return $this->_rgb["R"];
            case 'green':
            case 'g':
                return $this->_rgb["G"];
            case 'blue':
            case 'b':
                return $this->_rgb["B"];
            case 'hue':
            case 'h':
                return $this->_hsl["H"];
            case 'saturation':
            case 's':
                return $this->_hsl["S"];
            case 'lightness':
            case 'l':
                return $this->_hsl["L"];
        }
    }
    public function __set($name, $value)
    {
        switch (strtolower($name))
        {
            case 'red':
            case 'r':
                $this->_rgb["R"] = $value;
                $this->_hex = $this->rgbToHex($this->_rgb);
                $this->_hsl = $this->hexToHsl($this->_hex);
                break;
            case 'green':
            case 'g':
                $this->_rgb["G"] = $value;
                $this->_hex = $this->rgbToHex($this->_rgb);
                $this->_hsl = $this->hexToHsl($this->_hex);
                break;
            case 'blue':
            case 'b':
                $this->_rgb["B"] = $value;
                $this->_hex = $this->rgbToHex($this->_rgb);
                $this->_hsl = $this->hexToHsl($this->_hex);
                break;
            case 'hue':
            case 'h':
                $this->_hsl["H"] = $value;
                $this->_hex = $this->hslToHex($this->_hsl);
                $this->_rgb = $this->hexToRgb($this->_hex);
                break;
            case 'saturation':
            case 's':
                $this->_hsl["S"] = $value;
                $this->_hex = $this->hslToHex($this->_hsl);
                $this->_rgb = $this->hexToRgb($this->_hex);
                break;
            case 'lightness':
            case 'light':
            case 'l':
                $this->_hsl["L"] = $value;
                $this->_hex = $this->hslToHex($this->_hsl);
                $this->_rgb = $this->hexToRgb($this->_hex);
                break;
        }
    }
}
