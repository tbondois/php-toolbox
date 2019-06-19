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
     * Better boolean conversion based on special common keywords
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
            if (static::is_negative($val)) {
                $stripped = "-".$stripped;
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
        return static::starts_with($val, "-");
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
                parent::var_dump($var, false, 1);
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
