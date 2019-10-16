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
        ""          , "0",
        "false"     , "null",
        "''"        , '""',
        "no"        , "none",
        "disabled"  , "void",
        "undefined" , "empty",
        "[]"        , "{}",
        "__return_false",
    ];


    // ----------------------- Types Converts -----------------------


    /**
     * Better boolean conversion based on special common keywords
     * @param $val
     * @param array|null  $consideredAsFalse
     * @param string|null $ignoredChars
     * @return bool
     */
    public static function bool($val, $consideredAsFalse  = null, $ignoredChars = null) : bool
    {
        if (null === $consideredAsFalse) {
            $consideredAsFalse = self::BOOL_CAST_CONSIDERED_AS_FALSE;
        }
        if (null === $ignoredChars) {
            $consideredAsFalse = self::BOOL_CAST_IGNORED_CHARS;
        }

        if (empty($val)) {
            return false;
        }
        if (static::is_countable($val)) {
            return (bool)count($val);
        }
        if (is_numeric($val)) {
            return static::float($val) > 0;
        }

        $sVal = (string)$val;
        $sVal = str_replace($ignoredChars, "", $sVal);
        $sVal = strtolower($sVal);
        if (in_array($sVal, $consideredAsFalse)) {
            return false;
        }
        return (bool)$val;
    }

    /**
     * Better integer conversion
     * @param $val
     * @return int
     */
    public static function int($val) : int
    {
        return (int)static::float($val);
    }

    /**
     * Better float conversion
     * @param $val
     * @return float
     */
    public static function float($val) : float
    {
        if (!is_numeric($val)) { // is_numeric accept . but no , as decimal symbol

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

    /**
     * @param $val
     * @return string
     */
    public static function string($val) : string
    {
        return (string)$val;
    }


    // -----------------------  Type Detection -----------------------


    public static function get_type_meta($var) : string
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
     * @param $var
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


    // ----------------------- Strings -----------------------


    /**
     * @param $str
     * @param string $extraCharlist
     * @return string
     */
    public static function trim($str, string $extraCharlist = "")
    {
        $charlist = static::TRIM_BASE_CHARLIST.$extraCharlist;
        return trim($str, $charlist);
    }

    /**
     * @param string $string
     * @param int    $maxlength
     * @param string $ending
     * @param string $cutChar only 1 char here. can be \n to cut at first line.
     * @return string
     */
    public function cut_at_word($string, $maxlength = 255, $ending = "&hellip;", $cutChar= "¤")
    {
        if (strlen($string) > $maxlength) {
            $string = trim($string, $cutChar);
            $wrap = wordwrap($string, $maxlength, $cutChar);
            $sub = substr($wrap, 0, strpos($wrap, $cutChar));
            if (strlen($sub)) {
                return $sub.$ending;
            }
        }
        return $string;
    }

    /**
     * @param string $url
     * @param array  $params
     * @return string
     */
    public function build_url(string $url, $params = [])
    {
        if (!empty($params)) {
            $queryString = http_build_query($params);
            if (strpos($url, "?") === false()) {
                $separator = "?";
            } else {
                $separator = "&";
            }
            return $url.$separator.$queryString;
        }
    }


    //  ----------------------- Numbers and Decimals -----------------------


    /**
     * @param $val
     * @return bool
     */
    public static function is_negative($val) : bool
    {
        return static::starts_with((string)$val, "-");
    }

    /**
     * Check if parameter is contain a whole number (no decimal or alphabet, except a minus at first position for negative ones)
     *
     * @param $var
     *
     * @return bool
     */
    public static function is_whole_number($var) {
        if (is_int($var)) {
            return true;
        } elseif (is_float($var)) {
            return (int)$var == (float)$var;
        } elseif (is_string($var) && is_numeric($var)) {
            if (static::is_negative($var)) {
                $var = substr($var, 1);
            }
            return ctype_digit($var);
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
        if (static::is_whole_number($val) && !static::is_negative($val)) {
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


    //  ----------------------- Prices -----------------------


    /**
     * @see format_price()
     * @param $price
     *
     * @return bool
     */
    public function is_price($price) : bool
    {
        try {
             static::format_price($price, ["silent_errors" => false]);
        } catch (\Exception $e) {
            return false;
        }
        return true;

    }

    /**
     * @see is_price()
     * @param string|int|float $price valid price expected, should contains "." for decimal
     * @param array $options to edit default format rules
     *
     * @return string
     * @throws \Exception
     *
     */
    public function format_price($price, array $options = [])
    {
        try {
            $currency = $options["currency"] ?? null; // will be process with sprintf if contains %s (as price amount). ie: "$ %s", else will suffix it.
            $precision = $options["precision"] ?? 2; // decimal precision
            $decimalSmart = $options["decimal_smart"] ?? false; // will remove decimal part when .00
            $decimalSeparator = $options["decimal_separator"] ?? ","; // accepted : "." or ","
            $thousandsSeparator = $options["thousands_separator"]  ?? ""; // can be spaces, comma etc
            $forceHtmlNonBreakableSpaces = $options["nbsp"] ?? false; // will transform space into nbsp;
            $forceHtmlChars = $options["html_chars"] ?? false; // will convert special chars into html &; equivalent
            $hideSign = $options["hide_sign"] ?? false; // will not show the - when negative
            $silentErrors = $options["silent_errors"] ?? false;

            $formattedPrice = static::float_price($price, ["decimal_separator" => "."]);

            if ($hideSign) {
                $formattedPrice = abs($formattedPrice);
            }
            if ($decimalSmart && (float)floor($formattedPrice) === (float)$formattedPrice) {
                $precision = 0;
            }
            $formattedPrice = number_format($formattedPrice, $precision, $decimalSeparator, $thousandsSeparator);
            if (!empty($currency)) {
                if (strpos($currency, "%s") !== false) {
                    $formattedPrice = sprintf($currency, $formattedPrice);
                } else {
                    $formattedPrice = $formattedPrice.$currency;
                }
            }
            if ($forceHtmlChars) {
                $formattedPrice = htmlspecialchars($formattedPrice);
            }
            if ($forceHtmlNonBreakableSpaces) { // to process after htmlspecialchars !
                $formattedPrice = str_replace(" ", "&nbsp;", $formattedPrice);
            }
            return $formattedPrice;
        } catch (\Throwable $e) {
            if ($silentErrors) {
                throw new \Exception("Error formatting price '$price'", 41, $e);
            }
        }
        return $price;
    }

    /**
     * Intemps Convert a price label into in float value, with the most popular currencies
     * @see format_price()
     * @param float|int|string $price
     * @param array $options
     *
     * @return float
     * @throws \Exception
     */
    public function float_price($price, array $options = []) : float
    {
        $thousandsSeparator = $options["thousand_separator"] ?? "";// indication about entry param
        $decimalSeparator = $options["decimal_separator"] ?? ","; // indication about entry param

        $amount = strip_tags($price);

        $amount = htmlspecialchars_decode($amount);
        $amount = str_replace($thousandsSeparator,"¤¤", $amount);
        $amount = str_replace($decimalSeparator,".", $amount);
        $amount = str_replace([" ","¤¤","€","&euro;","$","£","¥","nbsp;","#"], "", $amount);
        $amount = static::trim($amount);
        $floatAmount = (float)$amount;
        if (is_numeric($amount) || $floatAmount !== .0) {
            return $floatAmount;
        }
        throw new \Exception("Error converting price '$price' to float ('$amount' : $floatAmount)", 40);
    }

    /**
     * @param int|float|string $val
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    public static function price_eur($val, array $options = [])
    {
        if (!isset($options["currency"])) {
            $options["currency"] = "%s€";
        }
        return static::format_price($val, $options);
    }

    /**
     * @param int|float|string $val
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    public static function price_usd($val, array $options = [])
    {
        if (!isset($options["currency"])) {
            $options["currency"] = "$%s";
        }
        return static::format_price($val, $options);
    }


    // ----------------------- Dates -----------------------


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


    // ----------------------- Bytes -----------------------


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


    // -----------------------  Array/iterable Detection -----------------------


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
        ;
    }

    /**
     * Polyfill for native function introduced in PHP 7.3
     * @see \is_countable()
     * @see https://www.php.net/manual/en/function.is-countable.php
     * @see https://wiki.php.net/rfc/is-countable
     * @param $var
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

    public function is_like_array($var) : bool
    {
        return static::is_iterable($var) && static::is_countable($var);
    }

    public function is_like_array_but_object($var) : bool
    {
        return static::is_like_array($var) && is_object($var);
    }

    /**
     * Test on a copy to not change the original array pointers
     * @param array $array
     * @return bool
     */
    public function is_sequential(array $array) : bool
    {
        $clone = $array;
        return array_keys($clone) === range(0, count($clone) - 1);
    }


    // -----------------------  Arrays -----------------------


    /**
     * Test on a copy to not change the original array pointers
     * @see \array_key_first()
     * @param array $array
     *
     * @return int|string
     */
    public static function array_first_key(array $array)
    {
        $clone = $array;
        return parent::array_first_key($clone);
    }

    /**
     * Test on a copy to not change the original array pointers
     * @param array $array
     *
     * @return int|string
     */
    public static function array_first_value(array $array)
    {
        $clone = $array;
        return reset($clone);
    }

    /**
     * Test on a copy to not change the original array pointers
     * @see \array_key_last()
     * @param array $array
     * @return int|string
     */
    public static function array_last_key(array $array)
    {
        $clone = $array;
        return parent::array_last_key($clone);
    }


    /**
     * Test on a copy to not change the original array pointers
     * @param array $array
     *
     * @return mixed
     */
    public static function array_last_value(array $array)
    {
        $clone = $array;
        return end($clone);
    }

    /**
     * Test on a copy to not change the original array pointers
     * @param array $array
     *
     * @return mixed
     */
    public static function array_random_key(array $array)
    {
        $clone = $array;
        return array_rand($clone, 1);
    }

    /**
     * @param array $array
     *
     * @return mixed
     */
    public static function array_random_value(array $array)
    {
        $clone = $array;
        $key = array_rand($clone, 1);
        return $clone[$key];
    }

    /**
     * Test on a copy to not change the original object internal pointer
     * @param array $array
     * @param mixed ...$indexes
     *
     * @return array
     */
    public static function array_column_recursive(array $array, ... $indexes)
    {
        $clone = $array;
        foreach ($indexes as $index) {
            try {
                $array = array_column($array, $index);
            } catch (\Exception $exception) {
                break;
            }
        }
        return $array;
    }


    // -----------------------  Traversable Objects -----------------------


    /**
     * @param \Traversable $collection
     *
     * @return int|string|null
     */
    public static function collection_first_key(\Traversable $collection )
    {
        $clone = clone $collection ;
        foreach ($clone as $key => $value) {
            return $key;
        }
        return null;
    }

    /**
     * Test on a copy to not change the original object internal pointer
     * @param \Traversable $collection
     *
     * @return mixed|null
     *
     */
    public static function collection_first_value(\Traversable $collection )
    {
        $clone = clone $collection ;
        foreach ($clone as $value) {
            return $value;
        }
        return null;
    }

    /**
     * Test on a copy to not change the original object internal pointer
     * @param \Traversable $collection
     *
     * @return int|string|null
     */
    public static function collection_last_key(\Traversable $collection )
    {
        $clone = clone $collection ;
        $lastKey = null;
        foreach ($clone as $key => $value) {
            $lastKey = $key;
        }
        return $lastKey;
    }


    /**
     * Test on a copy to not change the original object internal pointer
     * @param \Traversable $collection
     *
     * @return mixed|null
     */
    public static function collection_last_value(\Traversable $collection )
    {
        $clone = clone $collection ;
        $lastValue = null;
        foreach ($clone as $value) {
            $lastValue = $value;
        }
        return $lastValue;
    }


    // ----------------------- Development & debug -----------------------


    /**
     * @param int $index
     * @return string
     */
    public static function format_backtrace(int $index = 1)
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
        die(PHP_EOL."/die_dump in ".static::format_backtrace().PHP_EOL);
    }

    /**
     * dump and stop script
     * @param mixed ...$vars
     */
    public static function die_dump_plain(...$vars)
    {
        static::dump_plain(...$vars);
        die(PHP_EOL."/die_dump_plain in ".static::format_backtrace().PHP_EOL);
    }

} // end class
