<?php
define('CHUNK_SIZE', 1024*1024);

Class Arr
{
    public static function get($key = null, $array = array(), $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
}

Class URL
{
    public static function get($protocol = false)
    {
        $p = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';
        return $protocol ? $p . $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'];
    }
}

Class Helper
{
    public static function human_filesize($bytes, $decimals = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}