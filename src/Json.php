<?php

namespace TB\Toolbox;

use Exception;
use JsonException;

/**
 * PHP library "ext-json" should be installed to use one of theses functions
 * @author Thomas Bondois
 */
class Json
{

    /**
     * @see https://www.php.net/manual/en/function.json-decode.php
     * @param      $value
     * @param bool $assoc
     * @param int  $depth
     * @param int  $options
     *
     * @return mixed
     * @throws \JsonException|Exception
     *
     */
    public static function decode($value, $assoc = false, int $depth = 512, int $options = 0)
    {
        $decoded = json_decode($value, $assoc, $depth, $options);

        $jsonErrorCode = json_last_error();
        if ($jsonErrorCode !== JSON_ERROR_NONE){
            $jsonErrorMessage = json_last_error_msg();
            if (class_exists("JsonException")) {
                throw new JsonException("JSON Exception: ".$jsonErrorMessage, $jsonErrorCode);
            }
            throw new Exception($jsonErrorMessage, $jsonErrorCode);
        }
        return $decoded;


    }

    /**
     * @see https://www.php.net/manual/en/function.json-encode.php
     * @param     $value
     * @param int $options
     * @param int $depth
     * @return false|string
     * @throws \JsonException|Exception
     */
    public static function encode($value, int $options = 0, int $depth = 512)
    {
        $encoded = json_encode($value, $options, $depth);

        $jsonErrorCode = json_last_error();
        if ($jsonErrorCode !== JSON_ERROR_NONE){
            $jsonErrorMessage = json_last_error_msg();
            if (class_exists("JsonException")) {
                throw new JsonException("JSON Exception: ".$jsonErrorMessage, $jsonErrorCode);
            }
            throw new Exception($jsonErrorMessage, $jsonErrorCode);
        }
        return $encoded;
    }


} // end class
