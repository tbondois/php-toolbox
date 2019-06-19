<?php

namespace TB\Toolbox;

use utilphp\util as BaseUtil;

/**
 * @author Thomas Bondois
 */
class Util extends BaseUtil
{
    const DATE_FORMAT_DEFAULT = "Y-m-d h:i:s";

    /**
     * @param array $array
     * @return bool
     */
    public function is_sequential(array $array) : bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Polyfill for native function introduced in PHP 7.1
     * @param $var
     * @return bool
     */
    public static function is_iterable($var) : bool
    {
        return is_array($var)
            || $var instanceof \Traversable
            || $var instanceof \iterable
        ;
    }

    /**
     * Polyfill for native function introduced in PHP 7.3
     * @see https://www.php.net/manual/en/function.is-countable.php
     * @see https://wiki.php.net/rfc/is-countable
     * @param mixed $var
     * @return bool
     */
    public static function is_countable($var) {
        return is_array($var)
            || $var instanceof \Countable
            || (class_exists("ResourceBundle") && $var instanceof \ResourceBundle)
            || (class_exists("SimpleXmlElement") && $var instanceof \SimpleXmlElement)
        ;
    }

    /**
     * @param int|string|null $time
     * @param string|null $format
     * @return false|string
     */
    public static function date_format($time = null, $format = null)
    {
        if (null === $format) {
            $format = self::DATE_FORMAT_DEFAULT;
        }
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        return date($format, $time);
    }

    /**
     * @param int|string|null $time
     * @param string|null $format
     * @return false|string
     */
    public static function gmdate_format($time = null, $format = null)
    {
        if (null === $format) {
            $format = self::DATE_FORMAT_DEFAULT;
        }
        if (null !== $time && !is_numeric($time)) {
            $time = strtotime($time);
        }
        return gmdate($format, $time);
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public function bool($val) : bool
    {
        switch (strtolower(trim($val))) {
            case "":
            case "-":
            case "false":
            case "null":
            case "no":
            case "none":
            case "disabled":
            case "undefined":
            case "void":
            case "empty":
            case "0":
            case "-1":
                return false;
        }
        switch ($val) {
            case []:
            case -1:
                return false;
        }
        return (bool)$val;
    }


    /**
     * @param mixed $val
     * @return int
     */
    public static function int($val) : int
    {
        return (int)static::float($val);
    }

    /**
     * @param mixed $val
     * @return float
     */
    public static function float($val) : float
    {
        if (!is_numeric($val)) { // is_numeric accept . but no , as numeric value

            $stripped = preg_replace([
                    "/\,/",
                    "/[^0-9\.]/",
                ], [
                    ".",
                    "",
                ], (string)$val
            );
            if (static::is_negative($val)) {
                $stripped = '-'.$stripped;
            }
            if (!$stripped) {
                $stripped = 0;
            }
            $val = $stripped;
        }
        return (float)$val;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public static function is_negative($val) : bool
    {
        if (strpos((string)$val, '-') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if parameter is contain a whole number (no decimal or alphabet, except a minus at first position for negative ones)
     * @param $val
     * @return bool
     */
    public static function is_number($val) {
        if (is_int($val)) {
            return true;
        } elseif (is_string($val)) {
            if (static::is_negative($val)) {
                $val = substr($val, 1);
            }
            return ctype_digit($val);
        } else {
            return false;
        }
    }


    /**
     * Check if parameter is contain a whole number greather >= 0
     * @param $val
     * @return bool
     */
    public static function is_natural_number_include_zero($val) : bool
    {
        if (static::is_natural_number_exclude_zero($val) && !static::is_negative($val)) {
            return true;
        }
        return false;
    }

    /**
     * Check if parameter is contain a whole number greather >= 1
     * @param $val
     * @return bool
     */
    public static function is_natural_number_exclude_zero($val) : bool
    {
        if (static::is_natural_number_include_zero($val) && (int)$val >= 1) {
            return true;
        }
        return false;
    }

    /**
     * @param int|float|string $val
     * @param int              $precision
     * @param string           $decimalSymbol
     *
     * @return string
     */
    public static function format_decimal($val, int $precision = 2, string $decimalSymbol = '.')
    {
        $strVal = (string)round(static::float($val), $precision);
        if (static::is_number($strVal)) {
            return $strVal.$decimalSymbol."00";
        }
        $strVal = str_replace('.', $decimalSymbol, $strVal);
        $parts = explode($decimalSymbol, (string)$strVal);
        $lastPart = end($parts);
        if (strlen($lastPart) < $precision) {
            $strVal.= str_repeat("0", $precision - strlen($lastPart));
        }
        return $strVal;
    }

    /**
     * @param int|float|string $val
     * @param string $decimalSymbol
     * @return string
     */
    public static function price_eur($val, string $decimalSymbol = ',')
    {
       return static::format_decimal($val, 2, $decimalSymbol)."€";
    }

    /**
     * @param int|float|string $val
     * @param string $decimalSymbol
     * @return string
     */
    public static function price_usd($val, string $decimalSymbol = ',')
    {
        return "$".static::format_decimal($val, 2, $decimalSymbol);
    }

    /**
     * @param int $size
     * @return string
     */
    public static function human_readable_octets($size)
    {
        $unit = ['o','Ko','Mo','Go','To'];
        $size = (int)$size;
        return round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
    }

    /**
     * @param int $size
     * @return string
     */
    public static function human_readable_bytes($size)
    {
        $unit = ['B','KB','MB','GB','TB'];
        $size = (int)$size;
        return round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
    }

    /**
     * @param int $index
     * @return string|null
     */
    public static function formatted_backtrace(int $index = 1)
    {
        $dbt = debug_backtrace();
        if (isset($dbt[$index])) {
            $backtrace = $dbt[$index];
        } else {
            $backtrace = end($dbt);
        }
        return sprintf("%s:%s"
            , $backtrace['file']  ?? '-'
            , $backtrace['line'] ?? '-'
        );

        return null;
    }

    /**
     * Usage example:
     * $data = [ ... ];
     * Util::download_send_headers("data_export_" . date("Y-m-d") . ".csv");
     * Csv::array_to_csv($data);
     *
     * @param string $file
     */
    public static function download_send_headers(string $file)
    {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$file}");
        header("Content-Transfer-Encoding: binary");
    }

    /**
     * @param mixed ...$vars
     * @return string
     */
    public static function dump(...$vars)
    {
        if (function_exists("dump")) {
            dump(...$vars);
        } else {
            foreach ($vars as $var) {
                BaseUtil::var_dump($var, false, 1);
            }
        }
    }

    /**
     * @param mixed ...$vars
     * @return string
     */
    public static function dump_plain(...$vars)
    {
        foreach ($vars as $var) {
            BaseUtil::var_dump_plain($var, false, 1);
        }
    }

    /**
     * dump and stop script
     * @param mixed ...$vars
     */
    public static function die_dump(...$vars)
    {
        static::dump(...$vars);
        die(PHP_EOL."/die_dump in ".static::formatted_backtrace().PHP_EOL);
    }


    /**
     * dump and stop script
     * @param mixed ...$vars
     */
    public static function die_dump_plain(...$vars)
    {
        static::dump_plain(...$vars);
        die(PHP_EOL."/die_dump_plain in ".static::formatted_backtrace().PHP_EOL);
    }

} // end class
