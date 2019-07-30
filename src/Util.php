<?php

namespace TB\Toolbox;

/**
 * @author Thomas Bondois
 */
class Util extends \utilphp\util
{
    const DATETIME_FORMAT_DEFAULT   = "Y-m-d H:i:s";
    const DATETIME_FORMAT_DEFAULT_T = "Y-m-d\TH:i:s";
    const DATETIME_FORMAT_MICRO     = "Y-m-d H:i:s.u";
    const DATETIME_FORMAT_MICRO_T   = "Y-m-d\TH:i:s.u";

    const TRIM_BASE_CHARLIST = " \t\n\r\0\x0B";

    const BOOL_CAST_IGNORED_CHARS = [" ", "\t", "\r", "\n", "\0", "\x0B"];
    const BOOL_CAST_CONSIDERED_AS_FALSE = [
        "",
        "false"     , "null",
        "0"         , "-1",
        "''"        , '""',
        "no"        , "none",
        "disabled"  , "void",
        "undefined" , "empty",
        "[]"        , "{}",
        "__return_false",
    ];

    /**
     * Better boolean conversion based on special common keywords
     * @param mixed $val
     * @return bool
     */
    public static function bool($val) : bool
    {
        if (empty($val)) {
            return false;
        }
        if (static::is_countable($val)) {
            return (bool)count($val);
        }
        if (static::is_number($val)) {
            return static::float($val) > 0;
        }

        $sVal = (string)$val;
        $sVal = str_replace(static::BOOL_CAST_IGNORED_CHARS, "", $sVal);
        $sVal = strtolower($sVal);
        if (in_array($sVal, static::BOOL_CAST_CONSIDERED_AS_FALSE)) {
            return false;
        }
        return (bool)$val;
    }


    /**
     * Better integer conversion
     * @param mixed $val
     * @return int
     */
    public static function int($val) : int
    {
        return (int)static::float($val);
    }

    /**
     * Better float conversion
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
            if (!$stripped) {
                $stripped = 0;
            }
            if (static::is_negative($val)) {
                $stripped = "-".$stripped;
            }
            $val = $stripped;
        }
        return (float)$val;
    }

    public static function get_extended_type($var) : string
    {
        $meta = gettype($var);
        if (is_object($var)) {
            $meta.=":".get_class($var);
        }
        $size = static::get_size($var);
        if (is_numeric($size)) {
            $meta.="($size)";
        }
        return $meta;
    }

    /**
     * Get string length or elements count
     * @param mixed $var
     * @return int|null
     */
    public static function get_size($var)
    {
        if (is_string($var)) {
            return strlen($var);
        } elseif (static::is_countable($var)) {
            return count($var);
        }
        return null;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public static function is_negative($val) : bool
    {
        return static::starts_with((string)$val, "-");
    }

    /**
     * Check if parameter is contain a whole number (no decimal or alphabet, except a minus at first position for negative ones)
     * @param $val
     * @return bool
     */
    public static function is_number($val) {
        return !is_nan($val);
    }

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
     * @see \is_iterable()
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
     * @see \is_countable()
     * @see https://www.php.net/manual/en/function.is-countable.php
     * @see https://wiki.php.net/rfc/is-countable
     * @param mixed $var
     * @return bool
     */
    public static function is_countable($var)
    {
        return is_array($var)
            || $var instanceof \Countable
            || $var instanceof \ResourceBundle
            || $var instanceof \SimpleXmlElement
        ;
    }

    public function is_as_array($var) : bool
    {
        return static::is_iterable($var) && static::is_countable($var);
    }

    public function is_as_array_object($var) : bool
    {
        return static::is_as_array($var) && is_object($var);
    }

    /**
     * Polyfill for PHP 7.3
     * @see \array_key_first()
     * @param array $array
     *
     * @return int|string
     */
    public static function array_key_first(array $array)
    {
        return static::array_first_key($array);
    }

    /**
     * Polyfill for PHP 7.3
     * @see \array_key_last()
     * @param array $array
     * @return int|string
     */
    public static function array_key_last(array $array)
    {
        return static::array_last_key($array);
    }

    public static function array_key_random(array $array)
    {
        return array_rand($array, 1);
    }

    public static function array_value_random(array $array)
    {
        $key = array_rand($array, 1);
        return $array[$key];
    }

    public static function iterable_key_first(\iterable $iterable)
    {
        foreach ($iterable as $key => $value) {
            return $key;
        }
        return null;
    }

    public static function iterable_first(\iterable $items)
    {
        foreach ($items as $key => $value) {
            return $value;
        }
        return null;
    }

    public static function iterable_key_last(\iterable $items)
    {
        $lastKey = null;
        foreach ($items as $key => $value) {
            $lastKey = $key;
        }
        return $lastKey;
    }


    public static function iterable_last(\iterable $items)
    {
        $lastValue = null;
        foreach ($items as $key => $value) {
            $lastValue = $value;
        }
        return $lastValue;
    }

    /**
     * @param        $str
     * @param string $extraCharlist
     * @return string
     */
    public static function trim($str, string $extraCharlist = "")
    {
        $charlist = static::TRIM_BASE_CHARLIST.$extraCharlist;
        return trim($str, $charlist);
    }

    /**
     * Check if parameter is contain a whole number greather >= 0
     * @param $val
     * @return bool
     */
    public static function is_natural_number_include_zero($val) : bool
    {
        if (static::is_number($val) && !static::is_negative($val)) {
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
    public static function format_decimal($val, int $precision = 2, string $decimalSymbol = ".")
    {
        $strVal = (string)round(static::float($val), $precision);
        if (static::is_number($strVal)) {
            return $strVal.$decimalSymbol."00";
        }
        $strVal = str_replace(".", $decimalSymbol, $strVal);
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
    public static function price_eur($val, string $decimalSymbol = ",")
    {
       return static::format_decimal($val, 2, $decimalSymbol)."€";
    }

    /**
     * @param int|float|string $val
     * @param string $decimalSymbol
     * @return string
     */
    public static function price_usd($val, string $decimalSymbol = ",")
    {
        return "$".static::format_decimal($val, 2, $decimalSymbol);
    }

    /**
     * @param int $size
     * @return string
     */
    public static function human_readable_octets($size)
    {
        $unit = ["o","Ko","Mo","Go","To"];
        $size = (int)$size;
        return round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
    }

    /**
     * @param int $size
     * @return string
     */
    public static function human_readable_bytes($size)
    {
        $unit = ["B","KB","MB","GB","TB"];
        $size = (int)$size;
        return round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
    }
    
    /**
     * @see https://www.php.net/manual/fr/datetime.construct.php
     * @see https://www.php.net/manual/en/datetime.formats.php
     * @see https://www.php.net/manual/en/timezones.php
     * @param int|string|null $time ie : "-1 day", "yesterday", "now", "1940-06-22" etc
     * @param string|null $format
     * @param string|null $timezone ie: "Europe/Paris", "UTC"... null use php.ini default
     * @return false|string
     * @throws \Exception
     */
    public static function date_format($time = null, string $format = null, string $timezone = null)
    {
        if (null === $format) {
            $format = self::DATETIME_FORMAT_DEFAULT;
        }
        if (null == $format) {
            $time = 'now';
        } elseif (is_numeric($time)) {
            $time = "@".$time;
        }

        $oTimezone = null;
        if ($timezone) {
            $oTimezone = new \DateTimeZone($timezone);
        }
        $oDateTime = new \DateTime($time, $oTimezone);

        return $oDateTime->format($format);
    }

    /**
     * @param int|string|null $time
     * @param string|null $format
     *
     * @return false|string
     * @throws \Exception
     */
    public static function date_format_utc($time = null, string $format = null)
    {
        return static::date_format($time, $format, "UTC");
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
            , $backtrace["file"]  ?? "-"
            , $backtrace["line"] ?? "-"
        );

        return null;
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
                parent::var_dump($var);
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
            parent::var_dump_plain($var, false, 1);
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
